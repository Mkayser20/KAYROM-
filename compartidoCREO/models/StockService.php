<?php
// Tx
// ayudante para "transacciones" (agrupar varios pasos en uno solo).
// Sirve para que, si un paso falla, se deshagan TODOS los anteriores.
// Permite anidar: si ya hay una transacción abierta, no abre otra.
class Tx {
    private static $nivel = 0;      // cuántas "cajas" abiertas hay
    private static $fallo = false;  // si alguna falló

    // Abre una transacción (solo la primera vez; las demás se "suman" a esa)
    public static function iniciar($db) {
        if (self::$nivel === 0) {
            $db->begin_transaction();
            self::$fallo = false;
        }
        self::$nivel++;
    }

    // Cierra una transacción. $ok = true si todo salió bien.
    // Cuando se cierra la última: si hubo algún fallo, deshace todo; si no, guarda todo.
    // Devuelve true si todo quedó guardado bien.
    public static function terminar($db, $ok = true) {
        if (!$ok) self::$fallo = true;
        self::$nivel--;
        $resultado = !self::$fallo;
        if (self::$nivel === 0) {
            if (self::$fallo) { $db->rollback(); } else { $db->commit(); }
            self::$fallo = false;
        }
        return $resultado;
    }
}

class StockService {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConexion();
    }

    // Suma repuestos al stock (llegó un pedido, alta de repuesto, etc.)
    public function entrada($repuesto_id, $cantidad, $origen, $origen_id = null, $descripcion = '') {
        return $this->mover($repuesto_id, 'Entrada', $cantidad, $origen, $origen_id, $descripcion);
    }

    // Resta repuestos del stock (se usó en una orden de trabajo, etc.)
    // Si no alcanza el stock, NO resta nada y devuelve un error.
    public function salida($repuesto_id, $cantidad, $origen, $origen_id = null, $descripcion = '') {
        return $this->mover($repuesto_id, 'Salida', $cantidad, $origen, $origen_id, $descripcion);
    }

    // Corrige el stock a un número exacto (por ejemplo, tras contar los repuestos en el estante)
    public function ajustarA($repuesto_id, $nuevoStock, $origen = 'Ajuste manual', $origen_id = null, $descripcion = '') {
        return $this->mover($repuesto_id, 'Ajuste', $nuevoStock, $origen, $origen_id, $descripcion);
    }

    // Lógica común de las tres operaciones de arriba
    private function mover($repuesto_id, $tipo, $cantidad, $origen, $origen_id, $descripcion) {
        $repuesto_id = (int)$repuesto_id;
        $cantidad    = (int)$cantidad;

        if ($repuesto_id <= 0) return $this->error('Repuesto inválido.');
        if ($tipo !== 'Ajuste' && $cantidad <= 0) return $this->error('La cantidad debe ser mayor a 0.');
        if ($tipo === 'Ajuste' && $cantidad < 0)  return $this->error('El stock no puede ser negativo.');

        Tx::iniciar($this->db);
        try {
            // Leo el repuesto y lo "bloqueo" un instante para que nadie más lo cambie a la vez
            $stmt = $this->db->prepare("SELECT nombre, stock FROM repuestos WHERE id = ? FOR UPDATE");
            $stmt->bind_param('i', $repuesto_id);
            $stmt->execute();
            $fila = $stmt->get_result()->fetch_assoc();

            if (!$fila) {
                Tx::terminar($this->db, false);
                return $this->error('El repuesto no existe.');
            }

            $actual = (int)$fila['stock'];

            // Calculo cuánto queda y cuánto cambió
            if ($tipo === 'Entrada') {
                $nuevo = $actual + $cantidad;
                $delta = $cantidad;
            } elseif ($tipo === 'Salida') {
                if ($actual < $cantidad) {
                    Tx::terminar($this->db, false);
                    return $this->error("Stock insuficiente de \"{$fila['nombre']}\" (hay $actual, se necesitan $cantidad).");
                }
                $nuevo = $actual - $cantidad;
                $delta = $cantidad;
            } else { // Ajuste
                $nuevo = $cantidad;
                $delta = $nuevo - $actual; // puede ser positivo o negativo
                if ($delta === 0) {        // no cambió nada: no hace falta anotar
                    Tx::terminar($this->db, true);
                    return ['ok' => true, 'stock' => $actual, 'movimiento_id' => null];
                }
            }

            // 1) Cambio el número en el repuesto
            $stmt = $this->db->prepare("UPDATE repuestos SET stock = ? WHERE id = ?");
            $stmt->bind_param('ii', $nuevo, $repuesto_id);
            $stmt->execute();

            // 2) Anoto el movimiento (quién, qué, cuánto y de dónde vino)
            if ($descripcion === '') {
                $descripcion = "$tipo de " . abs($delta) . " x {$fila['nombre']}";
            }
            $descripcion = mb_substr($descripcion, 0, 200);
            $usuario_id  = $_SESSION['usuario_id'] ?? null;

            $stmt = $this->db->prepare(
                "INSERT INTO movimientos (repuesto_id, tipo, descripcion, cantidad, usuario_id, origen, origen_id)
                 VALUES (?, ?, ?, ?, ?, ?, ?)"
            );
            $stmt->bind_param('issiisi', $repuesto_id, $tipo, $descripcion, $delta, $usuario_id, $origen, $origen_id);
            $stmt->execute();
            $movimiento_id = $this->db->insert_id;

            Tx::terminar($this->db, true);
            return ['ok' => true, 'stock' => $nuevo, 'movimiento_id' => $movimiento_id];

        } catch (Throwable $e) {
            Tx::terminar($this->db, false);
            return $this->error('Error al actualizar el stock: ' . $e->getMessage());
        }
    }

    private function error($mensaje) {
        return ['ok' => false, 'error' => $mensaje];
    }
}
<?php
// Modelo de las Órdenes de Trabajo: qué mecánico trabajó en qué vehículo y qué repuestos usó
class OrdenTrabajoModel {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConexion();
    }

    // Trae todas las órdenes de trabajo de un vehículo, con el nombre del mecánico
    public function getByVehiculo($vehiculo_id) {
        $vehiculo_id = (int)$vehiculo_id;
        $sql = "SELECT ot.*, p.nombre AS mecanico_nombre, p.apellido AS mecanico_apellido
                FROM orden_trabajo ot
                LEFT JOIN usuario u ON u.id = ot.mecanico_id
                LEFT JOIN persona p ON p.id = u.persona_id
                WHERE ot.vehiculo_id = $vehiculo_id
                ORDER BY ot.fecha DESC";
        $result = $this->db->query($sql);
        $ordenes = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];

        // A cada orden le sumo la lista de repuestos que usó
        foreach ($ordenes as &$orden) {
            $orden['items'] = $this->getItems($orden['id']);
        }
        return $ordenes;
    }

    // Repuestos usados en una orden de trabajo puntual
    public function getItems($orden_trabajo_id) {
        $orden_trabajo_id = (int)$orden_trabajo_id;
        $sql = "SELECT otr.cantidad, r.nombre, r.id AS repuesto_id
                FROM orden_trabajo_repuesto otr
                JOIN repuestos r ON r.id = otr.repuesto_id
                WHERE otr.orden_trabajo_id = $orden_trabajo_id";
        $result = $this->db->query($sql);
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }

    // Crea una orden de trabajo con sus repuestos usados.
    // - Lo que hay en stock se descuenta (pasando por el StockService, que anota el movimiento).
    // - Lo que falta se junta en UN pedido automático por proveedor, ya vinculado
    //   a esta orden y a este vehículo.
    // - Todo ocurre junto: si algo falla, no se guarda nada a medias.
    public function create($vehiculo_id, $mecanico_id, $descripcion, $items) {
        $vehiculo_id = (int)$vehiculo_id;
        $mecanico_id = !empty($mecanico_id) ? (int)$mecanico_id : null;
        $descripcion = $descripcion ?? '';

        $stock       = new StockService();
        $pedidoModel = new PedidoModel();
        $avisos      = [];  // mensajes para mostrarle al mecánico
        $guardados   = 0;   // cuántos repuestos se guardaron de verdad en la orden
        $faltantes   = [];  // lo que falta, agrupado por proveedor

        Tx::iniciar($this->db);
        try {
            // 1) La orden
            $stmt = $this->db->prepare(
                "INSERT INTO orden_trabajo (vehiculo_id, mecanico_id, descripcion, estado)
                 VALUES (?, ?, ?, 'Abierta')"
            );
            $stmt->bind_param('iis', $vehiculo_id, $mecanico_id, $descripcion);
            $stmt->execute();
            $orden_id = $this->db->insert_id;

            // 2) Cada repuesto pedido por el mecánico
            $stmtRep = $this->db->prepare("SELECT nombre, stock, precio, proveedor_id FROM repuestos WHERE id = ?");
            $stmtOtr = $this->db->prepare(
                "INSERT INTO orden_trabajo_repuesto (orden_trabajo_id, repuesto_id, cantidad) VALUES (?, ?, ?)"
            );

            foreach ($items as $item) {
                $repuesto_id = (int)$item['repuesto_id'];
                $cantidad    = max(1, (int)$item['cantidad']);
                if ($repuesto_id <= 0) continue;

                $stmtRep->bind_param('i', $repuesto_id);
                $stmtRep->execute();
                $fila = $stmtRep->get_result()->fetch_assoc();
                // Antes un repuesto no encontrado se saltaba en silencio. Ahora se avisa.
                if (!$fila) throw new Exception("No se encontró el repuesto ID $repuesto_id.");

                $usar   = min($cantidad, (int)$fila['stock']); // lo que realmente hay
                $faltan = $cantidad - $usar;                   // lo que no alcanza

                // Anoto en la orden lo que pidió el mecánico (aunque falte stock)
                $stmtOtr->bind_param('iii', $orden_id, $repuesto_id, $cantidad);
                $stmtOtr->execute();
                $guardados++;

                // Descuento solo lo que había (el StockService anota el movimiento)
                if ($usar > 0) {
                    $res = $stock->salida($repuesto_id, $usar, 'Orden de Trabajo', $orden_id,
                                          "Uso en Orden de Trabajo #$orden_id: $usar x {$fila['nombre']}");
                    if (!$res['ok']) throw new Exception($res['error']);
                }

                // Lo que falta se guarda para pedirlo al proveedor habitual
                if ($faltan > 0) {
                    if (!empty($fila['proveedor_id'])) {
                        $faltantes[$fila['proveedor_id']][] = [
                            'repuesto_id' => $repuesto_id,
                            'cantidad'    => $faltan,
                            'precio'      => $fila['precio'],
                        ];
                        $avisos[] = "Faltaban $faltan de \"{$fila['nombre']}\" — se generó un pedido automático al proveedor.";
                    } else {
                        $avisos[] = "Faltaban $faltan de \"{$fila['nombre']}\" pero no tiene proveedor habitual asignado, no se pudo generar el pedido automático.";
                    }
                }
            }

            // 3) Un pedido automático por proveedor, vinculado a la orden y al vehículo
            foreach ($faltantes as $proveedor_id => $renglones) {
                $pedidoId = $pedidoModel->create([
                    'responsable_pedido' => 'Sistema (automático por Orden de Trabajo #' . $orden_id . ')',
                    'numero_unico'       => rand(1000, 9999),
                    'proveedor_id'       => $proveedor_id,
                    'vehiculo_id'        => $vehiculo_id,
                    'orden_trabajo_id'   => $orden_id,
                ], $renglones);
                if (!$pedidoId) throw new Exception($pedidoModel->error);
            }

            Tx::terminar($this->db, true);
            return ['success' => true, 'id' => $orden_id, 'avisos' => $avisos, 'guardados' => $guardados];

        } catch (Throwable $e) {
            Tx::terminar($this->db, false);
            error_log('[KAYROM OrdenTrabajo] ' . $e->getMessage()); // queda en xampp/apache/logs/error.log
            return ['success' => false, 'error' => $e->getMessage(), 'avisos' => []];
        }
    }

    // Cambiar el estado de una orden (Abierta / Cerrada)
    public function cambiarEstado($id, $estado) {
        $id = (int)$id;
        $estado = $this->db->real_escape_string($estado);
        return $this->db->query("UPDATE orden_trabajo SET estado='$estado' WHERE id=$id");
    }
}
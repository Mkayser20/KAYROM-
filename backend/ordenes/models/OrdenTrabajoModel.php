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

    // Crea una orden de trabajo con sus repuestos usados, y descuenta el stock real
    public function create($vehiculo_id, $mecanico_id, $descripcion, $items) {
        $vehiculo_id = (int)$vehiculo_id;
        $mecanico_id = !empty($mecanico_id) ? (int)$mecanico_id : 'NULL';
        $descripcion = $this->db->real_escape_string($descripcion ?? '');

        // Primero valido que haya stock suficiente de CADA repuesto antes de tocar nada.
        // Si falta uno solo, no creo la orden entera (mejor avisar que dejar la orden a medias).
        foreach ($items as $item) {
            $repuesto_id = (int)$item['repuesto_id'];
            $cantidad    = max(1, (int)$item['cantidad']);
            if ($repuesto_id <= 0) continue;

            $fila = $this->db->query("SELECT nombre, stock FROM repuestos WHERE id = $repuesto_id")->fetch_assoc();
            if (!$fila) {
                return ['error' => 'Uno de los repuestos seleccionados ya no existe.'];
            }
            if ($cantidad > $fila['stock']) {
                return ['error' => "No hay stock suficiente de \"{$fila['nombre']}\" (pediste $cantidad, quedan {$fila['stock']})."];
            }
        }

        $this->db->query(
            "INSERT INTO orden_trabajo (vehiculo_id, mecanico_id, descripcion, estado)
             VALUES ($vehiculo_id, $mecanico_id, '$descripcion', 'Abierta')"
        );
        $orden_id = $this->db->insert_id;

        foreach ($items as $item) {
            $repuesto_id = (int)$item['repuesto_id'];
            $cantidad    = max(1, (int)$item['cantidad']);
            if ($repuesto_id <= 0) continue;

            $this->db->query(
                "INSERT INTO orden_trabajo_repuesto (orden_trabajo_id, repuesto_id, cantidad)
                 VALUES ($orden_id, $repuesto_id, $cantidad)"
            );
            // Ya validé arriba que alcanza, así que descuento tranquilo
            $this->db->query(
                "UPDATE repuestos SET stock = stock - $cantidad WHERE id = $repuesto_id"
            );
        }
        return ['success' => true, 'id' => $orden_id];
    }

    // Cambiar el estado de una orden (Abierta / Cerrada)
    public function cambiarEstado($id, $estado) {
        $id = (int)$id;
        $estado = $this->db->real_escape_string($estado);
        return $this->db->query("UPDATE orden_trabajo SET estado='$estado' WHERE id=$id");
    }
}

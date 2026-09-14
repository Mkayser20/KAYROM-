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
    // Si falta stock de algún repuesto, no bloquea todo: usa lo que hay y genera
    // automáticamente un pedido al proveedor habitual de ese repuesto por la diferencia.
    public function create($vehiculo_id, $mecanico_id, $descripcion, $items) {
        $vehiculo_id = (int)$vehiculo_id;
        $mecanico_id = !empty($mecanico_id) ? (int)$mecanico_id : 'NULL';
        $descripcion = $this->db->real_escape_string($descripcion ?? '');

        $this->db->query(
            "INSERT INTO orden_trabajo (vehiculo_id, mecanico_id, descripcion, estado)
             VALUES ($vehiculo_id, $mecanico_id, '$descripcion', 'Abierta')"
        );
        $orden_id = $this->db->insert_id;

        require_once __DIR__ . '/../../pedidos/model/PedidoModel.php';
        $pedidoModel = new PedidoModel();
        $avisos = []; // mensajes para mostrarle al mecánico sobre lo que se pidió al proveedor

        foreach ($items as $item) {
            $repuesto_id = (int)$item['repuesto_id'];
            $cantidad    = max(1, (int)$item['cantidad']);
            if ($repuesto_id <= 0) continue;

            $fila = $this->db->query("SELECT nombre, stock, proveedor_id FROM repuestos WHERE id = $repuesto_id")->fetch_assoc();
            if (!$fila) continue;

            $disponible = (int)$fila['stock'];
            $usar   = min($cantidad, $disponible); // lo que realmente se puede usar ahora
            $faltan = $cantidad - $usar;            // lo que no alcanza

            // Registro en la orden la cantidad que el mecánico pidió (aunque falte stock)
            $this->db->query(
                "INSERT INTO orden_trabajo_repuesto (orden_trabajo_id, repuesto_id, cantidad)
                 VALUES ($orden_id, $repuesto_id, $cantidad)"
            );

            // Descuento del stock solo lo que efectivamente había
            if ($usar > 0) {
                $this->db->query("UPDATE repuestos SET stock = stock - $usar WHERE id = $repuesto_id");
            }

            // Si faltó, genero un pedido automático al proveedor habitual de ese repuesto
            if ($faltan > 0) {
                if (!empty($fila['proveedor_id'])) {
                    $pedidoModel->create([
                        'estado_pedido'      => 'Pendiente',
                        'responsable_pedido' => 'Sistema (automático por Orden de Trabajo #' . $orden_id . ')',
                        'numero_unico'       => rand(1000, 9999),
                        'cantidad'           => $faltan,
                        'detalle_pedido'     => "Faltante de \"{$fila['nombre']}\" para Orden de Trabajo #$orden_id",
                        'proveedor_id'       => $fila['proveedor_id'],
                    ]);
                    $avisos[] = "Faltaban $faltan de \"{$fila['nombre']}\" — se generó un pedido automático al proveedor.";
                } else {
                    $avisos[] = "Faltaban $faltan de \"{$fila['nombre']}\" pero no tiene proveedor habitual asignado, no se pudo generar el pedido automático.";
                }
            }
        }

        return ['success' => true, 'id' => $orden_id, 'avisos' => $avisos];
    }

    // Cambiar el estado de una orden (Abierta / Cerrada)
    public function cambiarEstado($id, $estado) {
        $id = (int)$id;
        $estado = $this->db->real_escape_string($estado);
        return $this->db->query("UPDATE orden_trabajo SET estado='$estado' WHERE id=$id");
    }
}

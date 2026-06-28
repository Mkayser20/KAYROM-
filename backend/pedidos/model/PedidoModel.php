<?php
//Modelo para gestionar toda la lógica de pedidos en la base de datos
class PedidoModel {
    private $db; //conexión a la base de datos

    public function __construct() {
        $this->db = Database::getInstance()->getConexion();
    }

    //obtener todos los pedidos ordenados por fecha más reciente
    public function getAll() {
        $sql = "SELECT p.*, dp.detalle_pedido
                FROM pedidos p
                LEFT JOIN detalle_pedido dp ON dp.id = p.detalle_pedido_id
                ORDER BY p.fecha_pedidos DESC";
        $result = $this->db->query($sql);
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }

    //obtener los últimos X pedidos (por defecto 5)
    public function getRecent($limit = 5) {
        $limit = (int)$limit;
        $sql = "SELECT p.*, dp.detalle_pedido
                FROM pedidos p
                LEFT JOIN detalle_pedido dp ON dp.id = p.detalle_pedido_id
                ORDER BY p.fecha_pedidos DESC LIMIT $limit";
        $result = $this->db->query($sql);
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }

    //obtener un pedido específico por su ID
    public function getById($id) {
        $id = (int)$id;
        $sql = "SELECT p.*, dp.detalle_pedido FROM pedidos p
                LEFT JOIN detalle_pedido dp ON dp.id = p.detalle_pedido_id
                WHERE p.id = $id";
        $result = $this->db->query($sql);
        return $result ? $result->fetch_assoc() : null;
    }

    //crear un nuevo pedido con su detalle
    public function create($data) {
    $estado      = $this->db->real_escape_string($data['estado_pedido']     ?? 'Pendiente');
    $responsable = $this->db->real_escape_string($data['responsable_pedido'] ?? '');
    $numero      = (int)($data['numero_unico'] ?? rand(1000,9999));
    $cantidad    = (int)($data['cantidad'] ?? 1);
    $detalle     = $this->db->real_escape_string($data['detalle_pedido']    ?? 'Sin detalle');

    //detalle de pedido primero
    $this->db->query(
        "INSERT INTO detalle_pedido (detalle_pedido, productos_id)
         VALUES ('$detalle', 1)"
    );
    $detalle_id = $this->db->insert_id;

    //despues el pedido con el id del detalle
    return $this->db->query(
        "INSERT INTO pedidos (fecha_pedidos, estado_pedido, responsable_pedido, numero_unico, cantidad, detalle_pedido_id)
         VALUES (NOW(), '$estado', '$responsable', $numero, $cantidad, $detalle_id)"
    );
    }
    
    //eliminar un pedido por su ID
    public function delete($id) {
        $id = (int)$id;
        return $this->db->query("DELETE FROM pedidos WHERE id=$id");
    }

    //contar el total de pedidos registrados
    public function getTotalPedidos() {
        $result = $this->db->query("SELECT COUNT(*) as total FROM pedidos");
        return $result ? $result->fetch_assoc()['total'] : 0;
    }

    //obtener cantidad de pedidos agrupados por estado (Pendiente, Entregado, etc)
    public function getCountByEstado() {
        $result = $this->db->query(
            "SELECT estado_pedido as label, COUNT(*) as total FROM pedidos GROUP BY estado_pedido"
        );
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }

    //marcar un pedido como entregado
    public function entregar($id) {
        $id = (int)$id;
    return $this->db->query(
        "UPDATE pedidos SET estado_pedido = 'Entregado' WHERE id = $id"
    );
    }
}

<?php
//Modelo para gestionar repuestos y su inventario
class RepuestoModel {
    private $db; //conexión a la base de datos

    public function __construct() {
        $this->db = Database::getInstance()->getConexion();
    }

    //obtener todos los repuestos ordenados por nombre
    public function getAll() {
        $result = $this->db->query("SELECT * FROM repuestos ORDER BY nombre ASC");
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }

    //obtener un repuesto específico por su ID
    public function getById($id) {
        $stmt = $this->db->prepare("SELECT * FROM repuestos WHERE id = ?");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    //crear un nuevo repuesto
    public function create($data) {
        $stmt = $this->db->prepare("INSERT INTO repuestos (nombre, categoria, stock, stock_minimo, precio) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param('ssiii', $data['nombre'], $data['categoria'], $data['stock'], $data['stock_minimo'], $data['precio']);
        return $stmt->execute();
    }

    //actualizar datos de un repuesto existente
    public function update($id, $data) {
        $stmt = $this->db->prepare("UPDATE repuestos SET nombre=?, categoria=?, stock=?, stock_minimo=?, precio=? WHERE id=?");
        $stmt->bind_param('ssiiid', $data['nombre'], $data['categoria'], $data['stock'], $data['stock_minimo'], $data['precio'], $id);
        return $stmt->execute();
    }

    //eliminar un repuesto por su ID
    public function delete($id) {
        $stmt = $this->db->prepare("DELETE FROM repuestos WHERE id=?");
        $stmt->bind_param('i', $id);
        return $stmt->execute();
    }

    //obtener cantidad total de stock de todos los repuestos
    public function getTotalStock() {
        $result = $this->db->query("SELECT SUM(stock) as total FROM repuestos");
        return $result ? $result->fetch_assoc()['total'] ?? 0 : 0;
    }

    //obtener repuestos con stock bajo (menor o igual al mínimo)
    public function getLowStock() {
        $result = $this->db->query("SELECT * FROM repuestos WHERE stock <= stock_minimo ORDER BY stock ASC");
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }

    //contar cuántos repuestos tienen stock bajo
    public function getLowStockCount() {
        $result = $this->db->query("SELECT COUNT(*) as total FROM repuestos WHERE stock <= stock_minimo");
        return $result ? $result->fetch_assoc()['total'] : 0;
    }

    //contar cuántos repuestos están sin stock (cantidad 0)
    public function getPorComprarCount() {
        $result = $this->db->query("SELECT COUNT(*) as total FROM repuestos WHERE stock = 0");
        return $result ? $result->fetch_assoc()['total'] : 0;
    }
}
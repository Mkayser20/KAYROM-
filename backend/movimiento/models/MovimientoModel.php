<?php
//Modelo para gestionar movimientos de inventario (entradas, salidas, etc)
class MovimientoModel {
    private $db; //conexión a la base de datos

    public function __construct() {
        $this->db = Database::getInstance()->getConexion();
    }

    //obtener todos los movimientos ordenados por fecha descendente (más recientes primero)
    public function getAll() {
        $result = $this->db->query("SELECT * FROM movimientos ORDER BY fecha DESC");
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }

    //obtener los últimos X movimientos (por defecto 5)
    public function getRecent($limit = 5) {
        $stmt = $this->db->prepare("SELECT * FROM movimientos ORDER BY fecha DESC LIMIT ?");
        $stmt->bind_param('i', $limit);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    //crear un nuevo movimiento (entrada/salida de inventario)
    public function create($data) {
        $stmt = $this->db->prepare("INSERT INTO movimientos (tipo, descripcion, cantidad) VALUES (?, ?, ?)");
        $stmt->bind_param('ssi', $data['tipo'], $data['descripcion'], $data['cantidad']);
        return $stmt->execute();
    }

    //eliminar un movimiento por su ID
    public function delete($id) {
        $stmt = $this->db->prepare("DELETE FROM movimientos WHERE id=?");
        $stmt->bind_param('i', $id);
        return $stmt->execute();
    }
}
<?php

class MovimientoModel {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConexion();
    }

    public function getAll() {
        $result = $this->db->query("SELECT * FROM movimientos ORDER BY fecha DESC");
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }

    public function getRecent($limit = 5) {
        $stmt = $this->db->prepare("SELECT * FROM movimientos ORDER BY fecha DESC LIMIT ?");
        $stmt->bind_param('i', $limit);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function create($data) {
        $stmt = $this->db->prepare("INSERT INTO movimientos (tipo, descripcion, cantidad) VALUES (?, ?, ?)");
        $stmt->bind_param('ssi', $data['tipo'], $data['descripcion'], $data['cantidad']);
        return $stmt->execute();
    }

    public function delete($id) {
        $stmt = $this->db->prepare("DELETE FROM movimientos WHERE id=?");
        $stmt->bind_param('i', $id);
        return $stmt->execute();
    }
}
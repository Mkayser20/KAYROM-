<?php
class ProveedorModel {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConexion();
    }

    public function getAll() {
        $sql = "SELECT pr.*, p.nombre, p.apellido
                FROM proveedor pr
                LEFT JOIN persona p ON p.id = pr.persona_id
                ORDER BY pr.nombre_proveedor ASC";
        $result = $this->db->query($sql);
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }

    public function getTotal() {
        $result = $this->db->query("SELECT COUNT(*) as total FROM proveedor");
        return $result ? $result->fetch_assoc()['total'] : 0;
    }
}

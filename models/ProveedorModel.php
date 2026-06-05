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

    public function getById($id) {
    $stmt = $this->db->prepare("SELECT * FROM proveedor WHERE id = ?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

public function create($data) {
    $cuit     = $data['cuit'] ?? 0;
    $nombre   = $this->db->real_escape_string($data['nombre_proveedor'] ?? '');
    $telefono = $data['telefono'] ?? 0;
    $email    = $this->db->real_escape_string($data['email'] ?? '');

    return $this->db->query(
        "INSERT INTO proveedor (cuit, nombre_proveedor, telefono, email)
         VALUES ($cuit, '$nombre', $telefono, '$email')"
    );
}

public function update($id, $data) {
    $cuit     = $data['cuit'] ?? 0;
    $nombre   = $this->db->real_escape_string($data['nombre_proveedor'] ?? '');
    $telefono = $data['telefono'] ?? 0;
    $email    = $this->db->real_escape_string($data['email'] ?? '');

    return $this->db->query(
        "UPDATE proveedor SET 
            cuit = $cuit,
            nombre_proveedor = '$nombre',
            telefono = $telefono,
            email = '$email'
         WHERE id = $id"
    );
}

public function delete($id) {
    $stmt = $this->db->prepare("DELETE FROM proveedor WHERE id = ?");
    $stmt->bind_param('i', $id);
    return $stmt->execute();
}
}

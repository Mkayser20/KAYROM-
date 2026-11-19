<?php
class ProveedorModel {
    private $db;
    public  $error = ''; // último mensaje de error (para mostrarlo en pantalla)

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
        $id = (int)$id;
        $stmt = $this->db->prepare("SELECT * FROM proveedor WHERE id = ?");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    // Revisa y limpia los datos del formulario. Devuelve los datos listos o null si hay error (queda en $this->error)
    // Antes cuit y telefono iban directo a la consulta sin comillas: eso permitía inyección SQL.
    private function limpiarDatos($data) {
        $nombre   = trim((string)($data['nombre_proveedor'] ?? ''));
        $cuit     = preg_replace('/\D/', '', (string)($data['cuit'] ?? ''));     // solo números (saca guiones)
        $telefono = trim((string)($data['telefono'] ?? ''));
        $email    = trim((string)($data['email'] ?? ''));

        if ($nombre === '') {
            $this->error = 'El nombre del proveedor es obligatorio.';
            return null;
        }
        if (mb_strlen($nombre) > 150) {
            $this->error = 'El nombre no puede superar los 150 caracteres.';
            return null;
        }
        if ($cuit !== '' && strlen($cuit) !== 11) {
            $this->error = 'El CUIT tiene que tener 11 números.';
            return null;
        }
        // el teléfono ahora es texto (en la migración se cambió la columna), así no se pierde el 0 de adelante
        if ($telefono !== '' && !preg_match('/^[0-9 +()-]{6,30}$/', $telefono)) {
            $this->error = 'El teléfono solo puede tener números, espacios, +, - y paréntesis.';
            return null;
        }
        if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->error = 'El email no tiene un formato válido.';
            return null;
        }

        return [
            'nombre'   => $nombre,
            'cuit'     => $cuit !== '' ? (int)$cuit : null,          // vacío = NULL en la base
            'telefono' => $telefono !== '' ? $telefono : null,
            'email'    => $email !== '' ? $email : null,
        ];
    }

    // Crea un proveedor. Devuelve el ID nuevo o false (el motivo queda en $this->error)
    public function create($data) {
        $d = $this->limpiarDatos($data);
        if (!$d) return false;

        $stmt = $this->db->prepare(
            "INSERT INTO proveedor (cuit, nombre_proveedor, telefono, email) VALUES (?, ?, ?, ?)"
        );
        $stmt->bind_param('isss', $d['cuit'], $d['nombre'], $d['telefono'], $d['email']);
        return $stmt->execute() ? $this->db->insert_id : false;
    }

    public function update($id, $data) {
        $id = (int)$id;
        $d = $this->limpiarDatos($data);
        if (!$d) return false;

        $stmt = $this->db->prepare(
            "UPDATE proveedor SET cuit = ?, nombre_proveedor = ?, telefono = ?, email = ? WHERE id = ?"
        );
        $stmt->bind_param('isssi', $d['cuit'], $d['nombre'], $d['telefono'], $d['email'], $id);
        return $stmt->execute();
    }

    public function delete($id) {
        $id = (int)$id;
        $stmt = $this->db->prepare(
            "SELECT
                (SELECT COUNT(*) FROM repuestos WHERE proveedor_id = ?) AS repuestos,
                (SELECT COUNT(*) FROM pedidos WHERE proveedor_id = ?) AS pedidos"
        );
        $stmt->bind_param('ii', $id, $id);
        $stmt->execute();
        $asociados = $stmt->get_result()->fetch_assoc();

        if ((int)$asociados['repuestos'] > 0 || (int)$asociados['pedidos'] > 0) {
            return ['error' => 'proveedor_en_uso'];
        }

        $stmt = $this->db->prepare("DELETE FROM proveedor WHERE id = ?");
        $stmt->bind_param('i', $id);
        return $stmt->execute() && $stmt->affected_rows > 0
            ? ['success' => true]
            : ['error' => 'error'];
    }
}

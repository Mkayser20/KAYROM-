<?php
class PermisoModel {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConexion();
    }

    // Devuelve los módulos que el usuario tiene permitidos
    public function getByUsuario($usuario_id) {
        $stmt = $this->db->prepare("SELECT modulo FROM permiso_usuario WHERE usuario_id = ?");
        $stmt->bind_param('i', $usuario_id);
        $stmt->execute();
        $result = $stmt->get_result();

        $modulos = [];
        while ($row = $result->fetch_assoc()) {
            $modulos[] = $row['modulo'];
        }
        return $modulos;
    }

    // Guarda los permisos: borra los viejos e inserta los nuevos
    public function setForUsuario($usuario_id, $modulos) {
        $stmt = $this->db->prepare("DELETE FROM permiso_usuario WHERE usuario_id = ?");
        $stmt->bind_param('i', $usuario_id);
        $stmt->execute();

        if (!empty($modulos)) {
            $stmt = $this->db->prepare("INSERT INTO permiso_usuario (usuario_id, modulo) VALUES (?, ?)");
            foreach ($modulos as $modulo) {
                $stmt->bind_param('is', $usuario_id, $modulo);
                $stmt->execute();
            }
        }
    }

    // Verifica si un usuario tiene acceso a un módulo
    public function tienePermiso($usuario_id, $modulo) {
        $stmt = $this->db->prepare("SELECT id FROM permiso_usuario WHERE usuario_id = ? AND modulo = ?");
        $stmt->bind_param('is', $usuario_id, $modulo);
        $stmt->execute();
        return $stmt->get_result()->num_rows > 0;
    }
}
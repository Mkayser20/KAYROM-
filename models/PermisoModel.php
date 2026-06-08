<?php
//Modelo para gestionar permisos de acceso a módulos por usuario
//Solo los admins pueden acceder a todos. Los empleados tienen permisos limitados
class PermisoModel {
    private $db; //conexión a la base de datos

    public function __construct() {
        $this->db = Database::getInstance()->getConexion();
    }

    //obtener lista de módulos que un usuario tiene permitidos
    public function getByUsuario($usuario_id) {
        $stmt = $this->db->prepare("SELECT modulo FROM permiso_usuario WHERE usuario_id = ?");
        $stmt->bind_param('i', $usuario_id);
        $stmt->execute();
        $result = $stmt->get_result();

        //recolectar todos los módulos del usuario en un array
        $modulos = [];
        while ($row = $result->fetch_assoc()) {
            $modulos[] = $row['modulo'];
        }
        return $modulos;
    }

    //guardar permisos para un usuario: elimina los antiguos e inserta los nuevos
    public function setForUsuario($usuario_id, $modulos) {
        //primero, eliminar todos los permisos anteriores del usuario
        $stmt = $this->db->prepare("DELETE FROM permiso_usuario WHERE usuario_id = ?");
        $stmt->bind_param('i', $usuario_id);
        $stmt->execute();

        //luego, insertar los nuevos permisos (si hay alguno)
        if (!empty($modulos)) {
            $stmt = $this->db->prepare("INSERT INTO permiso_usuario (usuario_id, modulo) VALUES (?, ?)");
            foreach ($modulos as $modulo) {
                $stmt->bind_param('is', $usuario_id, $modulo);
                $stmt->execute();
            }
        }
    }

    //verificar si un usuario tiene acceso a un módulo específico
    public function tienePermiso($usuario_id, $modulo) {
        $stmt = $this->db->prepare("SELECT id FROM permiso_usuario WHERE usuario_id = ? AND modulo = ?");
        $stmt->bind_param('is', $usuario_id, $modulo);
        $stmt->execute();
        //retorna true si encontró registro, false si no
        return $stmt->get_result()->num_rows > 0;
    }
}
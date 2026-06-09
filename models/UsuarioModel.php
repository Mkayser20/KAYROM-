<?php
class UsuarioModel {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConexion();
    }

    //buscar usuario por email(join con persona para traer nombre )
    public function findByEmail($email) {
    $e = $this->db->real_escape_string($email);
    $result = $this->db->query(
        "SELECT u.*, p.nombre, p.apellido, u.rol AS rol_nombre
         FROM usuario u
         LEFT JOIN persona p ON p.id = u.persona_id
         WHERE u.email = '$e' AND u.activo = 1
         LIMIT 1"
    );
    return $result ? $result->fetch_assoc() : null;
    }

    //inserta en persona primero, despues en usuario con la contraseña hasheada 
    public function create($data) {
        $nombre    = $this->db->real_escape_string($data['nombre']    ?? '');
        $apellido  = $this->db->real_escape_string($data['apellido']  ?? '');
        $domicilio = $this->db->real_escape_string($data['domicilio'] ?? '');
        $dni       = $this->db->real_escape_string($data['dni']              ?? '');
        $telefono  = $this->db->real_escape_string($data['telefono_persona'] ?? '');

        $this->db->query(
            "INSERT INTO persona (nombre, apellido, domicilio, dni, telefono_persona)
            VALUES ('$nombre', '$apellido', '$domicilio', '$dni', '$telefono')"
        );
        $persona_id = $this->db->insert_id;

        //insertar usuario con rol
        $nombre_usuario = $this->db->real_escape_string($data['nombre_usuario']    ?? '');
        $email          = $this->db->real_escape_string($data['email']             ?? '');
        $rol            = $this->db->real_escape_string($data['rol']               ?? 'empleado');
        $hash           = $this->db->real_escape_string(
                            password_hash($data['contrasena_usuario'] ?? '', PASSWORD_DEFAULT)
                        );
        $activo = (int)($data['activo'] ?? 1);

        $ok = $this->db->query(
            "INSERT INTO usuario (nombre_usuario, contrasena_usuario, activo, persona_id, email, rol)
            VALUES ('$nombre_usuario', '$hash', $activo, $persona_id, '$email', '$rol')"
        );
        // Devuelve el id del usuario nuevo (para los permisos), o false si falló
        return $ok ? $this->db->insert_id : false;
        }

    //chequea duplicados antes de registrar
    public function existeEmail($email, $excluirId = 0) {
        $e = $this->db->real_escape_string($email);
        $excluirId = (int)$excluirId;
        $r = $this->db->query("SELECT id FROM usuario WHERE email='$e' AND id != $excluirId LIMIT 1");
        return $r && $r->num_rows > 0;
    }

    public function existeUsuario($nombre_usuario, $excluirId = 0) {
        $u = $this->db->real_escape_string($nombre_usuario);
        $excluirId = (int)$excluirId;
        $r = $this->db->query("SELECT id FROM usuario WHERE nombre_usuario='$u' AND id != $excluirId LIMIT 1");
        return $r && $r->num_rows > 0;
    }

    // ¿existe el DNI en la tabla persona? Si pasás $excluirPersonaId, ignora a esa persona
   
    public function existeDni($dni, $excluirPersonaId = 0) {
        $d = $this->db->real_escape_string($dni);
        $excluirPersonaId = (int)$excluirPersonaId;
        $r = $this->db->query("SELECT id FROM persona WHERE dni='$d' AND id != $excluirPersonaId LIMIT 1");
        return $r && $r->num_rows > 0;
    }

    public function getRoles() {
    return [
        ['id' => 'admin',              'descripcion' => 'Administrador'],
        ['id' => 'empleado',           'descripcion' => 'Empleado'],
    ];
    }

    //genera el token de recuperación y lo guarda en la base de datos con su expiración (1 hora)
    public function setTokenRecuperacion(string $email): array|false {
        $stmt = $this->db->prepare(
            "SELECT u.id, p.nombre
             FROM usuario u
             LEFT JOIN persona p ON p.id = u.persona_id
             WHERE u.email = ? AND u.activo = 1
             LIMIT 1"
        );
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            return false;
        }

        $usuario = $result->fetch_assoc();
        $token   = bin2hex(random_bytes(32));

        $upd = $this->db->prepare(
            "UPDATE usuario
             SET token_recuperacion = ?,
                 token_expiracion   = DATE_ADD(NOW(), INTERVAL 1 HOUR)
             WHERE id = ?"
        );
        $upd->bind_param('si', $token, $usuario['id']);
        $upd->execute();

        return ['token' => $token, 'nombre' => $usuario['nombre'] ?? 'Usuario'];
    }


   //busca el usuario por token y verifica que no haya expirado
    public function findByToken(string $token): array|false {
        $stmt = $this->db->prepare(
            "SELECT u.id, u.nombre_usuario, p.nombre
             FROM usuario u
             LEFT JOIN persona p ON p.id = u.persona_id
             WHERE u.token_recuperacion = ? AND u.token_expiracion > NOW()
             LIMIT 1"
        );
        $stmt->bind_param('s', $token);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->num_rows > 0 ? $result->fetch_assoc() : false;
    }

   
//actualiza la contraseña del usuario, y borra el token para que no se pueda usar de nuevo
    public function resetPassword(int $id, string $nuevaPassword): bool {
        $hash = password_hash($nuevaPassword, PASSWORD_DEFAULT);
        $stmt = $this->db->prepare(
            "UPDATE usuario
             SET contrasena_usuario  = ?,
                 token_recuperacion  = NULL,
                 token_expiracion    = NULL
             WHERE id = ?"
        );
        $stmt->bind_param('si', $hash, $id);
        $stmt->execute();
        return $stmt->affected_rows > 0;
    }
}

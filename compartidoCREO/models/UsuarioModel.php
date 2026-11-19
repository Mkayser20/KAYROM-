<?php
class UsuarioModel {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConexion();
    }

    //buscar usuario por email(join con persona para traer nombre )
    public function findByEmail($email) {
        $stmt = $this->db->prepare(
            "SELECT u.*, p.nombre, p.apellido, u.rol AS rol_nombre
             FROM usuario u
             LEFT JOIN persona p ON p.id = u.persona_id
             WHERE u.email = ? AND u.activo = 1
             LIMIT 1"
        );
        $stmt->bind_param('s', $email);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    // Trae solo el estado y el rol de un usuario (index.php lo usa en cada pedido
    // para saber si lo desactivaron o le cambiaron el rol mientras tenía la sesión abierta)
    public function getEstadoSesion($id) {
        $stmt = $this->db->prepare("SELECT activo, rol FROM usuario WHERE id = ?");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    // Deja el rol prolijo: "Admin", " ADMIN " o "admin" quedan como "admin" (el único rol con permisos especiales).
    // Los demás roles son texto libre (máximo 30 letras) y funcionan como empleado.
    public static function normalizarRol($rol) {
        $rol = trim((string)$rol);
        if ($rol === '' ) return 'empleado';
        if (mb_strtolower($rol) === 'admin' || mb_strtolower($rol) === 'administrador') return 'admin';
        if (mb_strtolower($rol) === 'empleado') return 'empleado';
        return mb_substr($rol, 0, 30);
    }

    //inserta en persona primero, despues en usuario con la contraseña hasheada.
    //Ahora va todo junto en una transacción: antes, si fallaba el usuario, quedaba una persona suelta.
    public function create($data) {
        $nombre    = $data['nombre']    ?? '';
        $apellido  = $data['apellido']  ?? '';
        $domicilio = $data['domicilio'] ?? '';
        $dni       = $data['dni']              ?? '';
        $telefono  = $data['telefono_persona'] ?? '';
        $nombre_usuario = $data['nombre_usuario'] ?? '';
        $email          = $data['email']          ?? '';
        $rol            = self::normalizarRol($data['rol'] ?? 'empleado');
        $hash           = password_hash($data['contrasena_usuario'] ?? '', PASSWORD_DEFAULT);
        $activo         = (int)($data['activo'] ?? 1) === 1 ? 1 : 0;

        $this->db->begin_transaction();
        try {
            $stmt = $this->db->prepare(
                "INSERT INTO persona (nombre, apellido, domicilio, dni, telefono_persona) VALUES (?, ?, ?, ?, ?)"
            );
            $stmt->bind_param('sssss', $nombre, $apellido, $domicilio, $dni, $telefono);
            $stmt->execute();
            $persona_id = $this->db->insert_id;

            //insertar usuario con rol
            $stmt = $this->db->prepare(
                "INSERT INTO usuario (nombre_usuario, contrasena_usuario, activo, persona_id, email, rol)
                 VALUES (?, ?, ?, ?, ?, ?)"
            );
            $stmt->bind_param('ssiiss', $nombre_usuario, $hash, $activo, $persona_id, $email, $rol);
            $stmt->execute();
            $usuario_id = $this->db->insert_id;

            $this->db->commit();
            // Devuelve el id del usuario nuevo (para los permisos)
            return $usuario_id;

        } catch (Throwable $e) {
            $this->db->rollback();
            error_log('[KAYROM Usuario] ' . $e->getMessage());
            return false;
        }
    }

    //chequea duplicados antes de registrar
    public function existeEmail($email, $excluirId = 0) {
        return $this->existe("SELECT id FROM usuario WHERE email = ? AND id != ? LIMIT 1", $email, $excluirId);
    }

    public function existeUsuario($nombre_usuario, $excluirId = 0) {
        return $this->existe("SELECT id FROM usuario WHERE nombre_usuario = ? AND id != ? LIMIT 1", $nombre_usuario, $excluirId);
    }

    // Ayudante para los chequeos de duplicados: ejecuta la consulta preparada y dice si encontró algo
    private function existe($sql, $valor, $excluirId) {
        $valor = (string)$valor;
        $excluirId = (int)$excluirId;
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('si', $valor, $excluirId);
        $stmt->execute();
        return $stmt->get_result()->num_rows > 0;
    }

    // ¿existe el DNI en la tabla persona? Si pasás $excluirPersonaId, ignora a esa persona
   
    public function existeDni($dni, $excluirPersonaId = 0) {
        return $this->existe("SELECT id FROM persona WHERE dni = ? AND id != ? LIMIT 1", $dni, $excluirPersonaId);
    }

    // existe el teléfono en la tabla persona? 
    public function existeTelefono($telefono, $excluirPersonaId = 0) {
        return $this->existe("SELECT id FROM persona WHERE telefono_persona = ? AND id != ? LIMIT 1", $telefono, $excluirPersonaId);
    }

    // ---------- LÍMITE DE INTENTOS DE LOGIN ----------
    // Para que nadie pueda probar miles de contraseñas: después de 5 intentos fallidos
    // con el mismo email (o desde la misma IP) en 15 minutos, se bloquea un rato.
    // Usa la tabla intentos_login (se crea con migracion_mejoras.sql). Si la tabla no existe, no bloquea.
    const MAX_INTENTOS = 5;
    const MINUTOS_BLOQUEO = 15;

    public function estaBloqueado($email, $ip) {
        try {
            $stmt = $this->db->prepare(
                "SELECT COUNT(*) AS c FROM intentos_login
                 WHERE (email = ? OR ip = ?) AND fecha > (NOW() - INTERVAL " . self::MINUTOS_BLOQUEO . " MINUTE)"
            );
            $stmt->bind_param('ss', $email, $ip);
            $stmt->execute();
            return (int)$stmt->get_result()->fetch_assoc()['c'] >= self::MAX_INTENTOS;
        } catch (Throwable $e) {
            return false;
        }
    }

    public function registrarIntentoFallido($email, $ip) {
        try {
            $stmt = $this->db->prepare("INSERT INTO intentos_login (email, ip) VALUES (?, ?)");
            $stmt->bind_param('ss', $email, $ip);
            $stmt->execute();
        } catch (Throwable $e) {
            // sin la tabla no pasa nada, simplemente no se cuenta
        }
    }

    // Cuando entra bien, se borran sus intentos fallidos
    public function limpiarIntentos($email) {
        try {
            $stmt = $this->db->prepare("DELETE FROM intentos_login WHERE email = ?");
            $stmt->bind_param('s', $email);
            $stmt->execute();
        } catch (Throwable $e) {
        }
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

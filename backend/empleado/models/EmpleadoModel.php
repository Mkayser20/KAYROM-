<?php
// Modelo de Empleados.
// IMPORTANTE: en todo el módulo el "id" del empleado es el id de la tabla USUARIO.
// Antes se mezclaba con el id de la tabla empleado y se borraba/editaba al empleado equivocado.
// Un empleado = usuario (para entrar al sistema) + persona (sus datos) + empleado (puesto y especialidad).
class EmpleadoModel {
    private $db;
    public  $error = ''; // último mensaje de error (para mostrarlo en pantalla)

    public function __construct() {
        $this->db = Database::getInstance()->getConexion();
    }

    public function getAll() {
        $result = $this->db->query(
            "SELECT u.id, u.nombre_usuario, u.email, u.rol, u.activo,
                    p.nombre, p.apellido, p.domicilio, p.dni, p.telefono_persona,
                    emp.rango_trabajo, emp.especialidad
             FROM usuario u
             LEFT JOIN persona p ON p.id = u.persona_id
             LEFT JOIN empleado emp ON emp.persona_id = p.id
             ORDER BY p.apellido ASC"
        );
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }

    // Solo los activos (para elegir mecánico en una orden de trabajo)
    public function getActivos() {
        return array_values(array_filter($this->getAll(), fn($e) => (int)$e['activo'] === 1));
    }

    // Busca por id de USUARIO
    public function getById($id) {
        $id = (int)$id;
        $stmt = $this->db->prepare(
            "SELECT u.id, u.nombre_usuario, u.email, u.rol, u.activo, u.persona_id,
                    p.nombre, p.apellido, p.domicilio, p.dni, p.telefono_persona,
                    emp.id AS empleado_id, emp.rango_trabajo, emp.especialidad
             FROM usuario u
             LEFT JOIN persona p ON p.id = u.persona_id
             LEFT JOIN empleado emp ON emp.persona_id = u.persona_id
             WHERE u.id = ?"
        );
        $stmt->bind_param('i', $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    // Actualiza persona, usuario y datos de empleado. Todo junto: si algo falla no queda a medias.
    // Antes el WHERE usaba el id tal cual venía de la URL (inyección SQL); ahora todo va con consultas preparadas.
    public function update($id, $data) {
        $this->error = '';
        $id  = (int)$id;
        $emp = $this->getById($id);
        if (!$emp) {
            $this->error = 'El empleado no existe.';
            return false;
        }

        $nombre    = trim($data['nombre']    ?? '');
        $apellido  = trim($data['apellido']  ?? '');
        $domicilio = trim($data['domicilio'] ?? '');
        $dni       = trim($data['dni']       ?? '');
        $telefono  = trim($data['telefono_persona'] ?? '');
        $email     = trim($data['email']          ?? '');
        $usuario   = trim($data['nombre_usuario'] ?? '');
        $rol       = UsuarioModel::normalizarRol($data['rol'] ?? 'empleado');
        $activo    = (int)($data['activo'] ?? 1) === 1 ? 1 : 0;
        $rango     = trim($data['rango_trabajo'] ?? '');
        $especial  = trim($data['especialidad']  ?? '');

        if ($nombre === '' || $email === '' || $usuario === '') {
            $this->error = 'Nombre, email y usuario son obligatorios.';
            return false;
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->error = 'Ingresá un correo electrónico válido.';
            return false;
        }

        $this->db->begin_transaction();
        try {
            // 1) Datos personales (si el usuario no tenía persona, se la creo)
            $persona_id = (int)($emp['persona_id'] ?? 0);
            if ($persona_id > 0) {
                $stmt = $this->db->prepare(
                    "UPDATE persona SET nombre=?, apellido=?, domicilio=?, dni=?, telefono_persona=? WHERE id=?"
                );
                $stmt->bind_param('sssssi', $nombre, $apellido, $domicilio, $dni, $telefono, $persona_id);
                $stmt->execute();
            } else {
                $stmt = $this->db->prepare(
                    "INSERT INTO persona (nombre, apellido, domicilio, dni, telefono_persona) VALUES (?, ?, ?, ?, ?)"
                );
                $stmt->bind_param('sssss', $nombre, $apellido, $domicilio, $dni, $telefono);
                $stmt->execute();
                $persona_id = $this->db->insert_id;
            }

            // 2) Datos de acceso
            $stmt = $this->db->prepare(
                "UPDATE usuario SET email=?, nombre_usuario=?, rol=?, activo=?, persona_id=? WHERE id=?"
            );
            $stmt->bind_param('sssiii', $email, $usuario, $rol, $activo, $persona_id, $id);
            $stmt->execute();

            // 3) Puesto y especialidad (tabla empleado): si no tenía fila, se crea
            if (!empty($emp['empleado_id'])) {
                $empleado_id = (int)$emp['empleado_id'];
                $stmt = $this->db->prepare("UPDATE empleado SET rango_trabajo=?, especialidad=? WHERE id=?");
                $stmt->bind_param('ssi', $rango, $especial, $empleado_id);
            } else {
                $stmt = $this->db->prepare("INSERT INTO empleado (rango_trabajo, especialidad, persona_id) VALUES (?, ?, ?)");
                $stmt->bind_param('ssi', $rango, $especial, $persona_id);
            }
            $stmt->execute();

            $this->db->commit();
            return true;

        } catch (Throwable $e) {
            $this->db->rollback();
            error_log('[KAYROM Empleado] ' . $e->getMessage());
            $this->error = 'No se pudo actualizar el empleado.';
            return false;
        }
    }

    // Activa o desactiva un usuario (lo usa el check de la tabla). No borra nada: solo cambia usuario.activo
    public function cambiarActivo($id, $activo) {
        $id     = (int)$id;
        $activo = $activo ? 1 : 0;

        $stmt = $this->db->prepare("UPDATE usuario SET activo = ? WHERE id = ?");
        $stmt->bind_param('ii', $activo, $id);
        return $stmt->execute();
    }

    // "Eliminar" un empleado = darlo de baja (desactivarlo).
    // No se borra de verdad porque su nombre aparece en auditorías, movimientos y órdenes de trabajo:
    // si se borrara, ese historial quedaría sin saber quién hizo cada cosa.
    public function delete($id) {
        return $this->getById($id) ? $this->cambiarActivo($id, 0) : false;
    }

    // Cantidad de empleados activos (para el dashboard). Antes contaba la tabla empleado,
    // que no coincidía con la lista (la lista muestra usuarios).
    public function getTotal() {
        $result = $this->db->query("SELECT COUNT(*) as total FROM usuario WHERE activo = 1");
        return $result ? $result->fetch_assoc()['total'] : 0;
    }
}

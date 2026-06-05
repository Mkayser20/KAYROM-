<?php
class EmpleadoModel {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConexion();
    }

    public function getAll() {
    $result = $this->db->query(
        "SELECT u.id, u.nombre_usuario, u.email, u.rol, u.activo,
                p.nombre, p.apellido, p.domicilio, p.dni, p.telefono_persona
         FROM usuario u
         LEFT JOIN persona p ON p.id = u.persona_id
         ORDER BY p.apellido ASC"
    );
    return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }

    public function getById($id) {
    $stmt = $this->db->prepare(
        "SELECT u.id, u.nombre_usuario, u.email, u.rol, u.activo, u.persona_id,
                p.nombre, p.apellido, p.domicilio, p.dni, p.telefono_persona
         FROM usuario u
         LEFT JOIN persona p ON p.id = u.persona_id
         WHERE u.id = ?"
    );
    $stmt->bind_param('i', $id);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
    }

    public function create($data) {
        $nombre   = $this->db->real_escape_string($data['nombre']   ?? '');
        $apellido = $this->db->real_escape_string($data['apellido'] ?? '');
        $domicilio = $this->db->real_escape_string($data['domicilio'] ?? '');
        $dni      = $this->db->real_escape_string($data['dni']      ?? '');
        $telefono = $this->db->real_escape_string($data['telefono_persona'] ?? '');

        $this->db->query(
            "INSERT INTO persona (nombre, apellido, domicilio, dni, telefono_persona)
             VALUES ('$nombre', '$apellido', '$domicilio', '$dni', '$telefono')"
        );
        $persona_id = $this->db->insert_id;

        $rango      = $this->db->real_escape_string($data['rango_trabajo']  ?? '');
        $especialidad = $this->db->real_escape_string($data['especialidad'] ?? '');

        return $this->db->query(
            "INSERT INTO empleado (rango_trabajo, especialidad, persona_id)
             VALUES ('$rango', '$especialidad', $persona_id)"
        );
    }

    public function update($id, $data) {
    $emp = $this->getById($id);
    $persona_id = $emp['persona_id'];

    $nombre    = $this->db->real_escape_string($data['nombre']    ?? '');
    $apellido  = $this->db->real_escape_string($data['apellido']  ?? '');
    $domicilio = $this->db->real_escape_string($data['domicilio'] ?? '');
    $dni       = $this->db->real_escape_string($data['dni']       ?? '');
    $telefono  = $this->db->real_escape_string($data['telefono_persona'] ?? '');

    $this->db->query(
        "UPDATE persona SET nombre='$nombre', apellido='$apellido', domicilio='$domicilio',
         dni='$dni', telefono_persona='$telefono' WHERE id=$persona_id"
    );

    $email    = $this->db->real_escape_string($data['email']          ?? '');
    $usuario  = $this->db->real_escape_string($data['nombre_usuario'] ?? '');
    $rol      = $this->db->real_escape_string($data['rol']            ?? 'empleado');
    $activo   = (int)($data['activo'] ?? 1);

    return $this->db->query(
        "UPDATE usuario SET email='$email', nombre_usuario='$usuario',
         rol='$rol', activo=$activo WHERE id=$id"
    );
    }

    public function delete($id) {
    $emp = $this->getById($id);
    if (!$emp) return false;

    $persona_id = $emp['persona_id'] ?? 0;

    $this->db->begin_transaction();

    try {
    
        if ($persona_id) {
            $stmt = $this->db->prepare("DELETE FROM usuario WHERE persona_id = ?");
            $stmt->bind_param("i", $persona_id);
            $stmt->execute();
            $stmt->close();
        }

    
        $stmt = $this->db->prepare("DELETE FROM empleado WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->close();

    
        if ($persona_id) {
            $stmt = $this->db->prepare("DELETE FROM persona WHERE id = ?");
            $stmt->bind_param("i", $persona_id);
            $stmt->execute();
            $stmt->close();
        }

        $this->db->commit();
        return true;

    } catch (Exception $e) {
        $this->db->rollback();
        return false;
    }
        }

    public function getTotal() {
        $result = $this->db->query("SELECT COUNT(*) as total FROM empleado");
        return $result ? $result->fetch_assoc()['total'] : 0;
    }
}
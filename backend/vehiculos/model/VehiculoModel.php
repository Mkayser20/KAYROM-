<?php
class VehiculoModel {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConexion();
    }

    public function getAll() {
        $sql = "SELECT v.*, mv.modelo_vehiculo, mv.anio_vehiculo, tv.tipo_vehiculo
                FROM vehiculo v
                LEFT JOIN modelo_vehiculo mv ON v.modelo_vehiculo_id = mv.id
                LEFT JOIN tipo_vehiculo tv   ON v.tipo_vehiculo_id   = tv.id
                ORDER BY v.id DESC";
        $result = $this->db->query($sql);
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }

    public function getRecent($limit = 5) {
        $limit = (int)$limit;
        $sql = "SELECT v.*, mv.modelo_vehiculo, mv.anio_vehiculo, tv.tipo_vehiculo
                FROM vehiculo v
                LEFT JOIN modelo_vehiculo mv ON v.modelo_vehiculo_id = mv.id
                LEFT JOIN tipo_vehiculo tv   ON v.tipo_vehiculo_id   = tv.id
                ORDER BY v.id DESC LIMIT $limit";
        $result = $this->db->query($sql);
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }

    public function getById($id) {
        $id = (int)$id;
        $sql = "SELECT v.*, mv.modelo_vehiculo, mv.anio_vehiculo, tv.tipo_vehiculo
                FROM vehiculo v
                LEFT JOIN modelo_vehiculo mv ON v.modelo_vehiculo_id = mv.id
                LEFT JOIN tipo_vehiculo tv   ON v.tipo_vehiculo_id   = tv.id
                WHERE v.id = $id";
        $result = $this->db->query($sql);
        return $result ? $result->fetch_assoc() : null;
    }

    public function patenteExiste($patente, $excluirId = null) {
        $patente = $this->db->real_escape_string($patente);
        $sql = "SELECT id FROM vehiculo WHERE patente = '$patente'";
        if ($excluirId !== null) {
            $excluirId = (int)$excluirId;
            $sql .= " AND id != $excluirId";
        }
        $result = $this->db->query($sql);
        return $result && $result->num_rows > 0;
    }

    public function create($data) {
        $patente          = $this->db->real_escape_string($data['patente'] ?? '');
        
        // Validar que la patente no esté vacía
        if (empty($patente)) {
            return ['error' => 'La patente es requerida'];
        }
        
        // Validar que no exista otra patente igual
        if ($this->patenteExiste($patente)) {
            return ['error' => 'Ya existe un vehículo con la patente "' . htmlspecialchars($patente) . '"'];
        }
        
        $numero_chasis    = $this->db->real_escape_string($data['numero_chasis'] ?? '');
        $numero_motor     = $this->db->real_escape_string($data['numero_motor'] ?? '');
        $cantidad         = (int)($data['cantidad_vehiculo'] ?? 0);
        $modelo_id        = (int)($data['modelo_vehiculo_id'] ?? 0);
        $tipo_id          = (int)($data['tipo_vehiculo_id'] ?? 0);
        $compat_id        = (int)($data['compatibilidad_repuestos_id'] ?? 0);
        $fecha            = $this->db->real_escape_string($data['fecha_ingreso'] ?? date('Y-m-d'));
        $success = $this->db->query(
            "INSERT INTO vehiculo (cantidad_vehiculo, patente, numero_chasis, numero_motor,
             fecha_ingreso, compatibilidad_repuestos_id, tipo_vehiculo_id, modelo_vehiculo_id)
             VALUES ($cantidad,'$patente','$numero_chasis','$numero_motor','$fecha',$compat_id,$tipo_id,$modelo_id)"
        );
        return $success ? ['success' => true] : ['error' => 'Error al crear el vehículo'];
    }

    public function update($id, $data) {
        $id               = (int)$id;
        $patente          = $this->db->real_escape_string($data['patente'] ?? '');
        
        // Validar que la patente no esté vacía
        if (empty($patente)) {
            return ['error' => 'La patente es requerida'];
        }
        
        // Validar que no exista otra patente igual (excluyendo el vehículo actual)
        if ($this->patenteExiste($patente, $id)) {
            return ['error' => 'Ya existe un vehículo con la patente "' . htmlspecialchars($patente) . '"'];
        }
        
        $numero_chasis    = $this->db->real_escape_string($data['numero_chasis'] ?? '');
        $numero_motor     = $this->db->real_escape_string($data['numero_motor'] ?? '');
        $cantidad         = (int)($data['cantidad_vehiculo'] ?? 0);
        $modelo_id        = (int)($data['modelo_vehiculo_id'] ?? 0);
        $tipo_id          = (int)($data['tipo_vehiculo_id'] ?? 0);
        $fecha            = $this->db->real_escape_string($data['fecha_ingreso'] ?? date('Y-m-d'));
        $success = $this->db->query(
            "UPDATE vehiculo SET cantidad_vehiculo=$cantidad, patente='$patente',
             numero_chasis='$numero_chasis', numero_motor='$numero_motor',
             fecha_ingreso='$fecha', tipo_vehiculo_id=$tipo_id,
             modelo_vehiculo_id=$modelo_id WHERE id=$id"
        );
        return $success ? ['success' => true] : ['error' => 'Error al actualizar el vehículo'];
    }

    public function delete($id) {
        $id = (int)$id;
        return $this->db->query("DELETE FROM vehiculo WHERE id=$id");
    }

    public function getTotal() {
        $result = $this->db->query("SELECT COUNT(*) as total FROM vehiculo");
        return $result ? $result->fetch_assoc()['total'] : 0;
    }

    public function getModelos() {
        $result = $this->db->query("SELECT * FROM modelo_vehiculo ORDER BY modelo_vehiculo ASC");
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }

    public function getTipos() {
        $result = $this->db->query("SELECT * FROM tipo_vehiculo ORDER BY tipo_vehiculo ASC");
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }

    public function getCountByTipo() {
        $result = $this->db->query(
            "SELECT tv.tipo_vehiculo as label, COUNT(v.id) as total
             FROM vehiculo v
             LEFT JOIN tipo_vehiculo tv ON v.tipo_vehiculo_id = tv.id
             GROUP BY tv.tipo_vehiculo"
        );
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }
}

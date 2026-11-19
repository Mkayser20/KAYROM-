<?php
class VehiculoModel {
    private $db;

    // Estados posibles de un vehículo en el taller (lista cerrada, la usan el desplegable y la validación)
    public static $estadosTaller = ['En Diagnóstico', 'En Reparación', 'Esperando Repuestos', 'Listo para Entrega', 'Entregado'];

    public function __construct() {
        $this->db = Database::getInstance()->getConexion();
    }

    // Devuelve el ID de la última fila insertada (lo necesita el módulo de auditoría)
    public function getLastInsertedId() {
        return $this->db->insert_id;
    }

    public function getAll() {
        $sql = "SELECT v.*, mv.modelo_vehiculo, mv.anio_vehiculo, tv.tipo_vehiculo,
                       cl.nombre AS cliente_nombre, cl.apellido AS cliente_apellido, cl.dni AS cliente_dni, cl.telefono AS cliente_telefono
                FROM vehiculo v
                LEFT JOIN modelo_vehiculo mv ON v.modelo_vehiculo_id = mv.id
                LEFT JOIN tipo_vehiculo tv   ON v.tipo_vehiculo_id   = tv.id
                LEFT JOIN cliente cl         ON v.cliente_id        = cl.id
                ORDER BY v.id DESC";
        $result = $this->db->query($sql);
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }

    public function getRecent($limit = 5) {
        $limit = (int)$limit;
        $sql = "SELECT v.*, mv.modelo_vehiculo, mv.anio_vehiculo, tv.tipo_vehiculo,
                       cl.nombre AS cliente_nombre, cl.apellido AS cliente_apellido, cl.dni AS cliente_dni, cl.telefono AS cliente_telefono
                FROM vehiculo v
                LEFT JOIN modelo_vehiculo mv ON v.modelo_vehiculo_id = mv.id
                LEFT JOIN tipo_vehiculo tv   ON v.tipo_vehiculo_id   = tv.id
                LEFT JOIN cliente cl         ON v.cliente_id        = cl.id
                ORDER BY v.id DESC LIMIT $limit";
        $result = $this->db->query($sql);
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }

    public function getById($id) {
        $id = (int)$id;
        $sql = "SELECT v.*, mv.modelo_vehiculo, mv.anio_vehiculo, tv.tipo_vehiculo,
                       cl.id AS cliente_id, cl.nombre AS cliente_nombre, cl.apellido AS cliente_apellido, cl.dni AS cliente_dni, cl.telefono AS cliente_telefono
                FROM vehiculo v
                LEFT JOIN modelo_vehiculo mv ON v.modelo_vehiculo_id = mv.id
                LEFT JOIN tipo_vehiculo tv   ON v.tipo_vehiculo_id   = tv.id
                LEFT JOIN cliente cl         ON v.cliente_id        = cl.id
                WHERE v.id = $id";
        $result = $this->db->query($sql);
        return $result ? $result->fetch_assoc() : null;
    }

    // Deja la patente en un formato único: mayúsculas y sin espacios ni guiones.
    // Así "ab 123 cd", "AB-123-CD" y "AB123CD" se reconocen como la misma patente.
    public static function normalizarPatente($patente) {
        return strtoupper(preg_replace('/[\s-]+/', '', trim((string)$patente)));
    }

    public function patenteExiste($patente, $excluirId = null) {
        $patente   = self::normalizarPatente($patente);
        $excluirId = (int)$excluirId;
        $stmt = $this->db->prepare(
            "SELECT id FROM vehiculo WHERE REPLACE(REPLACE(UPPER(patente), ' ', ''), '-', '') = ? AND id != ?"
        );
        $stmt->bind_param('si', $patente, $excluirId);
        $stmt->execute();
        return $stmt->get_result()->num_rows > 0;
    }

    // Revisa los datos del formulario y los deja listos para guardar.
    // Devuelve ['error' => '...'] si algo está mal, o el array con los datos limpios.
    private function prepararDatos($data, $id = 0) {
        $patente = self::normalizarPatente($data['patente'] ?? '');

        // Validar que la patente no esté vacía
        if ($patente === '') {
            return ['error' => 'La patente es requerida'];
        }
        if (strlen($patente) > 20) {
            return ['error' => 'La patente no puede superar los 20 caracteres'];
        }
        // Validar que no exista otra patente igual
        if ($this->patenteExiste($patente, $id)) {
            return ['error' => 'Ya existe un vehículo con la patente "' . htmlspecialchars($patente) . '"'];
        }

        $modelo_id = (int)($data['modelo_vehiculo_id'] ?? 0);
        $tipo_id   = (int)($data['tipo_vehiculo_id'] ?? 0);

        // Validar que se haya elegido un modelo y un tipo reales (si no, la FK explota)
        if ($modelo_id <= 0) {
            return ['error' => 'Seleccioná un modelo de la lista'];
        }
        if ($tipo_id <= 0) {
            return ['error' => 'Seleccioná un tipo de vehículo de la lista'];
        }

        // El estado tiene que ser uno de la lista (antes solo se validaba en el desplegable del listado)
        $estado = $data['estado_taller'] ?? 'En Diagnóstico';
        if (!in_array($estado, self::$estadosTaller, true)) {
            return ['error' => 'Elegí un estado de taller válido'];
        }

        // La fecha tiene que ser una fecha real (si no, MySQL da error)
        $fecha = trim((string)($data['fecha_ingreso'] ?? ''));
        if ($fecha === '') {
            $fecha = date('Y-m-d');
        }
        $f = DateTime::createFromFormat('Y-m-d', substr($fecha, 0, 10));
        if (!$f || $f->format('Y-m-d') !== substr($fecha, 0, 10)) {
            return ['error' => 'La fecha de ingreso no es válida'];
        }

        $anio = !empty($data['anio']) ? (int)$data['anio'] : null;
        if ($anio !== null && ($anio < 1900 || $anio > (int)date('Y') + 1)) {
            return ['error' => 'El año del vehículo no es válido'];
        }

        $km = (int)($data['kilometraje'] ?? 0);
        if ($km < 0) {
            return ['error' => 'El kilometraje no puede ser negativo'];
        }

        return [
            'patente'   => $patente,
            'chasis'    => mb_substr(trim((string)($data['numero_chasis'] ?? '')), 0, 20),
            'motor'     => mb_substr(trim((string)($data['numero_motor'] ?? '')), 0, 30),
            'cantidad'  => (int)($data['cantidad_vehiculo'] ?? 0),
            'compat'    => (int)($data['compatibilidad_repuestos_id'] ?? 0),
            'fecha'     => $f->format('Y-m-d'),
            'estado'    => $estado,
            'km'        => $km,
            'anio'      => $anio,
            'cliente'   => !empty($data['cliente_id']) ? (int)$data['cliente_id'] : null,
            'modelo_id' => $modelo_id,
            'tipo_id'   => $tipo_id,
        ];
    }

    public function create($data) {
        $d = $this->prepararDatos($data);
        if (isset($d['error'])) return $d;

        $stmt = $this->db->prepare(
            "INSERT INTO vehiculo (cantidad_vehiculo, patente, numero_chasis, numero_motor,
             fecha_ingreso, compatibilidad_repuestos_id, tipo_vehiculo_id, modelo_vehiculo_id,
             estado_taller, kilometraje, anio, cliente_id)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
        );
        $stmt->bind_param('issssiiisiii', $d['cantidad'], $d['patente'], $d['chasis'], $d['motor'], $d['fecha'],
                          $d['compat'], $d['tipo_id'], $d['modelo_id'], $d['estado'], $d['km'], $d['anio'], $d['cliente']);
        return $stmt->execute() ? ['success' => true, 'id' => $this->db->insert_id] : ['error' => 'Error al crear el vehículo'];
    }

    public function update($id, $data) {
        $id = (int)$id;
        if (!$this->getById($id)) return ['error' => 'El vehículo no existe'];

        $d = $this->prepararDatos($data, $id);
        if (isset($d['error'])) return $d;

        $stmt = $this->db->prepare(
            "UPDATE vehiculo SET cantidad_vehiculo=?, patente=?, numero_chasis=?, numero_motor=?,
             fecha_ingreso=?, tipo_vehiculo_id=?, modelo_vehiculo_id=?, estado_taller=?,
             kilometraje=?, anio=?, cliente_id=? WHERE id=?"
        );
        $stmt->bind_param('issssiisiiii', $d['cantidad'], $d['patente'], $d['chasis'], $d['motor'], $d['fecha'],
                          $d['tipo_id'], $d['modelo_id'], $d['estado'], $d['km'], $d['anio'], $d['cliente'], $id);
        return $stmt->execute() ? ['success' => true] : ['error' => 'Error al actualizar el vehículo'];
    }

    // Cambia solo el estado del taller (lo usa el desplegable del listado)
    public function cambiarEstado($id, $estado) {
        // si el estado no está en la lista, no se guarda
        if (!in_array($estado, self::$estadosTaller, true)) return false;
        $id = (int)$id;

        $stmt = $this->db->prepare("UPDATE vehiculo SET estado_taller = ? WHERE id = ?");
        $stmt->bind_param('si', $estado, $id);
        return $stmt->execute();
    }

    public function delete($id) {
        $id = (int)$id;
        $stmt = $this->db->prepare(
            "SELECT
                (SELECT COUNT(*) FROM orden_trabajo WHERE vehiculo_id = ?) AS ordenes,
                (SELECT COUNT(*) FROM pedidos WHERE vehiculo_id = ?) AS pedidos"
        );
        $stmt->bind_param('ii', $id, $id);
        $stmt->execute();
        $asociados = $stmt->get_result()->fetch_assoc();

        if ((int)$asociados['ordenes'] > 0 || (int)$asociados['pedidos'] > 0) {
            return ['error' => 'vehiculo_en_uso'];
        }

        $stmt = $this->db->prepare("DELETE FROM vehiculo WHERE id = ?");
        $stmt->bind_param('i', $id);
        return $stmt->execute() && $stmt->affected_rows > 0
            ? ['success' => true]
            : ['error' => 'error'];
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

    //obtener cantidad de vehículos agrupados por estado de taller (para el gráfico del dashboard)
    public function getCountByEstadoTaller() {
        $result = $this->db->query(
            "SELECT estado_taller as label, COUNT(*) as total
             FROM vehiculo GROUP BY estado_taller"
        );
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }
}

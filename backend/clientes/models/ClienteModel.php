<?php
// Modelo de Clientes: los dueños de los vehículos que entran al taller
class ClienteModel {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConexion();
    }

    // Trae todos los clientes, con la cantidad de vehículos que tiene cada uno
    public function getAll() {
        $sql = "SELECT c.*, COUNT(v.id) AS cantidad_vehiculos
                FROM cliente c
                LEFT JOIN vehiculo v ON v.cliente_id = c.id
                GROUP BY c.id
                ORDER BY c.apellido ASC, c.nombre ASC";
        $result = $this->db->query($sql);
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }

    // Un cliente puntual
    public function getById($id) {
        $id = (int)$id;
        $stmt = $this->db->prepare("SELECT * FROM cliente WHERE id = ?");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    // Busca un cliente por su DNI (para no duplicarlo si ya está cargado)
    public function getByDni($dni) {
        $dni = $this->db->real_escape_string(trim($dni));
        $result = $this->db->query("SELECT * FROM cliente WHERE dni = '$dni'");
        return $result ? $result->fetch_assoc() : null;
    }

    // Todos los vehículos que tiene un cliente (para su ficha)
    public function getVehiculos($clienteId) {
        $clienteId = (int)$clienteId;
        $sql = "SELECT v.*, mv.modelo_vehiculo, tv.tipo_vehiculo
                FROM vehiculo v
                LEFT JOIN modelo_vehiculo mv ON v.modelo_vehiculo_id = mv.id
                LEFT JOIN tipo_vehiculo tv ON v.tipo_vehiculo_id = tv.id
                WHERE v.cliente_id = $clienteId
                ORDER BY v.id DESC";
        $result = $this->db->query($sql);
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }

    // Crea un cliente nuevo desde el ABM de Clientes
    public function create($data) {
        $nombre   = trim($data['nombre']   ?? '');
        $apellido = trim($data['apellido'] ?? '');
        $dni      = trim($data['dni']      ?? '');
        $telefono = trim($data['telefono'] ?? '');

        if ($nombre === '' || $apellido === '' || $dni === '') {
            return ['error' => 'Nombre, apellido y DNI son requeridos'];
        }
        if ($this->getByDni($dni)) {
            return ['error' => 'Ya existe un cliente con ese DNI'];
        }

        $stmt = $this->db->prepare("INSERT INTO cliente (nombre, apellido, dni, telefono) VALUES (?, ?, ?, ?)");
        $stmt->bind_param('ssss', $nombre, $apellido, $dni, $telefono);
        $ok = $stmt->execute();
        return $ok ? ['success' => true, 'id' => $this->db->insert_id] : ['error' => 'No se pudo crear el cliente'];
    }

    // Busca un cliente por DNI; si no existe, lo crea. Se usa al cargar un vehículo nuevo,
    // para no obligar a entrar primero al módulo de Clientes por separado.
    public function crearOEncontrar($nombre, $apellido, $dni, $telefono) {
        $dni = trim($dni);
        if ($dni === '') {
            return null; // sin DNI no se puede asociar ni crear cliente
        }
        $existente = $this->getByDni($dni);
        if ($existente) {
            return $existente['id'];
        }
        $resultado = $this->create([
            'nombre' => $nombre, 'apellido' => $apellido, 'dni' => $dni, 'telefono' => $telefono
        ]);
        return $resultado['id'] ?? null;
    }

    // Edita los datos de un cliente
    public function update($id, $data) {
        $id       = (int)$id;
        $nombre   = trim($data['nombre']   ?? '');
        $apellido = trim($data['apellido'] ?? '');
        $dni      = trim($data['dni']      ?? '');
        $telefono = trim($data['telefono'] ?? '');

        if ($nombre === '' || $apellido === '' || $dni === '') {
            return ['error' => 'Nombre, apellido y DNI son requeridos'];
        }
        $otro = $this->getByDni($dni);
        if ($otro && $otro['id'] != $id) {
            return ['error' => 'Ya existe otro cliente con ese DNI'];
        }

        $stmt = $this->db->prepare("UPDATE cliente SET nombre=?, apellido=?, dni=?, telefono=? WHERE id=?");
        $stmt->bind_param('ssssi', $nombre, $apellido, $dni, $telefono, $id);
        $ok = $stmt->execute();
        return $ok ? ['success' => true] : ['error' => 'No se pudo actualizar el cliente'];
    }

    // Elimina un cliente, siempre que no tenga vehículos cargados
    public function delete($id) {
        $id = (int)$id;
        $enUso = $this->db->query("SELECT COUNT(*) AS c FROM vehiculo WHERE cliente_id=$id")->fetch_assoc();
        if ($enUso['c'] > 0) {
            return ['error' => 'No se puede eliminar: tiene ' . $enUso['c'] . ' vehículo(s) asociado(s)'];
        }
        $ok = $this->db->query("DELETE FROM cliente WHERE id=$id");
        return $ok ? ['success' => true] : ['error' => 'No se pudo eliminar el cliente'];
    }
}

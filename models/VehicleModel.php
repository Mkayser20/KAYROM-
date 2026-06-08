<?php
//Modelo alternativo para gestionar vehículos (usa PDO en lugar de mysqli)
//Nota: Este modelo parece ser una versión anterior - se recomienda usar VehiculoModel en su lugar
class VehicleModel {
    private $pdo; //conexión PDO a la base de datos

    public function __construct() {
        $this->pdo = Database::getInstance()->getConnection();
    }

    //obtener todos los vehículos ordenados por fecha de creación descendente
    public function getAll() {
        return $this->pdo->query("SELECT * FROM vehiculos ORDER BY created_at DESC")->fetchAll();
    }

    //obtener los últimos X vehículos (por defecto 5)
    public function getRecent($limit = 5) {
        $stmt = $this->pdo->prepare("SELECT * FROM vehiculos ORDER BY created_at DESC LIMIT ?");
        $stmt->execute([$limit]);
        return $stmt->fetchAll();
    }

    //obtener un vehículo específico por su ID
    public function getById($id) {
        $stmt = $this->pdo->prepare("SELECT * FROM vehiculos WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    //crear un nuevo vehículo
    public function create($data) {
        $stmt = $this->pdo->prepare("INSERT INTO vehiculos (marca, modelo, anio, estado) VALUES (?, ?, ?, ?)");
        return $stmt->execute([$data['marca'], $data['modelo'], $data['anio'], $data['estado']]);
    }

    //actualizar datos de un vehículo existente
    public function update($id, $data) {
        $stmt = $this->pdo->prepare("UPDATE vehiculos SET marca=?, modelo=?, anio=?, estado=? WHERE id=?");
        return $stmt->execute([$data['marca'], $data['modelo'], $data['anio'], $data['estado'], $id]);
    }

    //eliminar un vehículo por su ID
    public function delete($id) {
        $stmt = $this->pdo->prepare("DELETE FROM vehiculos WHERE id=?");
        return $stmt->execute([$id]);
    }

    //contar total de vehículos registrados
    public function getTotal() {
        return $this->pdo->query("SELECT COUNT(*) as total FROM vehiculos")->fetch()['total'];
    }

    //obtener cantidad de vehículos agrupados por estado
    public function getCountByEstado() {
        return $this->pdo->query("SELECT estado, COUNT(*) as total FROM vehiculos GROUP BY estado")->fetchAll();
    }
}

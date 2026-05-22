<?php
// models/VehicleModel.php

class VehicleModel {
    private $pdo;

    public function __construct() {
        $this->pdo = Database::getInstance()->getConnection();
    }

    public function getAll() {
        return $this->pdo->query("SELECT * FROM vehiculos ORDER BY created_at DESC")->fetchAll();
    }

    public function getRecent($limit = 5) {
        $stmt = $this->pdo->prepare("SELECT * FROM vehiculos ORDER BY created_at DESC LIMIT ?");
        $stmt->execute([$limit]);
        return $stmt->fetchAll();
    }

    public function getById($id) {
        $stmt = $this->pdo->prepare("SELECT * FROM vehiculos WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function create($data) {
        $stmt = $this->pdo->prepare("INSERT INTO vehiculos (marca, modelo, anio, estado) VALUES (?, ?, ?, ?)");
        return $stmt->execute([$data['marca'], $data['modelo'], $data['anio'], $data['estado']]);
    }

    public function update($id, $data) {
        $stmt = $this->pdo->prepare("UPDATE vehiculos SET marca=?, modelo=?, anio=?, estado=? WHERE id=?");
        return $stmt->execute([$data['marca'], $data['modelo'], $data['anio'], $data['estado'], $id]);
    }

    public function delete($id) {
        $stmt = $this->pdo->prepare("DELETE FROM vehiculos WHERE id=?");
        return $stmt->execute([$id]);
    }

    public function getTotal() {
        return $this->pdo->query("SELECT COUNT(*) as total FROM vehiculos")->fetch()['total'];
    }

    public function getCountByEstado() {
        return $this->pdo->query("SELECT estado, COUNT(*) as total FROM vehiculos GROUP BY estado")->fetchAll();
    }
}

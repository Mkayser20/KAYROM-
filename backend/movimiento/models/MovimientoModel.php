<?php
// Modelo de Movimientos: SOLO LEE el historial de stock.
// Los movimientos ya no se crean ni se borran a mano desde acá:
// los escribe el StockService cada vez que cambia el stock.
// (Un historial que se puede borrar no sirve como prueba de lo que pasó.)
class MovimientoModel {
    private $db; //conexión a la base de datos

    public function __construct() {
        $this->db = Database::getInstance()->getConexion();
    }

    // Todos los movimientos, del más nuevo al más viejo,
    // con el nombre del repuesto y del usuario que lo hizo
    public function getAll() {
        $sql = "SELECT m.*, r.nombre AS repuesto_nombre, u.nombre_usuario
                FROM movimientos m
                LEFT JOIN repuestos r ON r.id = m.repuesto_id
                LEFT JOIN usuario u   ON u.id = m.usuario_id
                ORDER BY m.fecha DESC, m.id DESC";
        $result = $this->db->query($sql);
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }

    // Los últimos X movimientos (por defecto 5)
    public function getRecent($limit = 5) {
        $stmt = $this->db->prepare(
            "SELECT m.*, r.nombre AS repuesto_nombre
             FROM movimientos m
             LEFT JOIN repuestos r ON r.id = m.repuesto_id
             ORDER BY m.fecha DESC, m.id DESC LIMIT ?"
        );
        $stmt->bind_param('i', $limit);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    // Un movimiento puntual (este método faltaba y rompía el controlador)
    public function getById($id) {
        $stmt = $this->db->prepare("SELECT * FROM movimientos WHERE id = ?");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }
}
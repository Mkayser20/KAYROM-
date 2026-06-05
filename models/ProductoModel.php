<?php
class ProductoModel {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConexion();
    }

    public function getAll() {
        $sql = "SELECT p.*, s.cantidad_disponible, s.alerta_stockBajo
                FROM productos p
                LEFT JOIN stock s ON s.productos_id = p.id
                ORDER BY p.nombre_producto ASC";
        $result = $this->db->query($sql);
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }

    public function getById($id) {
        $id = (int)$id;
        $sql = "SELECT p.*, s.id as stock_id, s.cantidad_disponible, s.alerta_stockBajo
                FROM productos p
                LEFT JOIN stock s ON s.productos_id = p.id
                WHERE p.id = $id";
        $result = $this->db->query($sql);
        return $result ? $result->fetch_assoc() : null;
    }

    public function create($data) {
        $nombre   = $this->db->real_escape_string($data['nombre_producto']);
        $cantidad = (int)$data['cantidad_producto'];
        $costo    = (float)$data['costo_producto'];
        $tipo     = $this->db->real_escape_string($data['tipo_producto']);
        $ok = $this->db->query(
            "INSERT INTO productos (nombre_producto, cantidad_producto, costo_producto, tipo_producto)
             VALUES ('$nombre', $cantidad, $costo, '$tipo')"
        );
        if ($ok) {
            $pid      = $this->db->insert_id;
            $alerta   = (int)($data['alerta_stockBajo'] ?? 5);
            $disponible = $cantidad;
            $this->db->query(
                "INSERT INTO stock (alerta_stockBajo, cantidad_disponible, productos_id)
                 VALUES ($alerta, $disponible, $pid)"
            );
        }
        return $ok;
    }

    public function update($id, $data) {
        $id     = (int)$id;
        $nombre = $this->db->real_escape_string($data['nombre_producto']);
        $cantidad = (int)$data['cantidad_producto'];
        $costo  = (float)$data['costo_producto'];
        $tipo   = $this->db->real_escape_string($data['tipo_producto']);
        $this->db->query(
            "UPDATE productos SET nombre_producto='$nombre', cantidad_producto=$cantidad,
             costo_producto=$costo, tipo_producto='$tipo' WHERE id=$id"
        );
        $alerta   = (int)($data['alerta_stockBajo'] ?? 5);
        $disponible = (int)($data['cantidad_disponible'] ?? $cantidad);
        $this->db->query(
            "UPDATE stock SET alerta_stockBajo=$alerta, cantidad_disponible=$disponible
             WHERE productos_id=$id"
        );
        return true;
    }

    public function delete($id) {
        $id = (int)$id;
        $this->db->query("DELETE FROM stock WHERE productos_id=$id");
        return $this->db->query("DELETE FROM productos WHERE id=$id");
    }

    public function getTotalStock() {
        $result = $this->db->query("SELECT SUM(cantidad_disponible) as total FROM stock");
        return $result ? ($result->fetch_assoc()['total'] ?? 0) : 0;
    }

    public function getLowStock() {
        $sql = "SELECT p.*, s.cantidad_disponible, s.alerta_stockBajo
                FROM productos p
                JOIN stock s ON s.productos_id = p.id
                WHERE s.cantidad_disponible <= s.alerta_stockBajo
                ORDER BY s.cantidad_disponible ASC";
        $result = $this->db->query($sql);
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }

    public function getLowStockCount() {
        $result = $this->db->query(
            "SELECT COUNT(*) as total FROM stock WHERE cantidad_disponible <= alerta_stockBajo"
        );
        return $result ? $result->fetch_assoc()['total'] : 0;
    }

    public function getSinStockCount() {
        $result = $this->db->query(
            "SELECT COUNT(*) as total FROM stock WHERE cantidad_disponible = 0"
        );
        return $result ? $result->fetch_assoc()['total'] : 0;
    }
}

<?php
//Modelo para gestionar productos y su stock en la base de datos
class ProductoModel {
    private $db; //conexión a la base de datos

    public function __construct() {
        $this->db = Database::getInstance()->getConexion();
    }

    //obtener todos los productos con información de stock
    public function getAll() {
        $sql = "SELECT p.*, s.cantidad_disponible, s.alerta_stockBajo
                FROM productos p
                LEFT JOIN stock s ON s.productos_id = p.id
                ORDER BY p.nombre_producto ASC";
        $result = $this->db->query($sql);
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }

    //obtener un producto específico por su ID
    public function getById($id) {
        $id = (int)$id;
        $sql = "SELECT p.*, s.id as stock_id, s.cantidad_disponible, s.alerta_stockBajo
                FROM productos p
                LEFT JOIN stock s ON s.productos_id = p.id
                WHERE p.id = $id";
        $result = $this->db->query($sql);
        return $result ? $result->fetch_assoc() : null;
    }

    //crear un nuevo producto con su registro de stock asociado
    public function create($data) {
        $nombre   = $this->db->real_escape_string($data['nombre_producto']);
        $cantidad = (int)$data['cantidad_producto'];
        $costo    = (float)$data['costo_producto'];
        $tipo     = $this->db->real_escape_string($data['tipo_producto']);
        //insertar producto en la tabla
        $ok = $this->db->query(
            "INSERT INTO productos (nombre_producto, cantidad_producto, costo_producto, tipo_producto)
             VALUES ('$nombre', $cantidad, $costo, '$tipo')"
        );
        //si se insertó correctamente, crear su registro de stock
        if ($ok) {
            $pid      = $this->db->insert_id; //obtener ID del producto insertado
            $alerta   = (int)($data['alerta_stockBajo'] ?? 5); //cantidad de alerta para stock bajo
            $disponible = $cantidad;
            //crear registro de stock para el producto
            $this->db->query(
                "INSERT INTO stock (alerta_stockBajo, cantidad_disponible, productos_id)
                 VALUES ($alerta, $disponible, $pid)"
            );
        }
        return $ok;
    }

    //actualizar producto existente y su stock
    public function update($id, $data) {
        $id     = (int)$id;
        $nombre = $this->db->real_escape_string($data['nombre_producto']);
        $cantidad = (int)$data['cantidad_producto'];
        $costo  = (float)$data['costo_producto'];
        $tipo   = $this->db->real_escape_string($data['tipo_producto']);
        //actualizar datos del producto
        $this->db->query(
            "UPDATE productos SET nombre_producto='$nombre', cantidad_producto=$cantidad,
             costo_producto=$costo, tipo_producto='$tipo' WHERE id=$id"
        );
        //actualizar stock del producto
        $alerta   = (int)($data['alerta_stockBajo'] ?? 5);
        $disponible = (int)($data['cantidad_disponible'] ?? $cantidad);
        $this->db->query(
            "UPDATE stock SET alerta_stockBajo=$alerta, cantidad_disponible=$disponible
             WHERE productos_id=$id"
        );
        return true;
    }

    //eliminar un producto y su stock asociado
    public function delete($id) {
        $id = (int)$id;
        //primero eliminar el stock del producto
        $this->db->query("DELETE FROM stock WHERE productos_id=$id");
        //luego eliminar el producto
        return $this->db->query("DELETE FROM productos WHERE id=$id");
    }

    //obtener cantidad total de stock disponible en el sistema
    public function getTotalStock() {
        $result = $this->db->query("SELECT SUM(cantidad_disponible) as total FROM stock");
        return $result ? ($result->fetch_assoc()['total'] ?? 0) : 0;
    }

    //obtener productos con stock bajo (menor o igual a su alerta)
    public function getLowStock() {
        $sql = "SELECT p.*, s.cantidad_disponible, s.alerta_stockBajo
                FROM productos p
                JOIN stock s ON s.productos_id = p.id
                WHERE s.cantidad_disponible <= s.alerta_stockBajo
                ORDER BY s.cantidad_disponible ASC";
        $result = $this->db->query($sql);
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }

    //contar cuántos productos tienen stock bajo
    public function getLowStockCount() {
        $result = $this->db->query(
            "SELECT COUNT(*) as total FROM stock WHERE cantidad_disponible <= alerta_stockBajo"
        );
        return $result ? $result->fetch_assoc()['total'] : 0;
    }

    //contar cuántos productos están sin stock (cantidad 0)
    public function getSinStockCount() {
        $result = $this->db->query(
            "SELECT COUNT(*) as total FROM stock WHERE cantidad_disponible = 0"
        );
        return $result ? $result->fetch_assoc()['total'] : 0;
    }
}

<?php
//Modelo para gestionar repuestos y su inventario
class RepuestoModel {
    private $db; //conexión a la base de datos

    public function __construct() {
        $this->db = Database::getInstance()->getConexion();
    }

    //obtener todos los repuestos ordenados por nombre (con el nombre del proveedor, si tiene)
    public function getAll() {
        $result = $this->db->query(
            "SELECT r.*, pr.nombre_proveedor
             FROM repuestos r
             LEFT JOIN proveedor pr ON pr.id = r.proveedor_id
             ORDER BY r.nombre ASC"
        );
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }

    //obtener un repuesto específico por su ID
    public function getById($id) {
        $stmt = $this->db->prepare("SELECT * FROM repuestos WHERE id = ?");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    //crear un nuevo repuesto
    public function create($data) {
        if ($this->validarDatos($data) !== null) {
            return false;
        }
        $nombre = trim($data['nombre']);
        $stock = (int)($data['stock'] ?? 0);
        $stock_minimo = (int)$data['stock_minimo'];
        $precio = (float)$data['precio'];
        $sku = $data['sku'] ?? null;
        $proveedor_id = !empty($data['proveedor_id']) ? (int)$data['proveedor_id'] : null;
        $stmt = $this->db->prepare("INSERT INTO repuestos (nombre, categoria, stock, stock_minimo, precio, sku, proveedor_id) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param('ssiidsi', $nombre, $data['categoria'], $stock, $stock_minimo, $precio, $sku, $proveedor_id);
        $exito = $stmt->execute();
        // Devuelvo el ID real del repuesto recién creado (lo necesita la auditoría), no true/false
        return $exito ? $this->db->insert_id : false;
    }

    //actualizar datos de un repuesto existente
    public function update($id, $data) {
        if ($this->validarDatos($data) !== null) {
            return false;
        }
        $nombre = trim($data['nombre']);
        $stock = (int)($data['stock'] ?? 0);
        $stock_minimo = (int)$data['stock_minimo'];
        $precio = (float)$data['precio'];
        $sku = $data['sku'] ?? null;
        $proveedor_id = !empty($data['proveedor_id']) ? (int)$data['proveedor_id'] : null;
        $stmt = $this->db->prepare("UPDATE repuestos SET nombre=?, categoria=?, stock=?, stock_minimo=?, precio=?, sku=?, proveedor_id=? WHERE id=?");
        $stmt->bind_param('ssiidsii', $nombre, $data['categoria'], $stock, $stock_minimo, $precio, $sku, $proveedor_id, $id);
        return $stmt->execute();
    }

    public function validarDatos($data) {
        $nombreEntrada = $data['nombre'] ?? null;
        if (!is_string($nombreEntrada)) {
            return 'El nombre del repuesto es obligatorio.';
        }
        $nombre = trim($nombreEntrada);
        if ($nombre === '') {
            return 'El nombre del repuesto es obligatorio.';
        }
        if (mb_strlen($nombre, 'UTF-8') > 100) {
            return 'El nombre no puede superar los 100 caracteres.';
        }

        $categoria = $data['categoria'] ?? null;
        if (!is_string($categoria) || trim($categoria) === '') {
            return 'La categoría del repuesto es obligatoria.';
        }
        if (mb_strlen(trim($categoria), 'UTF-8') > 45) {
            return 'La categoría no puede superar los 45 caracteres.';
        }

        $precio = $data['precio'] ?? null;
        if (!is_scalar($precio) || !is_numeric($precio) || !is_finite((float)$precio)
            || (float)$precio < 0 || (float)$precio > 99999999.99) {
            return 'El precio debe ser un número mayor o igual a cero.';
        }

        $stockMinimoEntrada = $data['stock_minimo'] ?? null;
        if (!is_string($stockMinimoEntrada) && !is_int($stockMinimoEntrada)) {
            return 'El stock mínimo debe ser un entero mayor o igual a cero.';
        }
        $stockMinimo = filter_var($stockMinimoEntrada, FILTER_VALIDATE_INT);
        if ($stockMinimo === false || $stockMinimo < 0) {
            return 'El stock mínimo debe ser un entero mayor o igual a cero.';
        }

        return null;
    }

    //guardar (o quitar, pasando null) la imagen de un repuesto: se guarda solo el nombre del archivo
    public function setImagen($id, $nombreArchivo) {
        $stmt = $this->db->prepare("UPDATE repuestos SET imagen=? WHERE id=?");
        $stmt->bind_param('si', $nombreArchivo, $id);
        return $stmt->execute();
    }

    //saber si la tabla ya tiene la columna de imagen (se agrega con migracion_imagen_repuestos.sql)
    public function tieneColumnaImagen() {
        $result = $this->db->query("SHOW COLUMNS FROM repuestos LIKE 'imagen'");
        return $result && $result->num_rows > 0;
    }

    //eliminar un repuesto por su ID
    public function delete($id) {
        $stmt = $this->db->prepare("DELETE FROM repuestos WHERE id=?");
        $stmt->bind_param('i', $id);
        return $stmt->execute();
    }

    public function getReferencias($id) {
        $stmt = $this->db->prepare(
            "SELECT
                (SELECT COUNT(*) FROM orden_trabajo_repuesto WHERE repuesto_id = ?) AS ordenes,
                (SELECT COUNT(*) FROM pedido_item WHERE repuesto_id = ?) AS pedidos,
                (SELECT COUNT(*) FROM carrito WHERE repuesto_id = ?) AS carritos"
        );
        $stmt->bind_param('iii', $id, $id, $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    //obtener la lista de categorías realmente cargadas (para el filtro del listado)
    public function getCategorias() {
        $result = $this->db->query("SELECT DISTINCT categoria FROM repuestos WHERE categoria IS NOT NULL AND categoria <> '' ORDER BY categoria ASC");
        return $result ? array_column($result->fetch_all(MYSQLI_ASSOC), 'categoria') : [];
    }

    //obtener cantidad de repuestos agrupados por categoría (para el gráfico del dashboard)
    public function getCountByCategoria() {
        $result = $this->db->query(
            "SELECT categoria as label, COUNT(*) as total
             FROM repuestos GROUP BY categoria"
        );
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }

    //obtener cantidad total de stock de todos los repuestos
    public function getTotalStock() {
        $result = $this->db->query("SELECT SUM(stock) as total FROM repuestos");
        return $result ? $result->fetch_assoc()['total'] ?? 0 : 0;
    }

    //obtener repuestos con stock bajo (menor o igual al mínimo)
    public function getLowStock() {
        $result = $this->db->query("SELECT * FROM repuestos WHERE stock <= stock_minimo ORDER BY stock ASC");
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }

    //contar cuántos repuestos tienen stock bajo
    public function getLowStockCount() {
        $result = $this->db->query("SELECT COUNT(*) as total FROM repuestos WHERE stock <= stock_minimo");
        return $result ? $result->fetch_assoc()['total'] : 0;
    }

    //contar cuántos repuestos están sin stock (cantidad 0)
    public function getPorComprarCount() {
        $result = $this->db->query("SELECT COUNT(*) as total FROM repuestos WHERE stock = 0");
        return $result ? $result->fetch_assoc()['total'] : 0;
    }
}
<?php
// Modelo del carrito: maneja los repuestos que cada usuario va agregando
class CarritoModel {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConexion();
    }

    // Trae todos los ítems del carrito de UN usuario (con datos del repuesto)
    public function getByUsuario($usuario_id) {
        $usuario_id = (int)$usuario_id;
        $stmt = $this->db->prepare(
            "SELECT c.id, c.cantidad, c.repuesto_id,
                    r.nombre, r.precio, r.stock, r.proveedor_id
             FROM carrito c
             JOIN repuestos r ON r.id = c.repuesto_id
             WHERE c.usuario_id = ?
             ORDER BY c.fecha_agregado DESC"
        );
        $stmt->bind_param('i', $usuario_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    // Agrega un repuesto al carrito. Si ya está, le suma cantidad.
    // Devuelve false si el repuesto no existe (antes eso tiraba un error de la base).
    public function agregar($usuario_id, $repuesto_id, $cantidad = 1) {
        $usuario_id  = (int)$usuario_id;
        $repuesto_id = (int)$repuesto_id;
        $cantidad    = max(1, min(9999, (int)$cantidad)); // entre 1 y 9999

        // ¿existe el repuesto?
        $stmt = $this->db->prepare("SELECT id FROM repuestos WHERE id = ?");
        $stmt->bind_param('i', $repuesto_id);
        $stmt->execute();
        if ($stmt->get_result()->num_rows === 0) return false;

        // ya tiene ese repuesto en el carrito?
        $stmt = $this->db->prepare("SELECT id, cantidad FROM carrito WHERE usuario_id = ? AND repuesto_id = ? LIMIT 1");
        $stmt->bind_param('ii', $usuario_id, $repuesto_id);
        $stmt->execute();
        $fila = $stmt->get_result()->fetch_assoc();

        if ($fila) {
            // ya está: se suma la cantidad
            $nuevaCantidad = (int)$fila['cantidad'] + $cantidad;
            $stmt = $this->db->prepare("UPDATE carrito SET cantidad = ? WHERE id = ?");
            $stmt->bind_param('ii', $nuevaCantidad, $fila['id']);
        } else {
            // no está: se inserta
            $stmt = $this->db->prepare("INSERT INTO carrito (usuario_id, repuesto_id, cantidad) VALUES (?, ?, ?)");
            $stmt->bind_param('iii', $usuario_id, $repuesto_id, $cantidad);
        }
        return $stmt->execute();
    }

    // Quita un ítem puntual del carrito (solo si es de ese usuario)
    public function quitar($id, $usuario_id) {
        $id = (int)$id;
        $usuario_id = (int)$usuario_id;
        $stmt = $this->db->prepare("DELETE FROM carrito WHERE id = ? AND usuario_id = ?");
        $stmt->bind_param('ii', $id, $usuario_id);
        return $stmt->execute();
    }

    // Cambia la cantidad de un ítem sumando $delta (+1 o -1). Si llega a 0 se borra.
    public function cambiarCantidad($id, $usuario_id, $delta) {
        $id         = (int)$id;
        $usuario_id = (int)$usuario_id;
        $delta      = (int)$delta > 0 ? 1 : -1; // solo se sube o baja de a uno

        // Traigo la cantidad actual (validando que el ítem sea de ese usuario)
        $stmt = $this->db->prepare("SELECT cantidad FROM carrito WHERE id = ? AND usuario_id = ? LIMIT 1");
        $stmt->bind_param('ii', $id, $usuario_id);
        $stmt->execute();
        $fila = $stmt->get_result()->fetch_assoc();
        if (!$fila) return false;

        $nueva = (int)$fila['cantidad'] + $delta;

        if ($nueva <= 0) {
            // Si la cantidad llega a 0, se elimina el ítem del carrito
            return $this->quitar($id, $usuario_id);
        }
        $stmt = $this->db->prepare("UPDATE carrito SET cantidad = ? WHERE id = ? AND usuario_id = ?");
        $stmt->bind_param('iii', $nueva, $id, $usuario_id);
        return $stmt->execute();
    }

    // Vacía todo el carrito de un usuario (se usa al confirmar el pedido)
    public function vaciar($usuario_id) {
        $usuario_id = (int)$usuario_id;
        $stmt = $this->db->prepare("DELETE FROM carrito WHERE usuario_id = ?");
        $stmt->bind_param('i', $usuario_id);
        return $stmt->execute();
    }
}

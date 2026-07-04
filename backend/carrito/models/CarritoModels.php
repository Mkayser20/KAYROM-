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
        $sql = "SELECT c.id, c.cantidad, c.repuesto_id,
                       r.nombre, r.precio, r.stock
                FROM carrito c
                JOIN repuestos r ON r.id = c.repuesto_id
                WHERE c.usuario_id = $usuario_id
                ORDER BY c.fecha_agregado DESC";
        $r = $this->db->query($sql);
        return $r ? $r->fetch_all(MYSQLI_ASSOC) : [];
    }

    // Agrega un repuesto al carrito. Si ya está, le suma cantidad.
    public function agregar($usuario_id, $repuesto_id, $cantidad = 1) {
        $usuario_id  = (int)$usuario_id;
        $repuesto_id = (int)$repuesto_id;
        $cantidad    = (int)$cantidad;

        // ya tiene ese repuesto en el carrito?
        $existe = $this->db->query(
            "SELECT id, cantidad FROM carrito
             WHERE usuario_id = $usuario_id AND repuesto_id = $repuesto_id LIMIT 1"
        );

        if ($existe && $existe->num_rows > 0) {
            // ya está: se suma la cantidad
            $fila = $existe->fetch_assoc();
            $nuevaCantidad = $fila['cantidad'] + $cantidad;
            return $this->db->query(
                "UPDATE carrito SET cantidad = $nuevaCantidad WHERE id = {$fila['id']}"
            );
        } else {
            // no está: se inserto
            return $this->db->query(
                "INSERT INTO carrito (usuario_id, repuesto_id, cantidad)
                 VALUES ($usuario_id, $repuesto_id, $cantidad)"
            );
        }
    }

    // Quita un ítem puntual del carrito
    public function quitar($id, $usuario_id) {
        $id = (int)$id;
        $usuario_id = (int)$usuario_id;
        return $this->db->query(
            "DELETE FROM carrito WHERE id = $id AND usuario_id = $usuario_id"
        );
    }

    // Cambia la cantidad de un ítem sumando $delta (+1 o -1). Si llega a 0 se  borra.
    public function cambiarCantidad($id, $usuario_id, $delta) {
        $id         = (int)$id;
        $usuario_id = (int)$usuario_id;
        $delta      = (int)$delta;

        // Traigo la cantidad actual (validando que el ítem sea de ese usuario)
        $r = $this->db->query(
            "SELECT cantidad FROM carrito WHERE id = $id AND usuario_id = $usuario_id LIMIT 1"
        );
        if (!$r || $r->num_rows === 0) return false;

        $fila  = $r->fetch_assoc();
        $nueva = $fila['cantidad'] + $delta;

        if ($nueva <= 0) {
            // Si la cantidad llega a 0, se elimina el ítem del carrito
            return $this->db->query("DELETE FROM carrito WHERE id = $id AND usuario_id = $usuario_id");
        }
        return $this->db->query("UPDATE carrito SET cantidad = $nueva WHERE id = $id AND usuario_id = $usuario_id");
    }

    // Vacía todo el carrito de un usuario (se usa al confirmar el pedido)
    public function vaciar($usuario_id) {
        $usuario_id = (int)$usuario_id;
        return $this->db->query("DELETE FROM carrito WHERE usuario_id = $usuario_id");
    }
}
<?php
// Controlador del carrito: agregar, ver, quitar y confirmar
class CarritoController {
    private $model;

    public function __construct() {
        $this->model = new CarritoModel();
    }

    // Ver el carrito del usuario logueado
    public function index() {
        $usuario_id = $_SESSION['usuario_id'] ?? 0;
        $items = $this->model->getByUsuario($usuario_id);
        require_once 'backend/carrito/views/carrito.php';
    }

    // Agregar un repuesto al carrito
    public function agregar() {
        $usuario_id  = $_SESSION['usuario_id'] ?? 0;
        $repuesto_id = $_POST['repuesto_id'] ?? $_GET['id'] ?? 0;
        $cantidad    = (int)($_POST['cantidad'] ?? 1);
        if ($cantidad < 1) $cantidad = 1;

        $this->model->agregar($usuario_id, $repuesto_id, $cantidad);

        // Vuelve al carrito después de agregar
        header('Location: index.php?page=repuestos&msg=agregado');
        exit;
    }

    // Quitar un ítem del carrito
    public function quitar() {
        $usuario_id = $_SESSION['usuario_id'] ?? 0;
        $id = $_GET['id'] ?? 0;

        $this->model->quitar($id, $usuario_id);

        header('Location: index.php?page=carrito');
        exit;
    }

    // Sube o baja la cantidad de un ítem (delta = +1 o -1)
    public function cambiar() {
        $usuario_id = $_SESSION['usuario_id'] ?? 0;
        $id    = $_GET['id'] ?? 0;
        $delta = (int)($_GET['delta'] ?? 0);

        $this->model->cambiarCantidad($id, $usuario_id, $delta);

        header('Location: index.php?page=carrito');
        exit;
    }
}
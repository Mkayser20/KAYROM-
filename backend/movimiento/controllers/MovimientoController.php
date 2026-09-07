<?php
require_once __DIR__ . '/../../auditoria/AuditoriaModel.php';

// Controlador del módulo de movimientos - maneja CRUD de movimientos de inventario
class MovimientoController {
    private $model;     // modelo de movimientos
    private $auditoria; // modelo de auditoría

    public function __construct() {
        $this->model = new MovimientoModel();
        $this->auditoria = new AuditoriaModel();
    }

    // mostrar lista de todos los movimientos
    public function index() {
        $data = [
            'movimientos' => $this->model->getAll(), // obtener todos los movimientos
            'activePage'  => 'movimientos'
        ];
        require_once 'backend/movimiento/views/movimientos.php';
    }

    // crear un nuevo movimiento (entrada/salida)
    public function create() {
        // si es POST, guardar nuevo movimiento
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $nuevoId = $this->model->create($_POST);

            $tipo        = trim($_POST['tipo'] ?? 'Movimiento');
            $descripcion = trim($_POST['descripcion'] ?? '');
            $cantidad    = trim($_POST['cantidad'] ?? '0');

            // AUDITORÍA: Registro de creación
            $this->auditoria->registrar(
                'CREAR',
                'Movimientos',
                "Registro de $tipo: $descripcion (Cantidad: $cantidad)",
                $nuevoId
            );

            // redirigir con mensaje de éxito
            header('Location: index.php?page=movimientos&msg=created');
            exit;
        }
        // mostrar formulario vacío
        $data = ['activePage' => 'movimientos'];
        require_once 'backend/movimiento/views/movimiento_form.php';
    }

    // eliminar un movimiento
    public function delete() {
        $id = $_GET['id'] ?? 0;

        // Obtenemos los datos antes de eliminar para el detalle de la auditoría
        $movimiento = $this->model->getById($id);
        $detalle = isset($movimiento['descripcion']) 
            ? "{$movimiento['tipo']} - {$movimiento['descripcion']}" 
            : "ID $id";

        if ($this->model->delete($id)) {
            // AUDITORÍA: Registro de eliminación
            $this->auditoria->registrar(
                'ELIMINAR',
                'Movimientos',
                "Baja de movimiento: $detalle",
                $id
            );
        }

        // redirigir a lista con mensaje de éxito
        header('Location: index.php?page=movimientos&msg=deleted');
        exit;
    }
}
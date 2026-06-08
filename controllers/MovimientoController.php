<?php
//Controlador del módulo de movimientos - maneja CRUD de movimientos de inventario
class MovimientoController {
    private $model; //modelo de movimientos

    public function __construct() {
        $this->model = new MovimientoModel();
    }

    //mostrar lista de todos los movimientos
    public function index() {
        $data = [
            'movimientos' => $this->model->getAll(), //obtener todos los movimientos
            'activePage'  => 'movimientos'
        ];
        require_once 'views/movimientos.php';
    }

    //crear un nuevo movimiento (entrada/salida)
    public function create() {
        //si es POST, guardar nuevo movimiento
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->model->create($_POST);
            //redirigir con mensaje de éxito
            header('Location: index.php?page=movimientos&msg=created');
            exit;
        }
        //mostrar formulario vacío
        $data = ['activePage' => 'movimientos'];
        require_once 'views/movimiento_form.php';
    }

    //eliminar un movimiento
    public function delete() {
        $id = $_GET['id'] ?? 0;
        $this->model->delete($id);
        //redirigir a lista con mensaje de éxito
        header('Location: index.php?page=movimientos&msg=deleted');
        exit;
    }
}

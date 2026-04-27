<?php
class MovimientoController {
    private $model;

    public function __construct() {
        $this->model = new MovimientoModel();
    }

    public function index() {
        $data = [
            'movimientos' => $this->model->getAll(),
            'activePage'  => 'movimientos'
        ];
        require_once 'views/movimientos.php';
    }

    public function create() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->model->create($_POST);
            header('Location: index.php?page=movimientos&msg=created');
            exit;
        }
        $data = ['activePage' => 'movimientos'];
        require_once 'views/movimiento_form.php';
    }

    public function delete() {
        $id = $_GET['id'] ?? 0;
        $this->model->delete($id);
        header('Location: index.php?page=movimientos&msg=deleted');
        exit;
    }
}

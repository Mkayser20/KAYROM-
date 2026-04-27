<?php
class RepuestoController {
    private $model;
    private $movimientoModel;

    public function __construct() {
        $this->model = new RepuestoModel();
        $this->movimientoModel = new MovimientoModel();
    }

    public function index() {
        $data = [
            'repuestos'  => $this->model->getAll(),
            'activePage' => 'repuestos'
        ];
        require_once 'views/repuestos.php';
    }

    public function create() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->model->create($_POST);
            $this->movimientoModel->create([
                'tipo'        => 'Entrada',
                'descripcion' => 'Alta de repuesto: ' . $_POST['nombre'],
                'cantidad'    => $_POST['stock']
            ]);
            header('Location: index.php?page=repuestos&msg=created');
            exit;
        }
        $data = ['activePage' => 'repuestos'];
        require_once 'views/repuesto_form.php';
    }

    public function edit() {
        $id = $_GET['id'] ?? 0;
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->model->update($id, $_POST);
            header('Location: index.php?page=repuestos&msg=updated');
            exit;
        }
        $data = [
            'repuesto'   => $this->model->getById($id),
            'activePage' => 'repuestos'
        ];
        require_once 'views/repuesto_form.php';
    }

    public function delete() {
        $id = $_GET['id'] ?? 0;
        $this->model->delete($id);
        header('Location: index.php?page=repuestos&msg=deleted');
        exit;
    }
}

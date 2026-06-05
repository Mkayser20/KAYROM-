<?php
class ProveedorController {
    private $model;

    public function __construct() {
        $this->model = new ProveedorModel();
    }

    public function index() {
        $data = [
            'proveedores' => $this->model->getAll(),
            'activePage'  => 'proveedores'
        ];
        require_once 'views/proveedor.php';
    }

    public function create() {
        $error = '';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if ($this->model->create($_POST)) {
                header('Location: index.php?page=proveedores&msg=created');
                exit;
            } else {
                $error = 'Error al guardar el proveedor.';
            }
        }
        $data = ['error' => $error, 'activePage' => 'proveedores'];
        require_once 'views/proveedor_form.php';
    }

    public function edit() {
        $id = $_GET['id'] ?? 0;
        $error = '';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if ($this->model->update($id, $_POST)) {
                header('Location: index.php?page=proveedores&msg=updated');
                exit;
            } else {
                $error = 'Error al actualizar.';
            }
        }
        $data = [
            'proveedor' => $this->model->getById($id),
            'error' => $error,
            'activePage' => 'proveedores'
        ];
        require_once 'views/proveedor_form.php';
    }

    public function delete() {
        $id = $_GET['id'] ?? 0;
        $this->model->delete($id);
        header('Location: index.php?page=proveedores&msg=deleted');
        exit;
    }
}
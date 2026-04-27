<?php

class EmpleadoController {
    private $model;

    public function __construct() {
        $this->model = new EmpleadoModel();
    }

    public function index() {
        $data = [
            'empleados' => $this->model->getAll(),
            'activePage' => 'empleados'
        ];
        require_once 'views/empleados.php';
    }

    public function create() {
        $error = '';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if ($this->model->create($_POST)) {
                header('Location: index.php?page=empleados&msg=created');
                exit;
            } else {
                $error = 'Error al guardar el empleado.';
            }
        }
        $data = ['error' => $error, 'activePage' => 'empleados'];
        require_once 'views/empleado_form.php';
    }

    public function edit() {
        $id = $_GET['id'] ?? 0;
        $error = '';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if ($this->model->update($id, $_POST)) {
                header('Location: index.php?page=empleados&msg=updated');
                exit;
            } else {
                $error = 'Error al actualizar.';
            }
        }
        $data = [
            'empleado' => $this->model->getById($id),
            'error' => $error,
            'activePage' => 'empleados'
        ];
        require_once 'views/empleado_form.php';
    }

    public function delete() {
        $id = $_GET['id'] ?? 0;
        $this->model->delete($id);
        header('Location: index.php?page=empleados&msg=deleted');
        exit;
    }
}
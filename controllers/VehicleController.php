<?php
// controllers/VehicleController.php

class VehicleController {
    private $model;

    public function __construct() {
        $this->model = new VehicleModel();
    }

    public function index() {
        $data = [
            'vehiculos'  => $this->model->getAll(),
            'activePage' => 'vehiculos'
        ];
        require_once 'views/vehiculos.php';
    }

    public function create() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->model->create($_POST);
            header('Location: index.php?page=vehiculos&msg=created');
            exit;
        }
        $data = ['activePage' => 'vehiculos'];
        require_once 'views/vehiculo_form.php';
    }

    public function edit() {
        $id = $_GET['id'] ?? 0;
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->model->update($id, $_POST);
            header('Location: index.php?page=vehiculos&msg=updated');
            exit;
        }
        $data = [
            'vehiculo'   => $this->model->getById($id),
            'activePage' => 'vehiculos'
        ];
        require_once 'views/vehiculo_form.php';
    }

    public function delete() {
        $id = $_GET['id'] ?? 0;
        $this->model->delete($id);
        header('Location: index.php?page=vehiculos&msg=deleted');
        exit;
    }
}

<?php
class VehiculoController {
    private $model;

    public function __construct() {
        $this->model = new VehiculoModel();
    }

    public function index() {
        $data = [
            'vehiculos'  => $this->model->getAll(),
            'activePage' => 'vehiculos'
        ];
        require_once 'views/vehiculos_listado.php';
    }

    public function create() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $result = $this->model->create($_POST);
            if (isset($result['error'])) {
                $data = [
                    'modelos'    => $this->model->getModelos(),
                    'tipos'      => $this->model->getTipos(),
                    'activePage' => 'vehiculos',
                    'error'      => $result['error'],
                    'formData'   => $_POST
                ];
                require_once 'views/vehiculo_form.php';
                return;
            }
            header('Location: index.php?page=vehiculos&msg=created');
            exit;
        }
        $data = [
            'modelos'    => $this->model->getModelos(),
            'tipos'      => $this->model->getTipos(),
            'activePage' => 'vehiculos'
        ];
        require_once 'views/vehiculo_form.php';
    }

    public function edit() {
        $id = (int)($_GET['id'] ?? 0);
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $result = $this->model->update($id, $_POST);
            if (isset($result['error'])) {
                $data = [
                    'vehiculo'   => $this->model->getById($id),
                    'modelos'    => $this->model->getModelos(),
                    'tipos'      => $this->model->getTipos(),
                    'activePage' => 'vehiculos',
                    'error'      => $result['error'],
                    'formData'   => $_POST
                ];
                require_once 'views/vehiculo_form.php';
                return;
            }
            header('Location: index.php?page=vehiculos&msg=updated');
            exit;
        }
        $data = [
            'vehiculo'   => $this->model->getById($id),
            'modelos'    => $this->model->getModelos(),
            'tipos'      => $this->model->getTipos(),
            'activePage' => 'vehiculos'
        ];
        require_once 'views/vehiculo_form.php';
    }

    public function delete() {
        $id = (int)($_GET['id'] ?? 0);
        $this->model->delete($id);
        header('Location: index.php?page=vehiculos&msg=deleted');
        exit;
    }
}

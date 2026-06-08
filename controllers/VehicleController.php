<?php
//Controlador de vehículos (versión alternativa usando VehicleModel con PDO)
//Maneja operaciones CRUD de vehículos
class VehicleController {
    private $model; //modelo de vehículos

    public function __construct() {
        $this->model = new VehicleModel();
    }

    //mostrar lista de todos los vehículos
    public function index() {
        $data = [
            'vehiculos'  => $this->model->getAll(), //obtener todos los vehículos
            'activePage' => 'vehiculos'
        ];
        require_once 'views/vehiculos.php';
    }

    //crear un nuevo vehículo
    public function create() {
        //si es POST, guardar nuevo vehículo
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->model->create($_POST);
            //redirigir con mensaje de éxito
            header('Location: index.php?page=vehiculos&msg=created');
            exit;
        }
        //mostrar formulario vacío
        $data = ['activePage' => 'vehiculos'];
        require_once 'views/vehiculo_form.php';
    }

    //editar un vehículo existente
    public function edit() {
        $id = $_GET['id'] ?? 0;
        //si es POST, actualizar vehículo
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->model->update($id, $_POST);
            //redirigir con mensaje de éxito
            header('Location: index.php?page=vehiculos&msg=updated');
            exit;
        }
        //mostrar formulario con datos del vehículo
        $data = [
            'vehiculo'   => $this->model->getById($id),
            'activePage' => 'vehiculos'
        ];
        require_once 'views/vehiculo_form.php';
    }

    //eliminar un vehículo
    public function delete() {
        $id = $_GET['id'] ?? 0;
        $this->model->delete($id);
        //redirigir a lista con mensaje de éxito
        header('Location: index.php?page=vehiculos&msg=deleted');
        exit;
    }
}

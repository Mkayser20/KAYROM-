<?php
//Controlador del módulo de vehículos - maneja las operaciones CRUD (Create, Read, Update, Delete)
class VehiculoController {
    private $model; //modelo de vehículos para acceder a la base de datos

    public function __construct() {
        $this->model = new VehiculoModel();
    }

    //mostrar lista de todos los vehículos
    public function index() {
        $data = [
            'vehiculos'  => $this->model->getAll(),  //obtener todos los vehículos
            'activePage' => 'vehiculos' //marcar página activa
        ];
        //cargar vista con lista de vehículos
        require_once 'backend/vehiculos/views/vehiculos_listado.php';
    }

    //crear un nuevo vehículo
    public function create() {
        //si es formulario POST, guardar nuevo vehículo
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $result = $this->model->create($_POST);
            //si hubo error, mostrar formulario con error
            if (isset($result['error'])) {
                $data = [
                    'modelos'    => $this->model->getModelos(),  //para llenar seleccionables del formulario
                    'tipos'      => $this->model->getTipos(),    //para llenar seleccionables del formulario
                    'activePage' => 'vehiculos',
                    'error'      => $result['error'],
                    'formData'   => $_POST //retornar datos ingresados para que no se pierdan
                ];
                require_once 'backend/vehiculos/views/vehiculo_form.php';
                return;
            }
            //éxito: redirigir a lista con mensaje
            header('Location: index.php?page=vehiculos&msg=created');
            exit;
        }
        //mostrar formulario vacío para crear
        $data = [
            'modelos'    => $this->model->getModelos(),
            'tipos'      => $this->model->getTipos(),
            'activePage' => 'vehiculos'
        ];
        require_once 'backend/vehiculos/views/vehiculo_form.php';
    }

    //editar un vehículo existente
    public function edit() {
        $id = (int)($_GET['id'] ?? 0); //obtener ID del vehículo a editar
        //si es formulario POST, actualizar vehículo
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $result = $this->model->update($id, $_POST);
            //si hubo error, mostrar formulario con error
            if (isset($result['error'])) {
                $data = [
                    'vehiculo'   => $this->model->getById($id),  //cargar datos actuales
                    'modelos'    => $this->model->getModelos(),
                    'tipos'      => $this->model->getTipos(),
                    'activePage' => 'vehiculos',
                    'error'      => $result['error'],
                    'formData'   => $_POST
                ];
                require_once 'backend/vehiculos/views/vehiculo_form.php';
                return;
            }
            //éxito: redirigir a lista con mensaje
            header('Location: index.php?page=vehiculos&msg=updated');
            exit;
        }
        //mostrar formulario con datos del vehículo
        $data = [
            'vehiculo'   => $this->model->getById($id),
            'modelos'    => $this->model->getModelos(),
            'tipos'      => $this->model->getTipos(),
            'activePage' => 'vehiculos'
        ];
        require_once 'backend/vehiculos/views/vehiculo_form.php';
    }

    //eliminar un vehículo
    public function delete() {
        $id = (int)($_GET['id'] ?? 0); //obtener ID del vehículo a eliminar
        $this->model->delete($id);
        //redirigir a lista con mensaje de éxito
        header('Location: index.php?page=vehiculos&msg=deleted');
        exit;
    }
}

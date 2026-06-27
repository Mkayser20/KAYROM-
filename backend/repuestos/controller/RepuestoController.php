<?php
//Controlador del módulo de repuestos - maneja CRUD de repuestos y registra movimientos
class RepuestoController {
    private $model;               //modelo de repuestos
    private $movimientoModel;     //modelo para registrar movimientos de inventario

    public function __construct() {
        $this->model = new RepuestoModel();
        $this->movimientoModel = new MovimientoModel();
    }

    //mostrar lista de todos los repuestos
    public function index() {
        $data = [
            'repuestos'  => $this->model->getAll(),  //obtener todos los repuestos
            'activePage' => 'repuestos'
        ];
        require_once 'backend/repuestos/views/repuestos.php';
    }

    //crear un nuevo repuesto
    public function create() {
        //si es POST, guardar nuevo repuesto
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->model->create($_POST);
            //registrar movimiento de entrada de repuesto
            $this->movimientoModel->create([
                'tipo'        => 'Entrada', //tipo de movimiento: Entrada
                'descripcion' => 'Alta de repuesto: ' . $_POST['nombre'], //descripción con nombre del repuesto
                'cantidad'    => $_POST['stock'] //cantidad de stock inicial
            ]);
            //redirigir con mensaje de éxito
            header('Location: index.php?page=repuestos&msg=created');
            exit;
        }
        //mostrar formulario vacío
        $data = ['activePage' => 'repuestos'];
        require_once 'backend/repuestos/views/repuesto_form.php';
    }

    //editar un repuesto existente
    public function edit() {
        $id = $_GET['id'] ?? 0;
        //si es POST, actualizar repuesto
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->model->update($id, $_POST);
            //redirigir con mensaje de éxito
            header('Location: index.php?page=repuestos&msg=updated');
            exit;
        }
        //mostrar formulario con datos del repuesto
        $data = [
            'repuesto'   => $this->model->getById($id),
            'activePage' => 'repuestos'
        ];
        require_once 'backend/repuestos/views/repuesto_form.php';
    }

    //eliminar un repuesto
    public function delete() {
        $id = $_GET['id'] ?? 0;
        $this->model->delete($id);
        //redirigir a lista con mensaje de éxito
        header('Location: index.php?page=repuestos&msg=deleted');
        exit;
    }
}

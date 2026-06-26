<?php
//Controlador del módulo de proveedores - maneja CRUD de proveedores
class ProveedorController {
    private $model; //modelo de proveedores

    public function __construct() {
        $this->model = new ProveedorModel();
    }

    //mostrar lista de todos los proveedores
    public function index() {
        $data = [
            'proveedores' => $this->model->getAll(),  //obtener todos los proveedores
            'activePage'  => 'proveedores'
        ];
        require_once 'funcionalidades/proveedor/views/proveedor.php';
    }

    //crear un nuevo proveedor
    public function create() {
        $error = '';
        //si es POST, guardar nuevo proveedor
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if ($this->model->create($_POST)) {
                //éxito: redirigir a lista con mensaje
                header('Location: index.php?page=proveedores&msg=created');
                exit;
            } else {
                $error = 'Error al guardar el proveedor.';
            }
        }
        //mostrar formulario vacío con error si lo hay
        $data = ['error' => $error, 'activePage' => 'proveedores'];
        require_once 'funcionalidades/proveedor/views/proveedor_form.php';
    }

    //editar un proveedor existente
    public function edit() {
        $id = $_GET['id'] ?? 0;
        $error = '';
        //si es POST, actualizar proveedor
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if ($this->model->update($id, $_POST)) {
                //éxito: redirigir a lista con mensaje
                header('Location: index.php?page=proveedores&msg=updated');
                exit;
            } else {
                $error = 'Error al actualizar.';
            }
        }
        //mostrar formulario con datos del proveedor
        $data = [
            'proveedor' => $this->model->getById($id),
            'error' => $error,
            'activePage' => 'proveedores'
        ];
        require_once 'funcionalidades/proveedor/views/proveedor_form.php';
    }

    //eliminar un proveedor
    public function delete() {
        $id = $_GET['id'] ?? 0;
        $this->model->delete($id);
        //redirigir a lista con mensaje de éxito
        header('Location: index.php?page=proveedores&msg=deleted');
        exit;
    }
}
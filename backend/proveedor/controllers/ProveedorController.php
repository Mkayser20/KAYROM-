<?php
require_once __DIR__ . '/../../auditoria/AuditoriaModel.php';

//Controlador del módulo de proveedores - maneja CRUD de proveedores
class ProveedorController {
    private $model; //modelo de proveedores
    private $auditoria;

    public function __construct() {
        $this->model = new ProveedorModel();
        $this->auditoria = new AuditoriaModel();
    }

    //mostrar lista de todos los proveedores
    public function index() {
        $data = [
            'proveedores' => $this->model->getAll(),  //obtener todos los proveedores
            'activePage'  => 'proveedores'
        ];
        require_once 'backend/proveedor/views/proveedor.php';
    }

    //crear un nuevo proveedor
    public function create() {
        $error = '';
        //si es POST, guardar nuevo proveedor
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if ($this->model->create($_POST)) {
                $nombreProveedor = trim($_POST['nombre_proveedor'] ?? '');
                $this->auditoria->registrar(
                    'CREAR',
                    'Proveedores',
                    "Alta de proveedor: $nombreProveedor"
                );
                //éxito: redirigir a lista con mensaje
                header('Location: index.php?page=proveedores&msg=created');
                exit;
            } else {
                $error = 'Error al guardar el proveedor.';
                if (!empty($_POST['ajax'])) {
                    http_response_code(422);
                    echo $error;
                    exit;
                }
            }
        }
        //mostrar formulario vacío con error si lo hay
        $data = ['error' => $error, 'activePage' => 'proveedores'];
        require_once 'backend/proveedor/views/proveedor_form.php';
    }

    //editar un proveedor existente
    public function edit() {
        $id = $_GET['id'] ?? 0;
        $error = '';
        //si es POST, actualizar proveedor
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if ($this->model->update($id, $_POST)) {
                $nombreProveedor = trim($_POST['nombre_proveedor'] ?? '');
                $this->auditoria->registrar(
                    'EDITAR',
                    'Proveedores',
                    "Modificación de proveedor ID $id: $nombreProveedor",
                    $id
                );
                //éxito: redirigir a lista con mensaje
                header('Location: index.php?page=proveedores&msg=updated');
                exit;
            } else {
                $error = 'Error al actualizar.';
                if (!empty($_POST['ajax'])) {
                    http_response_code(422);
                    echo $error;
                    exit;
                }
            }
        }
        //mostrar formulario con datos del proveedor
        $data = [
            'proveedor' => $this->model->getById($id),
            'error' => $error,
            'activePage' => 'proveedores'
        ];
        require_once 'backend/proveedor/views/proveedor_form.php';
    }

    //eliminar un proveedor
    public function delete() {
        $id = $_GET['id'] ?? 0;
        $proveedor = $this->model->getById($id);
        $this->model->delete($id);

        if ($proveedor) {
            $nombreProveedor = $proveedor['nombre_proveedor'] ?? "ID $id";
            $this->auditoria->registrar(
                'ELIMINAR',
                'Proveedores',
                "Baja de proveedor: $nombreProveedor",
                $id
            );
        }

        //redirigir a lista con mensaje de éxito
        header('Location: index.php?page=proveedores&msg=deleted');
        exit;
    }
}
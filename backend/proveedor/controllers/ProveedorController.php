<?php
// Incluimos el modelo de auditoría
require_once __DIR__ . '/../../auditoria/AuditoriaModel.php';

// Controlador del módulo de proveedores - maneja CRUD de proveedores
class ProveedorController {
    private $model;     // modelo de proveedores
    private $auditoria; // modelo de auditoría

    public function __construct() {
        $this->model = new ProveedorModel();
        $this->auditoria = new AuditoriaModel();
    }

    // mostrar lista de todos los proveedores
    public function index() {
        $data = [
            'proveedores' => $this->model->getAll(), // obtener todos los proveedores
            'activePage'  => 'proveedores'
        ];
        require_once 'backend/proveedor/views/proveedor.php';
    }

    // crear un nuevo proveedor
    public function create() {
        $error = '';
        // si es POST, guardar nuevo proveedor
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if ($this->model->create($_POST)) {
                $nuevoId = $this->model->getLastInsertedId();
                $razonSocial = trim($_POST['razon_social'] ?? $_POST['nombre'] ?? '');

                // AUDITORÍA: Registro de alta
                $this->auditoria->registrar(
                    'CREAR',
                    'Proveedores',
                    "Alta de proveedor: $razonSocial",
                    $nuevoId
                );

                // éxito: redirigir a lista con mensaje
                header('Location: index.php?page=proveedores&msg=created');
                exit;
            } else {
                $error = 'Error al guardar el proveedor.';
            }
        }
        // mostrar formulario vacío con error si lo hay
        $data = ['error' => $error, 'activePage' => 'proveedores'];
        require_once 'backend/proveedor/views/proveedor_form.php';
    }

    // editar un proveedor existente
    public function edit() {
        $id = $_GET['id'] ?? 0;
        $error = '';
        // si es POST, actualizar proveedor
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if ($this->model->update($id, $_POST)) {
                $razonSocial = trim($_POST['razon_social'] ?? $_POST['nombre'] ?? '');

                // AUDITORÍA: Registro de actualización
                $this->auditoria->registrar(
                    'EDITAR',
                    'Proveedores',
                    "Modificación de proveedor ID $id: $razonSocial",
                    $id
                );

                // éxito: redirigir a lista con mensaje
                header('Location: index.php?page=proveedores&msg=updated');
                exit;
            } else {
                $error = 'Error al actualizar.';
            }
        }
        // mostrar formulario con datos del proveedor
        $data = [
            'proveedor'  => $this->model->getById($id),
            'error'      => $error,
            'activePage' => 'proveedores'
        ];
        require_once 'backend/proveedor/views/proveedor_form.php';
    }

    // eliminar un proveedor
    public function delete() {
        $id = $_GET['id'] ?? 0;

        // Obtenemos los datos previos para registrar la razón social/nombre en auditoría
        $proveedor = $this->model->getById($id);
        $nombreProveedor = $proveedor['razon_social'] ?? $proveedor['nombre'] ?? "ID $id";

        if ($this->model->delete($id)) {
            // AUDITORÍA: Registro de eliminación
            $this->auditoria->registrar(
                'ELIMINAR',
                'Proveedores',
                "Baja de proveedor: $nombreProveedor",
                $id
            );
        }

        // redirigir a lista con mensaje de éxito
        header('Location: index.php?page=proveedores&msg=deleted');
        exit;
    }
}
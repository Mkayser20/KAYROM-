<?php
require_once __DIR__ . '/../../auditoria/AuditoriaModel.php';

// Controlador del módulo de repuestos - maneja CRUD de repuestos y registra movimientos
class RepuestoController {
    private $model;           // modelo de repuestos
    private $movimientoModel; // modelo para registrar movimientos de inventario
    private $auditoria;       // modelo para el sistema de auditoría

    public function __construct() {
        $this->model = new RepuestoModel();
        $this->movimientoModel = new MovimientoModel();
        $this->auditoria = new AuditoriaModel();
    }

    // mostrar lista de todos los repuestos
    public function index() {
        $proveedorModel = new ProveedorModel();
        $data = [
            'repuestos'  => $this->model->getAll(),      //obtener todos los repuestos
            'categorias' => $this->model->getCategorias(), //categorías reales, para el filtro
            'proveedores'=> $proveedorModel->getAll(),    //para el select de proveedor en el alta rápida
            'activePage' => 'repuestos'
        ];
        require_once 'backend/repuestos/views/repuestos.php';
    }

    // crear un nuevo repuesto
    public function create() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $nuevoId = $this->model->create($_POST);

            // registrar movimiento de entrada de repuesto
            $this->movimientoModel->create([
                'tipo'        => 'Entrada',
                'descripcion' => 'Alta de repuesto: ' . $_POST['nombre'],
                'cantidad'    => $_POST['stock']
            ]);

            // AUDITORÍA: Registro de creación
            $nombreRepuesto = trim($_POST['nombre'] ?? '');
            $this->auditoria->registrar(
                'CREAR',
                'Repuestos',
                "Alta de repuesto: $nombreRepuesto (Stock inicial: {$_POST['stock']})",
                $nuevoId
            );

            // redirigir con mensaje de éxito
            header('Location: index.php?page=repuestos&msg=created');
            exit;
        }
        //mostrar formulario vacío
        $data = [
            'proveedores' => (new ProveedorModel())->getAll(),
            'activePage'  => 'repuestos'
        ];
        require_once 'backend/repuestos/views/repuesto_form.php';
    }

    // editar un repuesto existente
    public function edit() {
        $id = $_GET['id'] ?? 0;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->model->update($id, $_POST);

            // AUDITORÍA: Registro de modificación
            $nombreRepuesto = trim($_POST['nombre'] ?? '');
            $this->auditoria->registrar(
                'EDITAR',
                'Repuestos',
                "Modificación de repuesto ID $id: $nombreRepuesto",
                $id
            );

            // redirigir con mensaje de éxito
            header('Location: index.php?page=repuestos&msg=updated');
            exit;
        }

        // mostrar formulario con datos del repuesto
        $data = [
            'repuesto'    => $this->model->getById($id),
            'proveedores' => (new ProveedorModel())->getAll(),
            'activePage'  => 'repuestos'
        ];
        require_once 'backend/repuestos/views/repuesto_form.php';
    }

    // eliminar un repuesto
    public function delete() {
        $id = $_GET['id'] ?? 0;

        // Obtenemos los datos antes de eliminar para registrar el nombre en la auditoría
        $repuesto = $this->model->getById($id);
        $nombreRepuesto = $repuesto['nombre'] ?? "ID $id";

        if ($this->model->delete($id)) {
            // AUDITORÍA: Registro de eliminación
            $this->auditoria->registrar(
                'ELIMINAR',
                'Repuestos',
                "Baja de repuesto: $nombreRepuesto",
                $id
            );
        }

        // redirigir a lista con mensaje de éxito
        header('Location: index.php?page=repuestos&msg=deleted');
        exit;
    }
}
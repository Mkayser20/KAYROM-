<?php
require_once __DIR__ . '/../../auditoria/AuditoriaModel.php';

// Controlador del módulo de pedidos - maneja CRUD de pedidos y cambios de estado
class PedidoController {
    private $model;     // modelo de pedidos
    private $auditoria; // modelo de auditoría

    public function __construct() {
        $this->model = new PedidoModel();
        $this->auditoria = new AuditoriaModel();
    }

    // mostrar lista de todos los pedidos
    public function index() {
        $data = [
            'pedidos'    => $this->model->getAll(),  //obtener todos los pedidos
            'proveedores'=> (new ProveedorModel())->getAll(), //para el select del alta rápida
            'activePage' => 'pedidos'
        ];
        require_once 'backend/pedidos/views/pedidos_listado.php';
    }

    // crear un nuevo pedido
    public function create() {
        // si es POST, guardar nuevo pedido
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $nuevoId = $this->model->create($_POST);

            $proveedorId = $_POST['proveedor_id'] ?? $_POST['proveedor'] ?? 'N/A';
            $total       = $_POST['total'] ?? $_POST['monto'] ?? '';

            // AUDITORÍA: Registro de creación
            $detalle = "Alta de pedido #$nuevoId (Proveedor ID: $proveedorId" . ($total !== '' ? ", Total: $total" : "") . ")";
            $this->auditoria->registrar(
                'CREAR',
                'Pedidos',
                $detalle,
                $nuevoId
            );

            // redirigir con mensaje de éxito
            header('Location: index.php?page=pedidos&msg=created');
            exit;
        }
        //mostrar formulario vacío
        $data = [
            'proveedores' => (new ProveedorModel())->getAll(),
            'activePage'  => 'pedidos'
        ];
        require_once 'backend/pedidos/views/pedido_form.php';
    }

    // eliminar un pedido
    public function delete() {
        $id = (int)($_GET['id'] ?? 0);

        $pedido = $this->model->getById($id);

        if ($this->model->delete($id)) {
            // AUDITORÍA: Registro de eliminación
            $this->auditoria->registrar(
                'ELIMINAR',
                'Pedidos',
                "Baja de pedido #$id",
                $id
            );
        }

        // redirigir a lista con mensaje de éxito
        header('Location: index.php?page=pedidos&msg=deleted');
        exit;
    }

    // marcar un pedido como entregado
    public function entregar() {
        $id = (int)($_GET['id'] ?? 0);

        if ($this->model->entregar($id)) {
            // AUDITORÍA: Registro de cambio de estado
            $this->auditoria->registrar(
                'EDITAR',
                'Pedidos',
                "Pedido #$id marcado como ENTREGADO",
                $id
            );
        }

        // redirigir a lista con mensaje de éxito
        header('Location: index.php?page=pedidos&msg=updated');
        exit;
    }
}
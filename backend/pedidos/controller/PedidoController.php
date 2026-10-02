<?php
require_once __DIR__ . '/../../auditoria/AuditoriaModel.php';

// Controlador de Pedidos a proveedores: listar, crear, recibir y eliminar
class PedidoController {
    private $model;     // modelo de pedidos
    private $auditoria; // modelo de auditoría

    public function __construct() {
        $this->model = new PedidoModel();
        $this->auditoria = new AuditoriaModel();
    }

    // Lista de pedidos (más el formulario rápido para crear uno nuevo)
    public function index() {
        $data = [
            'pedidos'     => $this->model->getAll(),
            'proveedores' => (new ProveedorModel())->getAll(),  // para elegir proveedor
            'repuestos'   => (new RepuestoModel())->getAll(),   // para elegir los repuestos del pedido
            'activePage'  => 'pedidos'
        ];
        require_once 'backend/pedidos/views/pedidos_listado.php';
    }

    // Crear un pedido nuevo (llega desde la ventanita del listado)
    public function create() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?page=pedidos'); // el formulario está en el listado
            exit;
        }

        // Los repuestos llegan como listas paralelas: repuesto_id[] y cantidad[]
        $ids = $_POST['repuesto_id'] ?? [];
        $cants = $_POST['cantidad'] ?? [];
        $items = [];
        foreach ($ids as $i => $rid) {
            $items[] = ['repuesto_id' => $rid, 'cantidad' => $cants[$i] ?? 0];
        }

        // Si no escribieron responsable, uso el usuario que está logueado
        if (empty($_POST['responsable_pedido'])) {
            $_POST['responsable_pedido'] = $_SESSION['nombre'] ?? $_SESSION['nombre_usuario'] ?? 'Usuario';
        }

        $nuevoId = $this->model->create($_POST, $items);

        if (!$nuevoId) {
            // La ventanita muestra este texto como error (sin recargar la página)
            echo $this->model->error;
            exit;
        }

        $this->auditoria->registrar(
            'CREAR',
            'Pedidos',
            "Alta de pedido #$nuevoId (Proveedor ID: " . ($_POST['proveedor_id'] ?: 'N/A') . ", " . count($items) . " renglón/es)",
            $nuevoId
        );

        header('Location: index.php?page=pedidos&msg=created');
        exit;
    }

    // Eliminar un pedido (solo si todavía no fue recibido)
    public function delete() {
        $id = (int)($_GET['id'] ?? 0);

        if ($this->model->delete($id)) {
            $this->auditoria->registrar('ELIMINAR', 'Pedidos', "Baja de pedido #$id", $id);
            header('Location: index.php?page=pedidos&msg=deleted');
        } else {
            header('Location: index.php?page=pedidos&msg=no_borrable');
        }
        exit;
    }

    // RECIBIR un pedido: suma al stock lo que se pidió (antes esto se llamaba "entregar")
    public function recibir() {
        $id = (int)($_GET['id'] ?? 0);
        $res = $this->model->recibir($id);

        if ($res['ok']) {
            $this->auditoria->registrar('EDITAR', 'Pedidos', "Pedido #$id RECIBIDO (stock actualizado)", $id);
            header('Location: index.php?page=pedidos&msg=' . (!empty($res['sin_renglones']) ? 'recibido_sin_items' : 'recibido'));
        } else {
            header('Location: index.php?page=pedidos&msg=no_recibible');
        }
        exit;
    }
}
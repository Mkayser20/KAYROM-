<?php
class PedidoController {
    private $model;

    public function __construct() {
        $this->model = new PedidoModel();
    }

    public function index() {
        $data = [
            'pedidos'    => $this->model->getAll(),
            'activePage' => 'pedidos'
        ];
        require_once 'views/pedidos_listado.php';
    }

    public function create() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->model->create($_POST);
            header('Location: index.php?page=pedidos&msg=created');
            exit;
        }
        $data = ['activePage' => 'pedidos'];
        require_once 'views/pedido_form.php';
    }

    public function delete() {
        $id = (int)($_GET['id'] ?? 0);
        $this->model->delete($id);
        header('Location: index.php?page=pedidos&msg=deleted');
        exit;
    }

    public function entregar() {
        $id = $_GET['id'] ?? 0;
        $this->model->entregar($id);
        header('Location: index.php?page=pedidos&msg=updated');
        exit;
    }
}

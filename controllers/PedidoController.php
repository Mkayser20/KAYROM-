<?php
//Controlador del módulo de pedidos - maneja CRUD de pedidos y cambios de estado
class PedidoController {
    private $model; //modelo de pedidos

    public function __construct() {
        $this->model = new PedidoModel();
    }

    //mostrar lista de todos los pedidos
    public function index() {
        $data = [
            'pedidos'    => $this->model->getAll(),  //obtener todos los pedidos
            'activePage' => 'pedidos'
        ];
        require_once 'views/pedidos_listado.php';
    }

    //crear un nuevo pedido
    public function create() {
        //si es POST, guardar nuevo pedido
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->model->create($_POST);
            //redirigir con mensaje de éxito
            header('Location: index.php?page=pedidos&msg=created');
            exit;
        }
        //mostrar formulario vacío
        $data = ['activePage' => 'pedidos'];
        require_once 'views/pedido_form.php';
    }

    //eliminar un pedido
    public function delete() {
        $id = (int)($_GET['id'] ?? 0);
        $this->model->delete($id);
        //redirigir a lista con mensaje de éxito
        header('Location: index.php?page=pedidos&msg=deleted');
        exit;
    }

    //marcar un pedido como entregado
    public function entregar() {
        $id = $_GET['id'] ?? 0;
        $this->model->entregar($id);
        //redirigir a lista con mensaje de éxito
        header('Location: index.php?page=pedidos&msg=updated');
        exit;
    }
}

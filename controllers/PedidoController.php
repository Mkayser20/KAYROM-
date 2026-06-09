<?php
<<<<<<< HEAD
//Controlador del módulo de pedidos - maneja CRUD de pedidos y cambios de estado
class PedidoController {
    private $model; //modelo de pedidos
=======
class PedidoController {
    private $model;
>>>>>>> fea3808941fb476d8d3bbd3da4140c9ba8ce84cc

    public function __construct() {
        $this->model = new PedidoModel();
    }

<<<<<<< HEAD
    //mostrar lista de todos los pedidos
    public function index() {
        $data = [
            'pedidos'    => $this->model->getAll(),  //obtener todos los pedidos
=======
    public function index() {
        $data = [
            'pedidos'    => $this->model->getAll(),
>>>>>>> fea3808941fb476d8d3bbd3da4140c9ba8ce84cc
            'activePage' => 'pedidos'
        ];
        require_once 'views/pedidos_listado.php';
    }

<<<<<<< HEAD
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
=======
    public function create() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->model->create($_POST);
            header('Location: index.php?page=pedidos&msg=created');
            exit;
        }
>>>>>>> fea3808941fb476d8d3bbd3da4140c9ba8ce84cc
        $data = ['activePage' => 'pedidos'];
        require_once 'views/pedido_form.php';
    }

<<<<<<< HEAD
    //eliminar un pedido
    public function delete() {
        $id = (int)($_GET['id'] ?? 0);
        $this->model->delete($id);
        //redirigir a lista con mensaje de éxito
=======
    public function delete() {
        $id = (int)($_GET['id'] ?? 0);
        $this->model->delete($id);
>>>>>>> fea3808941fb476d8d3bbd3da4140c9ba8ce84cc
        header('Location: index.php?page=pedidos&msg=deleted');
        exit;
    }

<<<<<<< HEAD
    //marcar un pedido como entregado
    public function entregar() {
        $id = $_GET['id'] ?? 0;
        $this->model->entregar($id);
        //redirigir a lista con mensaje de éxito
=======
    public function entregar() {
        $id = $_GET['id'] ?? 0;
        $this->model->entregar($id);
>>>>>>> fea3808941fb476d8d3bbd3da4140c9ba8ce84cc
        header('Location: index.php?page=pedidos&msg=updated');
        exit;
    }
}

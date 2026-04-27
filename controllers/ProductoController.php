<?php
class ProductoController {
    private $model;

    public function __construct() {
        $this->model = new ProductoModel();
    }

    public function index() {
        $data = [
            'productos'  => $this->model->getAll(),
            'activePage' => 'productos'
        ];
        require_once 'views/productos_listado.php';
    }

    public function create() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->model->create($_POST);
            header('Location: index.php?page=productos&msg=created');
            exit;
        }
        $data = ['activePage' => 'productos'];
        require_once 'views/producto_form.php';
    }

    public function edit() {
        $id = (int)($_GET['id'] ?? 0);
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->model->update($id, $_POST);
            header('Location: index.php?page=productos&msg=updated');
            exit;
        }
        $data = [
            'producto'   => $this->model->getById($id),
            'activePage' => 'productos'
        ];
        require_once 'views/producto_form.php';
    }

    public function delete() {
        $id = (int)($_GET['id'] ?? 0);
        $this->model->delete($id);
        header('Location: index.php?page=productos&msg=deleted');
        exit;
    }
}

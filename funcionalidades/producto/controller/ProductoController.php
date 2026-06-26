<?php
//Controlador del módulo de productos - maneja CRUD de productos y stock
class ProductoController {
    private $model; //modelo de productos

    public function __construct() {
        $this->model = new ProductoModel();
    }

    //mostrar lista de todos los productos
    public function index() {
        $data = [
            'productos'  => $this->model->getAll(),  //obtener todos los productos
            'activePage' => 'productos'
        ];
        require_once 'funcionalidades/producto/views/productos_listado.php';
    }

    //crear un nuevo producto
    public function create() {
        //si es POST, guardar nuevo producto
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->model->create($_POST);
            //redirigir con mensaje de éxito
            header('Location: index.php?page=productos&msg=created');
            exit;
        }
        //mostrar formulario vacío
        $data = ['activePage' => 'productos'];
        require_once 'funcionalidades/producto/views/producto_form.php';
    }

    //editar un producto existente
    public function edit() {
        $id = (int)($_GET['id'] ?? 0);
        //si es POST, actualizar producto
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->model->update($id, $_POST);
            //redirigir con mensaje de éxito
            header('Location: index.php?page=productos&msg=updated');
            exit;
        }
        //mostrar formulario con datos del producto
        $data = [
            'producto'   => $this->model->getById($id),
            'activePage' => 'productos'
        ];
        require_once 'funcionalidades/producto/views/producto_form.php';
    }

    //eliminar un producto
    public function delete() {
        $id = (int)($_GET['id'] ?? 0);
        $this->model->delete($id);
        //redirigir a lista con mensaje de éxito
        header('Location: index.php?page=productos&msg=deleted');
        exit;
    }
}

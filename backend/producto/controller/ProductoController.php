<?php
require_once __DIR__ . '/../../auditoria/AuditoriaModel.php';

//Controlador del módulo de productos - maneja CRUD de productos y stock
class ProductoController {
    private $model; //modelo de productos
    private $auditoria;

    public function __construct() {
        $this->model = new ProductoModel();
        $this->auditoria = new AuditoriaModel();
    }

    //mostrar lista de todos los productos
    public function index() {
        $data = [
            'productos'  => $this->model->getAll(),  //obtener todos los productos
            'activePage' => 'productos'
        ];
        require_once 'backend/producto/views/productos_listado.php';
    }

    //crear un nuevo producto
    public function create() {
        //si es POST, guardar nuevo producto
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $resultado = $this->model->create($_POST);

            if ($resultado) {
                $nombreProducto = trim($_POST['nombre_producto'] ?? '');
                $this->auditoria->registrar(
                    'CREAR',
                    'Productos',
                    "Alta de producto: $nombreProducto"
                );
            }

            //redirigir con mensaje de éxito
            header('Location: index.php?page=productos&msg=created');
            exit;
        }
        //mostrar formulario vacío
        $data = ['activePage' => 'productos'];
        require_once 'backend/producto/views/producto_form.php';
    }

    //editar un producto existente
    public function edit() {
        $id = (int)($_GET['id'] ?? 0);
        //si es POST, actualizar producto
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->model->update($id, $_POST);
            $nombreProducto = trim($_POST['nombre_producto'] ?? '');

            $this->auditoria->registrar(
                'EDITAR',
                'Productos',
                "Modificación de producto ID $id: $nombreProducto",
                $id
            );

            //redirigir con mensaje de éxito
            header('Location: index.php?page=productos&msg=updated');
            exit;
        }
        //mostrar formulario con datos del producto
        $data = [
            'producto'   => $this->model->getById($id),
            'activePage' => 'productos'
        ];
        require_once 'backend/producto/views/producto_form.php';
    }

    //eliminar un producto
    public function delete() {
        $id = (int)($_GET['id'] ?? 0);
        $producto = $this->model->getById($id);

        if ($this->model->delete($id)) {
            $nombreProducto = $producto['nombre_producto'] ?? "ID $id";
            $this->auditoria->registrar(
                'ELIMINAR',
                'Productos',
                "Baja de producto: $nombreProducto",
                $id
            );
        }

        //redirigir a lista con mensaje de éxito
        header('Location: index.php?page=productos&msg=deleted');
        exit;
    }
}

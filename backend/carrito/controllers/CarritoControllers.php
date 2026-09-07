<?php
// Controlador del carrito: agregar, ver, quitar y confirmar
class CarritoController {
    private $model;

    public function __construct() {
        $this->model = new CarritoModel();
    }

    // Ver el carrito del usuario logueado
    public function index() {
        $usuario_id = $_SESSION['usuario_id'] ?? 0;
        $items = $this->model->getByUsuario($usuario_id);
        require_once 'backend/carrito/views/carrito.php';
    }

    // Agregar un repuesto al carrito
    public function agregar() {
        $usuario_id  = $_SESSION['usuario_id'] ?? 0;
        $repuesto_id = $_POST['repuesto_id'] ?? $_GET['id'] ?? 0;
        $cantidad    = (int)($_POST['cantidad'] ?? 1);
        if ($cantidad < 1) $cantidad = 1;

        $this->model->agregar($usuario_id, $repuesto_id, $cantidad);

        // Vuelve al carrito después de agregar
        header('Location: index.php?page=repuestos&msg=agregado');
        exit;
    }

    // Quitar un ítem del carrito
    public function quitar() {
        $usuario_id = $_SESSION['usuario_id'] ?? 0;
        $id = $_GET['id'] ?? 0;

        $this->model->quitar($id, $usuario_id);

        header('Location: index.php?page=carrito');
        exit;
    }

    // Sube o baja la cantidad de un ítem (delta = +1 o -1)
    public function cambiar() {
        $usuario_id = $_SESSION['usuario_id'] ?? 0;
        $id    = $_GET['id'] ?? 0;
        $delta = (int)($_GET['delta'] ?? 0);

        $this->model->cambiarCantidad($id, $usuario_id, $delta);

        header('Location: index.php?page=carrito');
        exit;
    }

    // Genera un pedido de compra (uno por proveedor) con todo lo que hay en el carrito, y lo vacía
    public function solicitar() {
        $usuario_id = $_SESSION['usuario_id'] ?? 0;
        $items = $this->model->getByUsuario($usuario_id);

        if (empty($items)) {
            header('Location: index.php?page=carrito');
            exit;
        }

        $responsable = $_SESSION['nombre'] ?? $_SESSION['nombre_usuario'] ?? 'Usuario';
        $pedidoModel = new PedidoModel();

        // Agrupo los ítems del carrito por proveedor (cada proveedor = un pedido aparte)
        $grupos = [];
        foreach ($items as $item) {
            $clave = $item['proveedor_id'] ?? 'sin_proveedor';
            $grupos[$clave]['proveedor_id'] = $item['proveedor_id'] ?? null;
            $grupos[$clave]['items'][] = $item;
        }

        foreach ($grupos as $grupo) {
            $detalle = [];
            $cantidadTotal = 0;
            foreach ($grupo['items'] as $item) {
                $detalle[] = $item['cantidad'] . 'x ' . $item['nombre'];
                $cantidadTotal += $item['cantidad'];
            }
            $pedidoModel->create([
                'estado_pedido'      => 'Pendiente',
                'responsable_pedido' => $responsable,
                'numero_unico'       => rand(1000, 9999),
                'cantidad'           => $cantidadTotal,
                'detalle_pedido'     => implode(', ', $detalle),
                'proveedor_id'       => $grupo['proveedor_id'],
            ]);
        }

        // Una vez generado el/los pedido(s), se vacía el carrito
        $this->model->vaciar($usuario_id);

        header('Location: index.php?page=pedidos&msg=created');
        exit;
    }
}

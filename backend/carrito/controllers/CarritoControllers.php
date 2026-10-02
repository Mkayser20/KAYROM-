<?php
require_once __DIR__ . '/../../auditoria/AuditoriaModel.php';

// Controlador de "Realizar pedido a proveedores" (antes llamado "Carrito"):
// agregar repuestos, ver la lista, quitar y generar los pedidos
class CarritoController {
    private $model;
    private $auditoria;

    public function __construct() {
        $this->model = new CarritoModel();
        $this->auditoria = new AuditoriaModel();
    }

    // Ver la lista de repuestos a pedir del usuario logueado
    public function index() {
        $usuario_id = $_SESSION['usuario_id'] ?? 0;
        $items = $this->model->getByUsuario($usuario_id);
        require_once 'backend/carrito/views/carrito.php';
    }

    // Agregar un repuesto a la lista
    public function agregar() {
        $usuario_id  = $_SESSION['usuario_id'] ?? 0;
        $repuesto_id = $_POST['repuesto_id'] ?? $_GET['id'] ?? 0;
        $cantidad    = (int)($_POST['cantidad'] ?? 1);
        if ($cantidad < 1) $cantidad = 1;

        $this->model->agregar($usuario_id, $repuesto_id, $cantidad);

        // Vuelve al listado de repuestos para seguir agregando
        header('Location: index.php?page=repuestos&msg=agregado');
        exit;
    }

    // Quitar un ítem de la lista
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

    // Genera los pedidos (uno por proveedor) con lo que hay en la lista, y la vacía.
    // Ahora cada pedido guarda sus RENGLONES (repuesto + cantidad + precio), no solo un texto.
    public function solicitar() {
        $usuario_id = $_SESSION['usuario_id'] ?? 0;
        $items = $this->model->getByUsuario($usuario_id);

        if (empty($items)) {
            header('Location: index.php?page=carrito');
            exit;
        }

        $responsable = $_SESSION['nombre'] ?? $_SESSION['nombre_usuario'] ?? 'Usuario';
        $pedidoModel = new PedidoModel();

        // Agrupo los ítems por proveedor (cada proveedor = un pedido aparte)
        $grupos = [];
        foreach ($items as $item) {
            $clave = $item['proveedor_id'] ?? 'sin_proveedor';
            $grupos[$clave]['proveedor_id'] = $item['proveedor_id'] ?? null;
            $grupos[$clave]['items'][] = $item;
        }

        // Si algo falla, no se crea ningún pedido y la lista NO se vacía
        $db = Database::getInstance()->getConexion();
        Tx::iniciar($db);

        $creados = [];
        foreach ($grupos as $grupo) {
            $renglones = [];
            foreach ($grupo['items'] as $item) {
                $renglones[] = [
                    'repuesto_id' => $item['repuesto_id'],
                    'cantidad'    => $item['cantidad'],
                    'precio'      => $item['precio'],
                ];
            }

            $pedidoId = $pedidoModel->create([
                'responsable_pedido' => $responsable,
                'numero_unico'       => rand(1000, 9999),
                'proveedor_id'       => $grupo['proveedor_id'],
            ], $renglones);

            if (!$pedidoId) {
                Tx::terminar($db, false); // deshace todo
                header('Location: index.php?page=carrito&msg=error');
                exit;
            }
            $creados[] = $pedidoId;
        }

        $this->model->vaciar($usuario_id);
        Tx::terminar($db, true);

        // Auditoría: queda registrado quién generó qué pedidos
        foreach ($creados as $pid) {
            $this->auditoria->registrar('CREAR', 'Pedidos', "Pedido #$pid generado desde Realizar pedido a proveedores", $pid);
        }

        header('Location: index.php?page=pedidos&msg=created');
        exit;
    }
}
<?php
session_start();

$page   = $_GET['page']   ?? 'iniciar_sesion';
$action = $_GET['action'] ?? 'index';

require_once 'config/base_datos.php';
require_once 'models/UsuarioModel.php';
require_once 'models/VehiculoModel.php';
require_once 'models/ProductoModel.php';
require_once 'models/PedidoModel.php';
require_once 'models/EmpleadoModel.php';
require_once 'models/ProveedorModel.php';
require_once 'controllers/SesionController.php';
require_once 'controllers/InicioController.php';
require_once 'controllers/VehiculoController.php';
require_once 'controllers/ProductoController.php';
require_once 'controllers/PedidoController.php';

// ── Páginas públicas (sin sesión) ──────────────────
$paginasPublicas = ['iniciar_sesion', 'registrar_usuario', 'logout', 'recuperar_clave', 'restablecer_clave'];

if (!in_array($page, $paginasPublicas) && empty($_SESSION['usuario_id'])) {
    header('Location: index.php?page=iniciar_sesion');
    exit;
}

// ── Router ─────────────────────────────────────────
switch ($page) {

    // Sesión
    case 'iniciar_sesion':
        $action = 'iniciar_sesion';
        (new SesionController())->iniciarSesion();
        break;

    case 'registrar_usuario':
        $action = 'registrar_usuario';
        (new SesionController())->registrarUsuario();
        break;

    case 'logout':
        (new SesionController())->cerrarSesion();
        break;

    case 'recuperar_clave':
        (new SesionController())->recuperarClave();
        break;

    case 'restablecer_clave':
        (new SesionController())->restablecerClave();
        break;

    // Sistema
    case 'inicio':
        (new InicioController())->index();
        break;

    case 'vehiculos':
        $c = new VehiculoController();
        $c->$action();
        break;

    case 'productos':
        $c = new ProductoController();
        $c->$action();
        break;

    case 'pedidos':
        $c = new PedidoController();
        $c->$action();
        break;
    case 'repuestos':
    require_once 'models/RepuestoModel.php';
    require_once 'models/MovimientoModel.php';
    require_once 'controllers/RepuestoController.php';
        $c = new RepuestoController();
        $c->$action();
    break;

    case 'estadisticas':
        (new InicioController())->estadisticas();
        break;

    case 'empleados':
    require_once 'controllers/EmpleadoController.php';
        $c = new EmpleadoController();
        $c->$action();
    break;

    default:
        // Si hay sesión va al inicio, si no al login
        if (!empty($_SESSION['usuario_id'])) {
            (new InicioController())->index();
        } else {
            (new SesionController())->iniciarSesion();
        }
}

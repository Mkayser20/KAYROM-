<?php
$cookiePath = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/index.php'));
$cookiePath = rtrim($cookiePath, '/') . '/';
ini_set('session.use_strict_mode', '1');
ini_set('session.use_only_cookies', '1');
session_set_cookie_params([
    'lifetime' => 0,
    'path' => $cookiePath,
    'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
    'httponly' => true,
    'samesite' => 'Lax',
]);
session_start();
require_once 'compartidoCREO/models/CsrfToken.php';

//lee que pagina y accion se piden por url, por defecto muestra el inicio
$page   = $_GET['page']   ?? 'iniciar_sesion';
$action = $_GET['action'] ?? 'index';
if (!is_string($action)) {
    $action = '';
}

if (preg_match('/^(?:delete|eliminar)/i', $action)) {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        header('Allow: POST');
        exit('Esta acción requiere una solicitud POST.');
    }
    if (!CsrfToken::validar($_POST['csrf_token'] ?? null)) {
        http_response_code(403);
        exit('La solicitud no es válida o expiró. Recargá la página e intentá de nuevo.');
    }
}

function despacharAccion($controlador, $accion) {
    if (!is_callable([$controlador, $accion])) {
        http_response_code(404);
        header('Content-Type: text/plain; charset=UTF-8');
        exit('La acción solicitada no existe.');
    }

    $controlador->$accion();
}

//carga la conexion a la base de datos, los modelos y los controladores necesarios para cada pagina
require_once 'config/base_datos.php';
require_once 'compartidoCREO/models/StockService.php';
require_once 'compartidoCREO/models/UsuarioModel.php';
require_once 'backend/vehiculos/model/VehiculoModel.php';
require_once 'backend/producto/model/ProductoModel.php';
require_once 'backend/pedidos/model/PedidoModel.php';
require_once 'backend/empleado/models/EmpleadoModel.php';
require_once 'backend/proveedor/models/ProveedorModel.php';
require_once 'backend/repuestos/model/RepuestoModel.php';
require_once 'backend/movimiento/models/MovimientoModel.php';
require_once 'backend/ordenes/models/OrdenTrabajoModel.php';
require_once 'backend/complementos/models/ComplementoModel.php';
require_once 'backend/clientes/models/ClienteModel.php';
require_once 'compartidoCREO/models/PermisoModel.php';
require_once 'backend/auditoria/AuditoriaModel.php';
require_once 'backend/auditoria/controllers/AuditoriaController.php';
require_once 'backend/sesion/controller/SesionController.php';
require_once 'backend/inicio/controllers/InicioController.php';
require_once 'backend/vehiculos/controller/VehiculoController.php';
require_once 'backend/producto/controller/ProductoController.php';
require_once 'backend/ordenes/controllers/OrdenTrabajoController.php';
require_once 'backend/complementos/controllers/ComplementoController.php';
require_once 'backend/clientes/controllers/ClienteController.php';
require_once 'backend/pedidos/controller/PedidoController.php';
require_once 'backend/proveedor/controllers/ProveedorController.php';
require_once 'backend/carrito/models/CarritoModels.php';
require_once 'backend/carrito/controllers/CarritoControllers.php';
require_once 'backend/ayuda/controllers/AyudaController.php';

//paginas que se pueden ver sin estar logueado
$paginasPublicas = ['iniciar_sesion', 'logout', 'recuperar_clave', 'restablecer_clave'];

//si la pagina no es publica y no hay sesion activa, redirige al login
if (!in_array($page, $paginasPublicas) && empty($_SESSION['usuario_id'])) {
    header('Location: index.php?page=iniciar_sesion');
    exit;
}

// Páginas que solo pueden usar los administradores
$paginasAdmin = ['registrar_usuario', 'empleados', 'complementos', 'auditorias'];

//si intentan acceder a una pagina de admin sin ser admin, redirige al inicio
if (in_array($page, $paginasAdmin) && ($_SESSION['rol'] ?? '') !== 'admin') {
    header('Location: index.php?page=inicio');
    exit;
}

// Validación de permisos por módulo (checkboxes del admin)
// Si el usuario no es admin, verifica si tiene permiso para acceder al módulo
if (!empty($_SESSION['usuario_id']) && ($_SESSION['rol'] ?? '') !== 'admin') {

    // Páginas que cualquier usuario logueado puede ver (sin importar permisos)
    $paginasLibres = ['inicio', 'logout', 'iniciar_sesion', 'ayuda'];

    // Módulos asignables por checkbox: si el page coincide, hay que chequear
    $modulosAsignables = ['vehiculos', 'repuestos', 'pedidos', 'proveedores', 'productos', 'movimientos', 'clientes'];

    //si la pagina pedida es un modulo asignable, verifica si el usuario tiene permiso
    if (in_array($page, $modulosAsignables)) {
        require_once 'compartidoCREO/models/PermisoModel.php';
        $permisoModel = new PermisoModel();

        //si no tiene permiso, redirige al inicio
        if (!$permisoModel->tienePermiso($_SESSION['usuario_id'], $page)) {
            header('Location: index.php?page=inicio');
            exit;
        }
    }
}

//depende la pagina pedida, crea el controlador correspondiente y llama a la accion pedida
switch ($page) {


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

    case 'inicio':
        (new InicioController())->index();
        break;

    case 'vehiculos':
        $c = new VehiculoController();
        despacharAccion($c, $action);
        break;

    case 'productos':
        $c = new ProductoController();
        despacharAccion($c, $action);
        break;

    case 'pedidos':
        $c = new PedidoController();
        despacharAccion($c, $action);
        break;
    case 'repuestos':
        require_once 'backend/repuestos/model/RepuestoModel.php';
        require_once 'backend/movimiento/models/MovimientoModel.php';
        require_once 'backend/repuestos/controller/RepuestoController.php';
        $c = new RepuestoController();
        despacharAccion($c, $action);
        break;

    case 'movimientos':
        require_once 'backend/movimiento/controllers/MovimientoController.php';
        despacharAccion(new MovimientoController(), $action);
        break;

    case 'proveedores':
        despacharAccion(new ProveedorController(), $action);
        break;

    case 'empleados':
        require_once 'backend/empleado/controller/EmpleadoController.php';
        $c = new EmpleadoController();
        despacharAccion($c, $action);
        break;

    case 'carrito':
        require_once 'backend/carrito/controllers/CarritoControllers.php';
        $c = new CarritoController();
        despacharAccion($c, $action);
        break;

    case 'ordenes':
        $c = new OrdenTrabajoController();
        despacharAccion($c, $action);
        break;

    case 'complementos':
        $c = new ComplementoController();
        despacharAccion($c, $action);
        break;

    case 'clientes':
        $c = new ClienteController();
        despacharAccion($c, $action);
        break;

    case 'auditorias':
        (new AuditoriaController())->index();
        break;

    case 'ayuda':
        (new AyudaController())->index();
        break;

    //si la pagina no coincide con ninguna de las anteriores, muestra el inicio o el login segun haya sesion o no
    default:
    
        if (!empty($_SESSION['usuario_id'])) {
            (new InicioController())->index();
        } else {
            (new SesionController())->iniciarSesion();
        }
}

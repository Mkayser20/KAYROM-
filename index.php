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
if (!is_string($page)) {
    $page = '';
}
if (!is_string($action)) {
    $action = '';
}

// Acciones que CAMBIAN datos y por eso solo se aceptan por POST.
// Antes algunas (recibir un pedido, cerrar una orden, el carrito...) se hacían con un link GET:
// cualquier página de afuera podía disparar esas acciones con una imagen o un link.
$accionesSoloPost = ['recibir', 'cerrar', 'anular', 'descontarPendientes', 'agregar', 'quitar', 'cambiar',
                     'solicitar', 'favorito', 'cambiarEstado', 'cambiarActivo'];
$esSoloPost = preg_match('/^(?:delete|eliminar|crear|editar)/i', $action)
           || in_array($action, $accionesSoloPost, true)
           || $page === 'logout';

if ($esSoloPost && $_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    header('Allow: POST');
    exit('Esta acción requiere una solicitud POST.');
}

// CSRF: TODO lo que llega por POST tiene que traer el token de la sesión
// (en un campo oculto csrf_token, o en el encabezado X-CSRF-Token cuando es por fetch/AJAX).
// El token lo agrega solo public/js/alertas.js a cada formulario y a cada fetch.
// Las pantallas públicas (login y recuperar clave) quedan afuera porque todavía no hay sesión.
$paginasSinCsrf = ['iniciar_sesion', 'recuperar_clave', 'restablecer_clave'];
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !in_array($page, $paginasSinCsrf, true)) {
    $tokenRecibido = $_POST['csrf_token'] ?? ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? null);
    if (!CsrfToken::validar($tokenRecibido)) {
        http_response_code(403);
        exit('La solicitud no es válida o expiró. Recargá la página e intentá de nuevo.');
    }
}

function despacharAccion($controlador, $accion) {
    // no se permite llamar a métodos "mágicos" de PHP (__construct, etc.) desde la URL
    if ($accion === '' || str_starts_with($accion, '_') || !is_callable([$controlador, $accion])) {
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

// Si hay sesión, vuelvo a leer el usuario de la base en cada pedido.
// Antes el rol y el estado quedaban guardados en la sesión: si el admin desactivaba a alguien,
// esa persona seguía entrando hasta que cerrara sesión.
if (!empty($_SESSION['usuario_id'])) {
    $usuarioActual = (new UsuarioModel())->getEstadoSesion((int)$_SESSION['usuario_id']);
    if (!$usuarioActual || (int)$usuarioActual['activo'] !== 1) {
        // lo desactivaron o lo borraron: se cierra la sesión
        $_SESSION = [];
        session_destroy();
        header('Location: index.php?page=iniciar_sesion');
        exit;
    }
    $_SESSION['rol'] = $usuarioActual['rol']; // si le cambiaron el rol, se aplica enseguida
}

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

    // Qué permiso hace falta para cada página.
    // Antes "ordenes" y "carrito" no estaban acá: cualquier empleado podía crear órdenes de trabajo
    // (que descuentan stock) o generar pedidos aunque no tuviera esos módulos.
    $permisoRequerido = [
        'vehiculos'   => 'vehiculos',
        'ordenes'     => 'vehiculos',   // las órdenes se manejan desde la ficha del vehículo
        'clientes'    => 'clientes',
        'repuestos'   => 'repuestos',
        'pedidos'     => 'pedidos',
        'carrito'     => 'pedidos',     // "Realizar pedido" genera pedidos
        'proveedores' => 'proveedores',
        'movimientos' => 'movimientos',
    ];

    //si la pagina pedida necesita un permiso, verifica que el usuario lo tenga
    if (isset($permisoRequerido[$page])) {
        $permisoModel = new PermisoModel();

        //si no tiene permiso, redirige al inicio (o avisa, si era un pedido por AJAX)
        if (!$permisoModel->tienePermiso($_SESSION['usuario_id'], $permisoRequerido[$page])) {
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                http_response_code(403);
                exit('No tenés permiso para usar este módulo.');
            }
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

<?php
session_start();

//lee que pagina y accion se piden por url, por defecto muestra el inicio
$page   = $_GET['page']   ?? 'iniciar_sesion';
$action = $_GET['action'] ?? 'index';

//carga la conexion a la base de datos, los modelos y los controladores necesarios para cada pagina
require_once 'config/base_datos.php';
require_once 'models/UsuarioModel.php';
require_once 'models/VehiculoModel.php';
require_once 'models/ProductoModel.php';
require_once 'models/PedidoModel.php';
require_once 'models/EmpleadoModel.php';
require_once 'models/ProveedorModel.php';
require_once 'models/PermisoModel.php';
require_once 'controllers/SesionController.php';
require_once 'controllers/InicioController.php';
require_once 'controllers/VehiculoController.php';
require_once 'controllers/ProductoController.php';
require_once 'controllers/PedidoController.php';
require_once 'controllers/ProveedorController.php';

//paginas que se pueden ver sin estar logueado
$paginasPublicas = ['iniciar_sesion', 'logout', 'recuperar_clave', 'restablecer_clave'];

//si la pagina no es publica y no hay sesion activa, redirige al login
if (!in_array($page, $paginasPublicas) && empty($_SESSION['usuario_id'])) {
    header('Location: index.php?page=iniciar_sesion');
    exit;
}

// Páginas que solo pueden usar los administradores
$paginasAdmin = ['registrar_usuario'];

//si intentan acceder a una pagina de admin sin ser admin, redirige al inicio
if (in_array($page, $paginasAdmin) && ($_SESSION['rol'] ?? '') !== 'admin') {
    header('Location: index.php?page=inicio');
    exit;
}

// Validación de permisos por módulo (checkboxes del admin)
// Si el usuario no es admin, verifica si tiene permiso para acceder al módulo
if (!empty($_SESSION['usuario_id']) && ($_SESSION['rol'] ?? '') !== 'admin') {

    // Páginas que cualquier usuario logueado puede ver (sin importar permisos)
    $paginasLibres = ['inicio', 'logout', 'iniciar_sesion'];

    // Módulos asignables por checkbox: si el page coincide, hay que chequear
    $modulosAsignables = ['vehiculos', 'repuestos', 'pedidos', 'proveedores', 'empleados', 'productos', 'movimientos'];

    //si la pagina pedida es un modulo asignable, verifica si el usuario tiene permiso
    if (in_array($page, $modulosAsignables)) {
        require_once 'models/PermisoModel.php';
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

    case 'proveedores':
    $action = $_GET['action'] ?? 'index';
    (new ProveedorController())->$action();
    break;

    case 'empleados':
    require_once 'controllers/EmpleadoController.php';
        $c = new EmpleadoController();
        $c->$action();
    break;

    //si la pagina no coincide con ninguna de las anteriores, muestra el inicio o el login segun haya sesion o no
    default:
    
        if (!empty($_SESSION['usuario_id'])) {
            (new InicioController())->index();
        } else {
            (new SesionController())->iniciarSesion();
        }
}

<?php
// Devuelve un ícono SVG de una sola tinta (toma el color del texto, como los del menú).
// Se usa así: icono('vehiculo')  o  icono('caja', 16) si querés elegir el tamaño (en píxeles)
if (!function_exists('icono')) {
    function icono($nombre, $tam = 24) {
        $dibujos = [
            'vehiculo'    => '<rect x="1" y="3" width="15" height="13" rx="2"/><path d="M16 8h4l3 5v3h-7V8z"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/>',
            'caja'        => '<path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/><polyline points="3.27 6.96 12 12.01 20.73 6.96"/><line x1="12" y1="22.08" x2="12" y2="12"/>',
            'llave'       => '<path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"/>',
            'lista'       => '<line x1="8" y1="6" x2="21" y2="6"/><line x1="8" y1="12" x2="21" y2="12"/><line x1="8" y1="18" x2="21" y2="18"/><line x1="3" y1="6" x2="3.01" y2="6"/><line x1="3" y1="12" x2="3.01" y2="12"/><line x1="3" y1="18" x2="3.01" y2="18"/>',
            'grafico'     => '<line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/>',
            'alerta'      => '<path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/>',
            'check'       => '<polyline points="20 6 9 17 4 12"/>',
            'editar'      => '<path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>',
            'eliminar'    => '<polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/>',
            'ajustes'     => '<circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 1 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 1 1-2.83-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 1 1 2.83-2.83l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 1 1 2.83 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/>',
            'etiqueta'    => '<path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"/><line x1="7" y1="7" x2="7.01" y2="7"/>',
            'movimientos' => '<polyline points="17 1 21 5 17 9"/><path d="M3 11V9a4 4 0 0 1 4-4h14"/><polyline points="7 23 3 19 7 15"/><path d="M21 13v2a4 4 0 0 1-4 4H3"/>',
            'recibir'     => '<path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/>',
            'enviar'      => '<path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/>',
        ];
        $trazos = $dibujos[$nombre] ?? '';
        return '<svg class="icono" width="' . $tam . '" height="' . $tam . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">' . $trazos . '</svg>';
    }
}

$activePage    = $data['activePage'] ?? '';
$msg           = $_GET['msg'] ?? '';
$msgTexts      = [
    'created' => 'Registro creado exitosamente.',
    'updated' => 'Registro actualizado.',
    'deleted'  => 'Registro eliminado.',
    'agregado' => 'Repuesto agregado a tu pedido a proveedores.',
    // NUEVO: mensajes del ciclo de pedidos y stock
    'recibido'           => 'Pedido recibido: el stock fue actualizado.',
    'recibido_sin_items' => 'Pedido marcado como recibido, pero es un pedido antiguo sin renglones: el stock no se modificó.',
    'no_recibible'       => 'Ese pedido no se puede recibir (ya fue recibido, está cancelado o falló el stock).',
    'repuesto_en_uso'    => 'No se puede eliminar: ese repuesto ya se usó en órdenes de trabajo o está en pedidos.',
    'no_borrable'        => 'No se puede eliminar un pedido ya recibido.',
    'error'              => 'No se pudo completar la operación. No se guardó ningún cambio.',
];
$nombreUsuario = $_SESSION['nombre'] ?? $_SESSION['nombre_usuario'] ?? 'Usuario';
$rolUsuario    = $_SESSION['rol']    ?? '';

$permisoModel = new PermisoModel();
$permisos = $permisoModel->getByUsuario($_SESSION['usuario_id'] ?? 0);
$esAdmin = ($rolUsuario === 'admin');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>KAYROM</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@600;700;800&family=JetBrains+Mono:wght@500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="public/css/style.css">
    <!-- Alertas modernas (SweetAlert2 + tema del sistema). La lógica está en public/js/alertas.js -->
    <link rel="stylesheet" href="public/vendor/sweetalert2/sweetalert2.min.css">
    <link rel="stylesheet" href="public/css/alertas.css">
    <script src="public/vendor/sweetalert2/sweetalert2.min.js"></script>
    <script src="public/js/alertas.js"></script>
</head>
<body>

<?php if ($msg && isset($msgTexts[$msg])): ?>
<div class="toast"><?= $msgTexts[$msg] ?></div>
<?php endif; ?>

<!-- SIDEBAR -->
<aside class="sidebar" id="sidebar">

    <!-- Logo -->
    <!-- Marca: emblema (auto + engranaje) y nombre. Los dibujos están en public/img -->
    <div class="sidebar-logo">
        <img class="logo-emblema" src="public/img/logo-emblema.png" alt="" width="84" height="40">
        <div class="sidebar-brand">
            <img class="logo-nombre" src="public/img/logo-nombre.png" alt="KAYROM" width="116" height="20">
            <span class="tag">Sistema de inventario</span>
        </div>
    </div>

    <nav class="sidebar-nav">

    <!-- 1. Inicio - lo ven todos -->
    <a href="index.php?page=inicio" class="nav-icon-btn <?= $activePage==='inicio'?'active':'' ?>">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
        <span class="tooltip">Inicio</span>
    </a>

    <!-- 2. Vehiculos - lo ven todos -->
     <?php if ($esAdmin || in_array('vehiculos', $permisos)): ?>
    <a href="index.php?page=vehiculos" class="nav-icon-btn <?= $activePage==='vehiculos'?'active':'' ?>">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="1" y="3" width="15" height="13" rx="2"/><path d="M16 8h4l3 5v3h-7V8z"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/></svg>
        <span class="tooltip">Vehículos</span>
    </a>
    <?php endif; ?>

    <?php if ($esAdmin || in_array('clientes', $permisos)): ?>
    <a href="index.php?page=clientes" class="nav-icon-btn <?= $activePage==='clientes'?'active':'' ?>">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
        <span class="tooltip">Clientes</span>
    </a>
    <?php endif; ?>

    <!-- 3. Repuestos - lo ven todos -->
     <?php if ($esAdmin || in_array('repuestos', $permisos)): ?>
    <a href="index.php?page=repuestos" class="nav-icon-btn <?= $activePage==='repuestos'?'active':'' ?>">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"/></svg>
        <span class="tooltip">Repuestos</span>
    </a>
    <?php endif; ?>

    <!-- 4. Pedidos - solo admin y empleado -->
    <?php if ($esAdmin || in_array('pedidos', $permisos)): ?>
    <a href="index.php?page=pedidos" class="nav-icon-btn <?= $activePage==='pedidos'?'active':'' ?>">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/><polyline points="3.27 6.96 12 12.01 20.73 6.96"/><line x1="12" y1="22.08" x2="12" y2="12"/></svg>
        <span class="tooltip">Pedidos</span>
    </a>
    <?php endif; ?>

    <!-- NUEVO: Movimientos de stock -->
    <?php if ($esAdmin || in_array('movimientos', $permisos)): ?>
    <a href="index.php?page=movimientos" class="nav-icon-btn <?= $activePage==='movimientos'?'active':'' ?>">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="17 1 21 5 17 9"/><path d="M3 11V9a4 4 0 0 1 4-4h14"/><polyline points="7 23 3 19 7 15"/><path d="M21 13v2a4 4 0 0 1-4 4H3"/></svg>
        <span class="tooltip">Movimientos</span>
    </a>
    <?php endif; ?>

    <!-- 5. Proveedores - solo admin -->
    <?php if ($esAdmin || in_array('proveedores', $permisos)): ?>
   <a href="index.php?page=proveedores" class="nav-icon-btn <?= $activePage==='proveedores'?'active':'' ?>">
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-handshake-icon lucide-handshake"><path d="m11 17 2 2a1 1 0 1 0 3-3"/><path d="m14 14 2.5 2.5a1 1 0 1 0 3-3l-3.88-3.88a3 3 0 0 0-4.24 0l-.88.88a1 1 0 1 1-3-3l2.81-2.81a5.79 5.79 0 0 1 7.06-.87l.47.28a2 2 0 0 0 1.42.25L21 4"/><path d="m21 3 1 11h-2"/><path d="M3 3 2 14l6.5 6.5a1 1 0 1 0 3-3"/><path d="M3 4h8"/></svg>
        <span class="tooltip">Proveedores</span>
    </a>
    <?php endif; ?>

    <!-- opciones extra del admin -->
     <?php if ($esAdmin): ?>
    <a href="index.php?page=empleados" class="nav-icon-btn <?= $activePage==='empleados'?'active':'' ?>">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
        <span class="tooltip">Empleados</span>
    </a>
    <a href="index.php?page=complementos" class="nav-icon-btn <?= $activePage==='complementos'?'active':'' ?>">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 1 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 1 1-2.83-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 1 1 2.83-2.83l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 1 1 2.83 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>
        <span class="tooltip">Complementos</span>
    </a>
     <?php endif; ?>

    <a href="index.php?page=carrito" class="nav-icon-btn <?= $activePage==='carrito'?'active':'' ?>">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg>
        <span class="tooltip">Realizar pedido</span>
    </a>
    <?php if ($esAdmin): ?>
    <a href="index.php?page=registrar_usuario&action=registrar_usuario" class="nav-icon-btn <?= $activePage==='registrar_usuario'?'active':'' ?>">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="8.5" cy="7" r="4"/><line x1="20" y1="8" x2="20" y2="14"/><line x1="23" y1="11" x2="17" y2="11"/></svg>
        <span class="tooltip">Registrar Usuario</span>
    </a>
    <?php endif; ?>

    <?php if ($esAdmin): ?>
    <a href="index.php?page=auditorias" class="nav-icon-btn <?= $activePage==='auditorias'?'active':'' ?>">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M7 6h10"/><path d="M7 12h10"/><path d="M7 18h10"/><circle cx="4" cy="6" r="1"/><circle cx="4" cy="12" r="1"/><circle cx="4" cy="18" r="1"/></svg>
        <span class="tooltip">Auditoría</span>
    </a>
    <?php endif; ?>

    <!-- 6. Ayuda - lo ven todos -->
    <a href="index.php?page=ayuda<?= ($activePage && $activePage !== 'ayuda') ? '&modulo=' . urlencode($activePage) : '' ?>" class="nav-icon-btn <?= $activePage==='ayuda'?'active':'' ?>">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
        <span class="tooltip">Ayuda</span>
    </a>

</nav>

</aside>

<!-- TOPBAR -->
<div class="sidebar-backdrop" id="sidebar-backdrop" onclick="cerrarSidebar()"></div>

<header class="topbar">
    <button type="button" class="menu-toggle" onclick="abrirSidebar()" aria-label="Abrir menú">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
    </button>
    <div class="topbar-title">Sistema de Inventario de Vehículos y Repuestos</div>
    <div class="topbar-right">
        <!-- Menú de usuario: cápsula con avatar, nombre y rol. Al tocarla se abre el menú con "Cerrar sesión" -->
        <div class="menu-usuario" id="menu-usuario">
            <button type="button" class="chip-usuario" id="chip-usuario" onclick="toggleMenuUsuario(event)"
                    aria-haspopup="true" aria-expanded="false">
                <span class="avatar">
                    <?= strtoupper(substr($nombreUsuario, 0, 1)) ?>
                    <span class="punto-estado" title="Sesión activa"></span>
                </span>
                <span class="info-usuario">
                    <span class="nombre-usuario"><?= htmlspecialchars($nombreUsuario) ?></span>
                    <?php if ($rolUsuario): ?>
                    <span class="rol-usuario"><?= htmlspecialchars($rolUsuario) ?></span>
                    <?php endif; ?>
                </span>
                <svg class="flecha" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"/></svg>
            </button>

            <div class="desplegable-usuario" id="desplegable-usuario">
                <div class="cabecera-usuario">
                    <span class="avatar avatar-lg">
                        <?= strtoupper(substr($nombreUsuario, 0, 1)) ?>
                        <span class="punto-estado"></span>
                    </span>
                    <div>
                        <div class="nom"><?= htmlspecialchars($nombreUsuario) ?></div>
                        <div class="sub">Sesión activa<?= $rolUsuario ? ' · ' . htmlspecialchars($rolUsuario === 'admin' ? 'Administrador' : ucfirst($rolUsuario)) : '' ?></div>
                    </div>
                </div>
                <a href="index.php?page=logout" onclick="cerrarMenuUsuario(); return confirmarAccion(this.href, '¿Cerrar sesión?')">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
                    Cerrar sesión
                </a>
            </div>
        </div>
    </div>

</header>
    
    <script>
// confirmarAccion() y el resto de las alertas se definen en public/js/alertas.js (SweetAlert2)

// Modales de alta rápida (Nuevo Vehículo / Repuesto / Pedido) desde el listado
function abrirModal(id) {
    document.getElementById(id).classList.add('open');
}
function cerrarModal(id) {
    document.getElementById(id).classList.remove('open');
}

// Sidebar en mobile: se abre como un panel flotante encima del contenido, con fondo oscuro atrás
function abrirSidebar() {
    document.getElementById('sidebar').classList.add('open');
    document.getElementById('sidebar-backdrop').classList.add('open');
}
function cerrarSidebar() {
    document.getElementById('sidebar').classList.remove('open');
    document.getElementById('sidebar-backdrop').classList.remove('open');
}
// Si tocás un link del menú estando en mobile, se cierra solo (no hace falta cerrarlo a mano)
document.querySelectorAll('.sidebar-nav a').forEach(link => link.addEventListener('click', cerrarSidebar));

// Menú de usuario (arriba a la derecha): se abre/cierra al tocar la cápsula; se cierra con clic afuera o con Escape
function toggleMenuUsuario(event) {
    event.stopPropagation(); // para que el clic no llegue al "cerrar al hacer clic afuera"
    const menu  = document.getElementById('desplegable-usuario');
    const chip  = document.getElementById('chip-usuario');
    const abrir = !menu.classList.contains('abierto');
    menu.classList.toggle('abierto', abrir);
    chip.classList.toggle('abierto', abrir);
    chip.setAttribute('aria-expanded', abrir ? 'true' : 'false');
}
function cerrarMenuUsuario() {
    const menu = document.getElementById('desplegable-usuario');
    const chip = document.getElementById('chip-usuario');
    if (!menu) { return; }
    menu.classList.remove('abierto');
    chip.classList.remove('abierto');
    chip.setAttribute('aria-expanded', 'false');
}
document.addEventListener('click', function (e) {
    if (!e.target.closest('#menu-usuario')) { cerrarMenuUsuario(); }
});
document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') { cerrarMenuUsuario(); }
});

// Combo con buscador (autocompletar): arma un input + dropdown a partir de una lista cerrada
// de opciones {id, label}. Sigue siendo "elegir de la lista", no texto libre: el valor real
// que se manda al servidor queda guardado en un input oculto, no en lo que se escribe.
function crearCombo(containerId, opciones) {
    const cont     = document.getElementById(containerId);
    const input    = cont.querySelector('.combo-input');
    const oculto   = cont.querySelector('.combo-value');
    const dropdown = cont.querySelector('.combo-dropdown');

    function render(filtro) {
        const texto = (filtro || '').toLowerCase();
        const filtradas = opciones.filter(o => o.label.toLowerCase().includes(texto));
        if (filtradas.length === 0) {
            dropdown.innerHTML = '<div class="combo-empty">Sin resultados</div>';
            return;
        }
        dropdown.innerHTML = filtradas.map(o =>
            `<div class="combo-option" data-id="${o.id}">${o.label}</div>`
        ).join('');
        dropdown.querySelectorAll('.combo-option').forEach((op, i) => {
            op.onclick = () => {
                input.value  = filtradas[i].label;
                oculto.value = filtradas[i].id;
                dropdown.classList.remove('open');
            };
        });
    }

    input.addEventListener('focus', () => { render(input.value); dropdown.classList.add('open'); });
    input.addEventListener('input', () => { oculto.value = ''; render(input.value); dropdown.classList.add('open'); });
    document.addEventListener('click', (e) => { if (!cont.contains(e.target)) dropdown.classList.remove('open'); });

    // Estas dos funciones quedan "colgadas" del contenedor para poder usarlas desde afuera
    // (por ejemplo, al abrir el modal en modo edición, para precargar el valor actual)
    cont._setValor = function (id) {
        const encontrada = opciones.find(o => o.id == id);
        input.value  = encontrada ? encontrada.label : '';
        oculto.value = encontrada ? encontrada.id : '';
    };
    cont._limpiar = function () {
        input.value = '';
        oculto.value = '';
    };
}

// Manda el formulario de un modal por AJAX en vez de como un POST normal del navegador.
// Así, si el servidor devuelve un error (ej: "la patente ya existe"), se muestra ahí
// mismo en el modal (en el toast indicado) en vez de sacarte a otra pantalla entera.
// Si todo sale bien, recién ahí se recarga la página (para traer los datos actualizados).
async function enviarModalForm(form, event, toastId) {
    event.preventDefault();
    const boton = form.querySelector('button[type="submit"]');
    const textoOriginal = boton.textContent;
    boton.disabled = true;
    boton.textContent = 'Guardando...';

    try {
        const respuesta = await fetch(form.action, { method: 'POST', body: new FormData(form) });

        // Si el servidor redirigió (header('Location: ...')), fue éxito: recargo para ver los datos nuevos
        if (respuesta.redirected) {
            window.location.reload();
            return false;
        }

        // Si no redirigió, el cuerpo de la respuesta es el mensaje de error en texto plano
        // el servidor devuelve el motivo del error como texto corto; si viniera algo raro
        // (por ejemplo una página de error de PHP entera) muestro un mensaje genérico
        const mensaje = (await respuesta.text()).trim();
        const esTextoCorto = mensaje !== '' && mensaje.length < 200 && mensaje.indexOf('<') === -1;
        notificar('error', esTextoCorto ? mensaje : 'Ocurrió un error, probá de nuevo.');
    } catch (err) {
        notificar('error', 'Error de conexión, probá de nuevo.');
    } finally {
        boton.disabled = false;
        boton.textContent = textoOriginal;
    }
    return false;
}
// Modo tarjetas en celular: copia el título de cada columna (th) a cada celda (td) de las
// tablas con la clase "tabla-cards", así el CSS lo muestra al lado del dato
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('table.tabla-cards').forEach(function (tabla) {
        const titulos = Array.from(tabla.querySelectorAll('thead th')).map(th => th.textContent.trim());
        tabla.querySelectorAll('tbody tr').forEach(function (fila) {
            Array.from(fila.children).forEach(function (celda, i) {
                if (titulos[i]) celda.setAttribute('data-label', titulos[i]);
            });
        });
    });
});

// Muestra un aviso chiquito abajo a la derecha (usa el mismo estilo del toast del sistema,
// que se desvanece solo con la animación del CSS)
function mostrarToast(texto, tipo) {
    // con la librería de alertas cargada (public/js/alertas.js) se muestra la píldora; el tipo por defecto es éxito
    if (window.notificar) { notificar(tipo || 'success', texto); return; }
    // respaldo: si la librería no cargó, queda el aviso de antes
    const aviso = document.createElement('div');
    aviso.className = 'toast';
    aviso.textContent = texto;
    document.body.appendChild(aviso);
    setTimeout(() => aviso.remove(), 3500);
}

// Manda un cambio rápido (un estado, un check) por AJAX, sin recargar la página.
// Devuelve true si salió bien. Si falla, muestra el motivo en un aviso y devuelve false.
async function enviarCambio(url, datos) {
    try {
        const respuesta = await fetch(url, { method: 'POST', body: new URLSearchParams(datos) });
        if (respuesta.ok) return true;
        // el servidor devuelve el motivo del error en texto plano
        const mensaje = (await respuesta.text()).trim();
        mostrarToast(mensaje || 'No se pudo guardar el cambio.', 'error');
    } catch (err) {
        mostrarToast('Error de conexión, probá de nuevo.', 'error');
    }
    return false;
}

// Cerrar el modal si se hace clic fuera de la tarjeta (en el fondo oscuro)
document.addEventListener('click', function (e) {
    if (e.target.classList.contains('quick-modal-overlay')) {
        e.target.classList.remove('open');
    }
});
    </script>
<main class="main-content">
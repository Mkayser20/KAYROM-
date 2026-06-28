<?php
$activePage    = $data['activePage'] ?? '';
$msg           = $_GET['msg'] ?? '';
$msgTexts      = [
    'created' => '✅ Registro creado exitosamente.',
    'updated' => '✅ Registro actualizado.',
    'deleted'  => '🗑️ Registro eliminado.',
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
    <title>Kayrom — Sistema de Inventario</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Exo+2:wght@300;400;500;600;700&family=Rajdhani:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="public/css/style.css">
</head>
<body>

<?php if ($msg && isset($msgTexts[$msg])): ?>
<div class="toast"><?= $msgTexts[$msg] ?></div>
<?php endif; ?>

<!-- SIDEBAR -->
<aside class="sidebar">

    <!-- Logo -->
    <div class="sidebar-logo">
        <div class="logo-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="20" height="20">
                <rect x="1" y="3" width="15" height="13" rx="2"/><path d="M16 8h4l3 5v3h-7V8z"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/>
            </svg>
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
     <?php endif; ?>

     <?php if ($esAdmin): ?>
    <a href="index.php?page=estadisticas" class="nav-icon-btn <?= $activePage==='estadisticas'?'active':'' ?>">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>
        <span class="tooltip">Estadísticas</span>
    </a>
    <?php endif; ?>

    <?php if ($esAdmin): ?>
    <a href="index.php?page=registrar_usuario&action=registrar_usuario" class="nav-icon-btn <?= $activePage==='registrar_usuario'?'active':'' ?>">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="8.5" cy="7" r="4"/><line x1="20" y1="8" x2="20" y2="14"/><line x1="23" y1="11" x2="17" y2="11"/></svg>
        <span class="tooltip">Registrar Usuario</span>
    </a>
    <?php endif; ?>

    <!-- 6. Ayuda - lo ven todos -->
    <a href="#" class="nav-icon-btn">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
        <span class="tooltip">Ayuda</span>
    </a>

</nav>

    <div class="sidebar-footer">v1.0</div>
</aside>

<!-- TOPBAR -->
<header class="topbar">
    <div class="topbar-title">Sistema de Inventario de Vehículos y Repuestos</div>
    <div class="topbar-right">
        <div class="topbar-user">
            <div style="text-align:right; line-height:1.3;">
                <div style="font-size:13px; color:var(--text-primary); font-weight:600;">
                    <?= htmlspecialchars($nombreUsuario) ?>
                </div>
                <?php if ($rolUsuario): ?>
                <div style="font-size:11px; color:var(--text-muted);">
                    <?= htmlspecialchars($rolUsuario) ?>
                </div>
                <?php endif; ?>
            </div>
            <div class="avatar">
                <?= strtoupper(substr($nombreUsuario, 0, 1)) ?>
                <span class="badge">!</span>
            </div>
        </div>
        <a href="index.php?page=logout" class="btn-logout"
           onclick="return confirmarAccion(this.href, '¿Cerrar sesión?')">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
            Cerrar Sesión
        </a>
    </div>

</header>
    
    <div id="modal-overlay" class="modal-overlay">
    <div class="modal-box">
        <p id="modal-mensaje" class="modal-mensaje"></p>
        <div class="modal-botones">
            <button type="button" id="btn-modal-cancelar" class="btn-cancelar">Cancelar</button>
            <button type="button" id="btn-modal-aceptar" class="btn-aceptar">Aceptar</button>
        </div>
    </div>
    </div>
                    
    <script>
        document.querySelectorAll('.nav-icon-btn').forEach(btn => {
            const tooltip = btn.querySelector('.tooltip');
            if (!tooltip) return;
            btn.addEventListener('mouseenter', () => {
                const rect = btn.getBoundingClientRect();
                tooltip.style.top = (rect.top + rect.height / 2) + 'px';
        });
    });
function confirmarAccion(urlDestino, mensaje) {
    const overlay = document.getElementById('modal-overlay');
    const mensajeElemento = document.getElementById('modal-mensaje');
    const btnAceptar = document.getElementById('btn-modal-aceptar');
    const btnCancelar = document.getElementById('btn-modal-cancelar');
    
    mensajeElemento.textContent = mensaje;
    
    overlay.style.display = 'flex';
    
    btnAceptar.onclick = function() {
        window.location.href = urlDestino;
    };
    
    btnCancelar.onclick = function() {
        overlay.style.display = 'none';
    };
    
    return false;
    }
    </script>
<main class="main-content">

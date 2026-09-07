<?php
$activePage    = $data['activePage'] ?? '';
$msg           = $_GET['msg'] ?? '';
$msgTexts      = [
    'created' => '✅ Registro creado exitosamente.',
    'updated' => '✅ Registro actualizado.',
    'deleted'  => '🗑️ Registro eliminado.',
    'agregado' => '🛒 Repuesto añadido al carrito.',
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
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@600;700;800&family=JetBrains+Mono:wght@500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="public/css/style.css">
</head>
<body>

<?php if ($msg && isset($msgTexts[$msg])): ?>
<div class="toast"><?= $msgTexts[$msg] ?></div>
<?php endif; ?>

<!-- SIDEBAR -->
<aside class="sidebar" id="sidebar">

    <!-- Logo -->
    <div class="sidebar-logo">
        <div class="logo-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="20" height="20">
                <rect x="1" y="3" width="15" height="13" rx="2"/><path d="M16 8h4l3 5v3h-7V8z"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/>
            </svg>
        </div>
        <div class="sidebar-brand">
            <span class="name">KayRom</span>
            <span class="tag">Taller &amp; Repuestos</span>
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
    <a href="index.php?page=complementos" class="nav-icon-btn <?= $activePage==='complementos'?'active':'' ?>">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 1 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 1 1-2.83-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 1 1 2.83-2.83l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 1 1 2.83 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>
        <span class="tooltip">Complementos</span>
    </a>
     <?php endif; ?>

    <a href="index.php?page=carrito" class="nav-icon-btn <?= $activePage==='carrito'?'active':'' ?>">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg>
        <span class="tooltip">Carrito</span>
    </a>
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

    <div class="sidebar-footer">
        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg>
        Azul Eléctrico · v2.0
    </div>
</aside>

<!-- TOPBAR -->
<div class="sidebar-backdrop" id="sidebar-backdrop" onclick="cerrarSidebar()"></div>

<header class="topbar">
    <button type="button" class="menu-toggle" onclick="abrirSidebar()" aria-label="Abrir menú">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
    </button>
    <div class="topbar-title">Sistema de Inventario de Vehículos y Repuestos</div>
    <div class="topbar-right">
        <div class="topbar-user">
            <div style="text-align:right; line-height:1.3;">
                <div style="font-size:13px; color:var(--text-primary); font-weight:600;">
                    <?= htmlspecialchars($nombreUsuario) ?>
                </div>
                <?php if ($rolUsuario): ?>
                <div style="font-size:11px; color:var(--accent-blue-bright); font-family:var(--font-mono); text-transform:uppercase; letter-spacing:.04em;">
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
        <div class="modal-icono">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
        </div>
        <p id="modal-mensaje" class="modal-mensaje"></p>
        <p class="modal-subtexto">Esta acción no se puede deshacer.</p>
        <div class="modal-botones">
            <button type="button" id="btn-modal-cancelar" class="btn-cancelar">Cancelar</button>
            <button type="button" id="btn-modal-aceptar" class="btn-aceptar">Eliminar</button>
        </div>
    </div>
    </div>
                    
    <script>
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
        const mensaje = (await respuesta.text()).trim();
        const toast = document.getElementById(toastId);
        if (toast) {
            toast.textContent = '⚠️ ' + (mensaje || 'Ocurrió un error, probá de nuevo.');
        } else {
            alert(mensaje || 'Ocurrió un error, probá de nuevo.');
        }
    } catch (err) {
        const toast = document.getElementById(toastId);
        if (toast) toast.textContent = '⚠️ Error de conexión, probá de nuevo.';
    } finally {
        boton.disabled = false;
        boton.textContent = textoOriginal;
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

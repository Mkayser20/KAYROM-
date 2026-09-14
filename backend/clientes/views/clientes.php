<?php require_once 'compartidoCREO/views/encabezado.php'; ?>

<div class="page-header">
    <h1><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align:-4px;margin-right:4px;"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg> Clientes</h1>
    <button type="button" class="btn btn-primary" onclick="abrirCliente()">＋ Nuevo Cliente</button>
</div>

<?php if (!empty($_GET['error'])): ?>
    <div class="toast-error" style="position:static;display:flex;margin-bottom:16px;">⚠️ <?= htmlspecialchars($_GET['error']) ?></div>
<?php endif; ?>

<div class="panel">
    <div class="panel-body">
        <table>
            <thead><tr><th>#</th><th>Nombre</th><th>DNI</th><th>Teléfono</th><th>Vehículos</th><th>Acciones</th></tr></thead>
            <tbody>
            <?php if (empty($data['clientes'])): ?>
                <tr><td colspan="6" style="text-align:center;color:var(--text-muted);padding:30px;">Sin clientes registrados todavía</td></tr>
            <?php else: foreach ($data['clientes'] as $c): ?>
                <tr>
                    <td style="color:var(--text-muted);"><?= $c['id'] ?></td>
                    <td>
                        <a href="index.php?page=clientes&action=verFicha&id=<?= $c['id'] ?>" style="color:var(--text-primary);text-decoration:none;font-weight:600;">
                            <?= htmlspecialchars($c['nombre'] . ' ' . $c['apellido']) ?>
                        </a>
                    </td>
                    <td style="color:var(--text-muted);"><?= htmlspecialchars($c['dni']) ?></td>
                    <td style="color:var(--text-muted);"><?= htmlspecialchars($c['telefono'] ?: '—') ?></td>
                    <td><span class="badge-status disponible"><?= $c['cantidad_vehiculos'] ?></span></td>
                    <td>
                        <div class="action-icons">
                            <a href="index.php?page=clientes&action=verFicha&id=<?= $c['id'] ?>" class="action-edit" title="Ver ficha">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                            </a>
                            <a href="#" class="action-edit" title="Editar"
                               onclick="return abrirCliente({
                                    id: <?= $c['id'] ?>,
                                    nombre: <?= htmlspecialchars(json_encode($c['nombre']), ENT_QUOTES, 'UTF-8') ?>,
                                    apellido: <?= htmlspecialchars(json_encode($c['apellido']), ENT_QUOTES, 'UTF-8') ?>,
                                    dni: <?= htmlspecialchars(json_encode($c['dni']), ENT_QUOTES, 'UTF-8') ?>,
                                    telefono: <?= htmlspecialchars(json_encode($c['telefono']), ENT_QUOTES, 'UTF-8') ?>
                               })">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                            </a>
                            <a href="index.php?page=clientes&action=delete&id=<?= $c['id'] ?>" class="action-delete" title="Eliminar"
                               onclick="return confirmarAccion(this.href, '¿Eliminar este cliente?')">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                            </a>
                        </div>
                    </td>
                </tr>
            <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal: alta / edición de cliente -->
<div id="modal-cliente" class="quick-modal-overlay">
    <div class="quick-modal-box">
        <div class="quick-modal-header">
            <h3 id="cliente-titulo">＋ Nuevo Cliente</h3>
            <button type="button" class="quick-modal-close" onclick="cerrarModal('modal-cliente')">&times;</button>
        </div>
        <div class="quick-modal-body">
            <form method="POST" id="form-cliente" action="index.php?page=clientes&action=create" onsubmit="return enviarModalForm(this, event, 'toast-error-cliente')">
                <input type="hidden" name="ajax" value="1">
                <input type="hidden" name="id" id="cliente-id">
                <div id="toast-error-cliente" class="toast-error"></div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Nombre</label>
                        <input type="text" name="nombre" id="cliente-nombre">
                    </div>
                    <div class="form-group">
                        <label>Apellido</label>
                        <input type="text" name="apellido" id="cliente-apellido">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>DNI</label>
                        <input type="text" name="dni" id="cliente-dni">
                    </div>
                    <div class="form-group">
                        <label>Teléfono</label>
                        <input type="text" name="telefono" id="cliente-telefono">
                    </div>
                </div>
                <button type="submit" class="btn btn-primary" id="cliente-submit">✅ Guardar</button>
            </form>
        </div>
    </div>
</div>

<script>
// Un solo modal para alta y edición de cliente
function abrirCliente(c) {
    const form   = document.getElementById('form-cliente');
    const titulo = document.getElementById('cliente-titulo');
    if (c && c.id) {
        form.action = 'index.php?page=clientes&action=edit';
        titulo.textContent = '✏️ Editar Cliente';
        document.getElementById('cliente-id').value = c.id;
        document.getElementById('cliente-nombre').value = c.nombre || '';
        document.getElementById('cliente-apellido').value = c.apellido || '';
        document.getElementById('cliente-dni').value = c.dni || '';
        document.getElementById('cliente-telefono').value = c.telefono || '';
    } else {
        form.reset();
        form.action = 'index.php?page=clientes&action=create';
        titulo.textContent = '＋ Nuevo Cliente';
    }
    abrirModal('modal-cliente');
    return false;
}
</script>

<?php require_once 'compartidoCREO/views/pie_pagina.php'; ?>

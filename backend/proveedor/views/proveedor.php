<?php require_once 'compartidoCREO/views/encabezado.php'; ?>

<div class="page-header">
    <h1>📦 Proveedores</h1>
    <button type="button" class="btn btn-primary" onclick="abrirProveedor()">+ Nuevo Proveedor</button>
</div>

<div class="panel">
    <div class="panel-body">
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Nombre</th>
                    <th>CUIT</th>
                    <th>Teléfono</th>
                    <th>Email</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
            <?php if (empty($data['proveedores'])): ?>
                <tr><td colspan="6" style="text-align:center;color:var(--text-muted);padding:30px;">Sin proveedores registrados</td></tr>
            <?php else: foreach ($data['proveedores'] as $p): ?>
                <tr>
                    <td><?= $p['id'] ?></td>
                    <td><strong><?= htmlspecialchars($p['nombre_proveedor']) ?></strong></td>
                    <td><?= $p['cuit'] ?></td>
                    <td><?= $p['telefono'] ?></td>
                    <td><?= htmlspecialchars($p['email']) ?></td>
                    <td>
                        <div class="action-icons">
                            <a href="#" class="action-edit"
                               onclick="return abrirProveedor({
                                    id: <?= $p['id'] ?>,
                                    nombre_proveedor: <?= htmlspecialchars(json_encode($p['nombre_proveedor']), ENT_QUOTES, 'UTF-8') ?>,
                                    cuit: <?= htmlspecialchars(json_encode($p['cuit']), ENT_QUOTES, 'UTF-8') ?>,
                                    telefono: <?= htmlspecialchars(json_encode($p['telefono']), ENT_QUOTES, 'UTF-8') ?>,
                                    email: <?= htmlspecialchars(json_encode($p['email']), ENT_QUOTES, 'UTF-8') ?>
                               })"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg></a>
                            <a href="index.php?page=proveedores&action=delete&id=<?= $p['id'] ?>" class="action-delete"
                               onclick="return confirmarAccion(this.href, '¿Eliminar este proveedor?')"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg></a>
                        </div>
                    </td>
                </tr>
            <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal: alta / edición de proveedor (se reusa para las dos cosas) -->
<div id="modal-proveedor" class="quick-modal-overlay">
    <div class="quick-modal-box">
        <div class="quick-modal-header">
            <h3 id="modal-proveedor-titulo">＋ Nuevo Proveedor</h3>
            <button type="button" class="quick-modal-close" onclick="cerrarModal('modal-proveedor')">&times;</button>
        </div>
        <div class="quick-modal-body">
            <form method="POST" id="form-proveedor" action="index.php?page=proveedores&action=create" onsubmit="return enviarModalForm(this, event, 'toast-error')">
                <input type="hidden" name="ajax" value="1">
                <div id="toast-error" class="toast-error"></div>
                <div class="form-group">
                    <label>Nombre del Proveedor</label>
                    <input type="text" name="nombre_proveedor" id="pv-nombre">
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>CUIT</label>
                        <input type="number" name="cuit" id="pv-cuit">
                    </div>
                    <div class="form-group">
                        <label>Teléfono</label>
                        <input type="number" name="telefono" id="pv-telefono">
                    </div>
                </div>
                <div class="form-group">
                    <label>Email</label>
                    <input type="text" name="email" id="pv-email">
                </div>
                <button type="submit" class="btn btn-primary" id="pv-submit">✅ Guardar</button>
            </form>
        </div>
    </div>
</div>

<script>
// Un solo modal para alta y edición: sin datos = alta; con datos = edición.
function abrirProveedor(p) {
    const form   = document.getElementById('form-proveedor');
    const titulo = document.getElementById('modal-proveedor-titulo');
    const submit = document.getElementById('pv-submit');

    if (p && p.id) {
        form.action = 'index.php?page=proveedores&action=edit&id=' + p.id;
        titulo.textContent = '✏️ Editar Proveedor';
        submit.textContent = '💾 Actualizar';
        document.getElementById('pv-nombre').value   = p.nombre_proveedor || '';
        document.getElementById('pv-cuit').value     = p.cuit || '';
        document.getElementById('pv-telefono').value = p.telefono || '';
        document.getElementById('pv-email').value    = p.email || '';
    } else {
        form.action = 'index.php?page=proveedores&action=create';
        titulo.textContent = '＋ Nuevo Proveedor';
        submit.textContent = '✅ Guardar';
        form.reset();
    }
    abrirModal('modal-proveedor');
    return false;
}
</script>

<?php require_once 'compartidoCREO/views/pie_pagina.php'; ?>

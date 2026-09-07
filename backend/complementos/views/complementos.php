<?php require_once 'compartidoCREO/views/encabezado.php'; ?>

<div class="page-header">
    <h1>⚙️ Complementos</h1>
</div>

<?php if (!empty($_GET['error'])): ?>
    <div class="toast-error" style="position:static;display:flex;margin-bottom:16px;">⚠️ <?= htmlspecialchars($_GET['error']) ?></div>
<?php endif; ?>

<div class="dashboard-grid">

    <!-- ───── MODELOS DE VEHÍCULO ───── -->
    <div class="panel">
        <div class="panel-header">
            <div class="panel-title">🚘 Modelos de Vehículo</div>
            <button type="button" class="btn btn-primary" style="font-size:12px;padding:6px 12px;" onclick="abrirModelo()">＋ Nuevo</button>
        </div>
        <div class="panel-body">
            <table>
                <thead><tr><th>Modelo</th><th>En uso</th><th>Acciones</th></tr></thead>
                <tbody>
                <?php if (empty($data['modelos'])): ?>
                    <tr><td colspan="3" style="text-align:center;color:var(--text-muted);padding:20px;">Sin modelos cargados</td></tr>
                <?php else: foreach ($data['modelos'] as $m): ?>
                    <tr>
                        <td><?= htmlspecialchars($m['modelo_vehiculo']) ?></td>
                        <td style="color:var(--text-muted);"><?= $m['cantidad_vehiculos'] ?> vehículo(s)</td>
                        <td>
                            <div class="action-icons">
                                <a href="#" class="action-edit"
                                   onclick="return abrirModelo({id: <?= $m['id'] ?>, nombre: <?= htmlspecialchars(json_encode($m['modelo_vehiculo']), ENT_QUOTES, 'UTF-8') ?>})"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg></a>
                                <a href="index.php?page=complementos&action=eliminarModelo&id=<?= $m['id'] ?>" class="action-delete"
                                   onclick="return confirmarAccion(this.href, '¿Eliminar este modelo?')"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg></a>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- ───── TIPOS DE VEHÍCULO ───── -->
    <div class="panel">
        <div class="panel-header">
            <div class="panel-title">🏷️ Tipos de Vehículo</div>
            <button type="button" class="btn btn-primary" style="font-size:12px;padding:6px 12px;" onclick="abrirTipo()">＋ Nuevo</button>
        </div>
        <div class="panel-body">
            <table>
                <thead><tr><th>Tipo</th><th>En uso</th><th>Acciones</th></tr></thead>
                <tbody>
                <?php if (empty($data['tipos'])): ?>
                    <tr><td colspan="3" style="text-align:center;color:var(--text-muted);padding:20px;">Sin tipos cargados</td></tr>
                <?php else: foreach ($data['tipos'] as $t): ?>
                    <tr>
                        <td><?= htmlspecialchars($t['tipo_vehiculo']) ?></td>
                        <td style="color:var(--text-muted);"><?= $t['cantidad_vehiculos'] ?> vehículo(s)</td>
                        <td>
                            <div class="action-icons">
                                <a href="#" class="action-edit"
                                   onclick="return abrirTipo({id: <?= $t['id'] ?>, nombre: <?= htmlspecialchars(json_encode($t['tipo_vehiculo']), ENT_QUOTES, 'UTF-8') ?>})"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg></a>
                                <a href="index.php?page=complementos&action=eliminarTipo&id=<?= $t['id'] ?>" class="action-delete"
                                   onclick="return confirmarAccion(this.href, '¿Eliminar este tipo?')"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg></a>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>

<!-- Modal: alta / edición de Modelo -->
<div id="modal-modelo" class="quick-modal-overlay">
    <div class="quick-modal-box">
        <div class="quick-modal-header">
            <h3 id="modelo-titulo">＋ Nuevo Modelo</h3>
            <button type="button" class="quick-modal-close" onclick="cerrarModal('modal-modelo')">&times;</button>
        </div>
        <div class="quick-modal-body">
            <form method="POST" id="form-modelo" action="index.php?page=complementos&action=crearModelo">
                <input type="hidden" name="id" id="modelo-id">
                <div class="form-group">
                    <label>Nombre del Modelo</label>
                    <input type="text" name="nombre" id="modelo-nombre" placeholder="Ej: Toyota Hilux">
                </div>
                <button type="submit" class="btn btn-primary" id="modelo-submit">✅ Guardar</button>
            </form>
        </div>
    </div>
</div>

<!-- Modal: alta / edición de Tipo -->
<div id="modal-tipo" class="quick-modal-overlay">
    <div class="quick-modal-box">
        <div class="quick-modal-header">
            <h3 id="tipo-titulo">＋ Nuevo Tipo</h3>
            <button type="button" class="quick-modal-close" onclick="cerrarModal('modal-tipo')">&times;</button>
        </div>
        <div class="quick-modal-body">
            <form method="POST" id="form-tipo" action="index.php?page=complementos&action=crearTipo">
                <input type="hidden" name="id" id="tipo-id">
                <div class="form-group">
                    <label>Nombre del Tipo</label>
                    <input type="text" name="nombre" id="tipo-nombre" placeholder="Ej: Automóvil, Moto, Camioneta">
                </div>
                <button type="submit" class="btn btn-primary" id="tipo-submit">✅ Guardar</button>
            </form>
        </div>
    </div>
</div>

<script>
// Un solo modal para alta y edición de Modelo (sin datos = alta; con datos = edición)
function abrirModelo(m) {
    const form   = document.getElementById('form-modelo');
    const titulo = document.getElementById('modelo-titulo');
    if (m && m.id) {
        form.action = 'index.php?page=complementos&action=editarModelo';
        titulo.textContent = '✏️ Editar Modelo';
        document.getElementById('modelo-id').value = m.id;
        document.getElementById('modelo-nombre').value = m.nombre || '';
    } else {
        form.reset();
        form.action = 'index.php?page=complementos&action=crearModelo';
        titulo.textContent = '＋ Nuevo Modelo';
        document.getElementById('modelo-id').value = '';
    }
    abrirModal('modal-modelo');
    return false;
}

// Lo mismo para Tipo
function abrirTipo(t) {
    const form   = document.getElementById('form-tipo');
    const titulo = document.getElementById('tipo-titulo');
    if (t && t.id) {
        form.action = 'index.php?page=complementos&action=editarTipo';
        titulo.textContent = '✏️ Editar Tipo';
        document.getElementById('tipo-id').value = t.id;
        document.getElementById('tipo-nombre').value = t.nombre || '';
    } else {
        form.reset();
        form.action = 'index.php?page=complementos&action=crearTipo';
        titulo.textContent = '＋ Nuevo Tipo';
        document.getElementById('tipo-id').value = '';
    }
    abrirModal('modal-tipo');
    return false;
}
</script>

<?php require_once 'compartidoCREO/views/pie_pagina.php'; ?>

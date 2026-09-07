<?php require_once 'compartidoCREO/views/encabezado.php'; ?>

<div class="page-header">
    <h1>Empleados</h1>
    <button type="button" class="btn btn-primary" onclick="abrirEmpleadoModal('create')">＋ Nuevo Empleado</button>
</div>

<div class="panel">
    <div class="panel-body">
        <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Nombre</th>
                <th>Apellido</th>
                <th>Puesto</th>
                <th>Especialidad</th>
                <th>Email</th>
                <th>Usuario</th>
                <th>Rol</th>
                <th>Estado</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($data['empleados'] as $e): ?>
            <tr>
                <td style="color:var(--text-muted);"><?= $e['id'] ?></td>
                <td><?= htmlspecialchars($e['nombre'] ?? '-') ?></td>
                <td><?= htmlspecialchars($e['apellido'] ?? '-') ?></td>
                <td style="color:var(--text-muted);"><?= htmlspecialchars($e['rango_trabajo'] ?? '-') ?></td>
                <td style="color:var(--text-muted);"><?= htmlspecialchars($e['especialidad'] ?? '-') ?></td>
                <td style="color:var(--text-muted);"><?= htmlspecialchars($e['email'] ?? '-') ?></td>
                <td style="color:var(--text-muted);"><?= htmlspecialchars($e['nombre_usuario'] ?? '-') ?></td>
                <td><?= htmlspecialchars($e['rol'] ?? '-') ?></td>
                <td>
                    <span class="badge-status <?= $e['activo'] ? 'disponible' : 'vendido' ?>">
                        <?= $e['activo'] ? 'Activo' : 'Inactivo' ?>
                    </span>
                </td>
                <td>
                    <div class="action-icons">
                        <a href="#" class="action-edit" onclick="return abrirEmpleadoModal('edit', <?= $e['id'] ?>)"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg></a>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
        </table>
    </div>
</div>

<!-- Modal: alta / edición de empleado. Carga la página real en un iframe -->
<div id="modal-empleado" class="quick-modal-overlay">
    <div class="quick-modal-box" style="max-width:720px; height:85vh;">
        <div class="quick-modal-header">
            <h3 id="emp-titulo">＋ Nuevo Empleado</h3>
            <button type="button" class="quick-modal-close" onclick="cerrarModal('modal-empleado')">&times;</button>
        </div>
        <iframe id="empleado-iframe" src="" style="width:100%; height:calc(100% - 57px); border:none; background:var(--bg-dark);"></iframe>
    </div>
</div>

<script>
function abrirEmpleadoModal(modo, id) {
    const iframe = document.getElementById('empleado-iframe');
    const titulo = document.getElementById('emp-titulo');
    if (modo === 'edit') {
        iframe.src = 'index.php?page=empleados&action=edit&id=' + id + '&modal=1';
        titulo.textContent = '✏️ Editar Empleado';
    } else {
        iframe.src = 'index.php?page=empleados&action=create&modal=1';
        titulo.textContent = '＋ Nuevo Empleado';
    }
    abrirModal('modal-empleado');
    return false;
}
</script>

<?php require_once 'compartidoCREO/views/pie_pagina.php'; ?>
<?php require_once 'compartidoCREO/views/encabezado.php'; ?>

<div class="page-header">
    <h1>Empleados</h1>
</div>

<div class="panel">
    <div class="panel-body">
        <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Nombre</th>
                <th>Apellido</th>
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
                        <a href="index.php?page=empleados&action=edit&id=<?= $e['id'] ?>" class="action-edit">✏️</a>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
        </table>
    </div>
</div>

<?php require_once 'compartidoCREO/views/pie_pagina.php'; ?>
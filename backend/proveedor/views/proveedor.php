<?php require_once 'views/encabezado.php'; ?>

<div class="page-header">
    <h1>📦 Proveedores</h1>
    <a href="index.php?page=proveedores&action=create" class="btn btn-green">+ Nuevo Proveedor</a>
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
                            <a href="index.php?page=proveedores&action=edit&id=<?= $p['id'] ?>" class="action-edit">✏️</a>
                            <a href="index.php?page=proveedores&action=delete&id=<?= $p['id'] ?>" class="action-delete"
                               onclick="return confirmarAccion(this.href, '¿Eliminar este proveedor?')">🗑️</a>
                        </div>
                    </td>
                </tr>
            <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once 'views/pie_pagina.php'; ?>

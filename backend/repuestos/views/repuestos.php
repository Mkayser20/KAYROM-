<?php
require_once 'compartidoCREO/views/encabezado.php';
?>

<div class="page-header">
    <h1>🔧 Repuestos</h1>
    <a href="index.php?page=repuestos&action=create" class="btn btn-green">＋ Nuevo Repuesto</a>
</div>

<div class="panel">
    <div class="panel-body">
        <table>
            <thead>
                <tr>
                    <th>#</th><th>Nombre</th><th>Categoría</th><th>Stock</th><th>Stock Mín.</th><th>Precio</th><th>Estado</th><th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($data['repuestos'] as $r): ?>
                <?php $bajo = $r['stock'] <= $r['stock_minimo']; ?>
                <tr>
                    <td style="color:var(--text-muted);"><?= $r['id'] ?></td>
                    <td><?= htmlspecialchars($r['nombre']) ?></td>
                    <td style="color:var(--text-muted);"><?= htmlspecialchars($r['categoria']) ?></td>
                    <td>
                        <strong style="color:<?= $bajo ? 'var(--accent-orange)' : 'var(--accent-green)' ?>">
                            <?= $r['stock'] ?>
                        </strong>
                    </td>
                    <td style="color:var(--text-muted);"><?= $r['stock_minimo'] ?></td>
                    <td>$<?= number_format($r['precio'],0,',','.') ?></td>
                    <td>
                        <?php if ($r['stock'] == 0): ?>
                            <span class="badge-status vendido">Sin Stock</span>
                        <?php elseif ($bajo): ?>
                            <span class="badge-status reparacion">Stock Bajo</span>
                        <?php else: ?>
                            <span class="badge-status disponible">OK</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <div class="action-icons">
                            <a href="index.php?page=repuestos&action=edit&id=<?= $r['id'] ?>" class="action-edit">✏️</a>
                            <a href="index.php?page=repuestos&action=delete&id=<?= $r['id'] ?>" class="action-delete"
                            onclick="return confirmarAccion(this.href, '¿Eliminar este repuesto?')">🗑️</a>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once 'compartidoCREO/views/pie_pagina.php'; ?>

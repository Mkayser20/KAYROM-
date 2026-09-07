<?php require_once 'compartidoCREO/views/encabezado.php'; ?>

<div class="page-header">
    <h1>🔧 Productos / Stock</h1>
    <a href="index.php?page=productos&action=create" class="btn btn-primary">＋ Nuevo Producto</a>
</div>

<div class="panel">
    <div class="panel-body">
        <table>
            <thead>
                <tr><th>#</th><th>Nombre</th><th>Tipo</th><th>Cantidad</th><th>Stock Disponible</th><th>Alerta</th><th>Costo</th><th>Estado</th><th>Acciones</th></tr>
            </thead>
            <tbody>
            <?php if(empty($data['productos'])): ?>
                <tr><td colspan="9" style="text-align:center;color:var(--text-muted);padding:30px;">No hay productos registrados</td></tr>
            <?php else: foreach($data['productos'] as $p): ?>
                <?php $bajo = ($p['cantidad_disponible'] ?? 0) <= ($p['alerta_stockBajo'] ?? 0); ?>
                <tr>
                    <td style="color:var(--text-muted);"><?= $p['id'] ?></td>
                    <td><strong><?= htmlspecialchars($p['nombre_producto']) ?></strong></td>
                    <td style="color:var(--text-muted);"><?= htmlspecialchars($p['tipo_producto']) ?></td>
                    <td><?= $p['cantidad_producto'] ?></td>
                    <td>
                        <strong style="color:<?= $bajo ? 'var(--accent-orange)' : 'var(--accent-green)' ?>">
                            <?= $p['cantidad_disponible'] ?? 0 ?>
                        </strong>
                    </td>
                    <td style="color:var(--text-muted);"><?= $p['alerta_stockBajo'] ?? '-' ?></td>
                    <td>$<?= number_format($p['costo_producto'], 2, ',', '.') ?></td>
                    <td>
                        <?php if(($p['cantidad_disponible']??0)==0): ?>
                            <span class="badge-status vendido">Sin Stock</span>
                        <?php elseif($bajo): ?>
                            <span class="badge-status reparacion">Stock Bajo</span>
                        <?php else: ?>
                            <span class="badge-status disponible">OK</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <div class="action-icons">
                            <a href="index.php?page=productos&action=edit&id=<?= $p['id'] ?>" class="action-edit"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg></a>
                            <a href="index.php?page=productos&action=delete&id=<?= $p['id'] ?>" class="action-delete"
                            onclick="return confirmarAccion(this.href, '¿Eliminar este producto?')"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg></a>
                        </div>
                    </td>
                </tr>
            <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php require_once 'compartidoCREO/views/pie_pagina.php'; ?>

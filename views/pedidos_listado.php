<?php require_once 'views/encabezado.php'; ?>

<div class="page-header">
    <h1>📦 Pedidos</h1>
    <a href="index.php?page=pedidos&action=create" class="btn btn-primary">＋ Nuevo Pedido</a>
</div>

<div class="panel">
    <div class="panel-body">
        <table>
            <thead>
                <tr><th>#</th><th>Fecha</th><th>N° Único</th><th>Estado</th><th>Responsable</th><th>Cantidad</th><th>Detalle</th><th>Acciones</th></tr>
            </thead>
            <tbody>
            <?php if(empty($data['pedidos'])): ?>
                <tr><td colspan="8" style="text-align:center;color:var(--text-muted);padding:30px;">No hay pedidos registrados</td></tr>
            <?php else: foreach($data['pedidos'] as $p): ?>
                <?php
                $ecls = match(strtolower($p['estado_pedido'] ?? '')) {
                    'entregado','completado','finalizado' => 'disponible',
                    'cancelado','rechazado'               => 'vendido',
                    default                               => 'reparacion'
                };
                ?>
                <tr>
                    <td style="color:var(--text-muted);"><?= $p['id'] ?></td>
                    <td style="color:var(--text-muted);"><?= substr($p['fecha_pedidos'],0,10) ?></td>
                    <td><strong>#<?= $p['numero_unico'] ?></strong></td>
                    <td><span class="badge-status <?= $ecls ?>"><?= htmlspecialchars($p['estado_pedido']) ?></span></td>
                    <td><?= htmlspecialchars($p['responsable_pedido'] ?? '-') ?></td>
                    <td><?= $p['cantidad'] ?></td>
                    <td style="color:var(--text-muted);font-size:12px;"><?= htmlspecialchars(substr($p['detalle_pedido'] ?? '-', 0, 40)) ?></td>
                    <td>
                        <div class="action-icons">
                            <?php if(strtolower($p['estado_pedido']) !== 'entregado'): ?>
                            <a href="index.php?page=pedidos&action=delete&id=<?= $p['id'] ?>"
                            onclick="return confirmarAccion(this.href, '¿Eliminar este pedido?')"
                            style="color:var(--text-primary);"
                            title="Eliminar">🗑️</a>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
            <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php require_once 'views/pie_pagina.php'; ?>

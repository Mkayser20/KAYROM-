<?php require_once 'compartidoCREO/views/encabezado.php'; ?>

<div class="page-header">
    <h1>🛒 Mi Carrito</h1>
</div>

<?php if (empty($items)): ?>
    <div class="panel"><div class="panel-body" style="padding:30px;text-align:center;color:var(--text-muted);">
        Tu carrito está vacío. Agregá repuestos desde el <a href="index.php?page=repuestos" style="color:var(--accent-blue-bright);">listado de repuestos</a>.
    </div></div>
<?php else: ?>

    <div class="panel">
        <div class="panel-body">
            <table>
                <thead>
                    <tr>
                        <th>Repuesto</th>
                        <th>Precio</th>
                        <th>Cantidad</th>
                        <th>Subtotal</th>
                        <th>Acción</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $total = 0;
                    foreach ($items as $item):
                        $subtotal = $item['precio'] * $item['cantidad'];
                        $total += $subtotal;
                    ?>
                    <tr>
                        <td><?= htmlspecialchars($item['nombre']) ?></td>
                        <td style="color:var(--text-muted);">$<?= number_format($item['precio'], 2, ',', '.') ?></td>
                        <td>
                            <div class="action-icons">
                                <a href="index.php?page=carrito&action=cambiar&id=<?= $item['id'] ?>&delta=-1" class="action-edit" title="Quitar uno"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><line x1="5" y1="12" x2="19" y2="12"/></svg></a>
                                <strong style="margin: 0 8px;"><?= $item['cantidad'] ?></strong>
                                <a href="index.php?page=carrito&action=cambiar&id=<?= $item['id'] ?>&delta=1" class="action-edit" title="Agregar uno"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg></a>
                            </div>
                        </td>
                        <td><strong>$<?= number_format($subtotal, 2, ',', '.') ?></strong></td>
                        <td>
                            <a href="index.php?page=carrito&action=quitar&id=<?= $item['id'] ?>" class="action-delete"
                               onclick="return confirmarAccion(this.href, '¿Quitar este repuesto del carrito?')"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg></a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="panel" style="margin-top:16px;">
        <div class="panel-body" style="padding:20px; display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:14px;">
            <div>
                <div class="stat-label">Total del carrito</div>
                <div class="stat-value" style="font-size:26px;">$<?= number_format($total, 2, ',', '.') ?></div>
            </div>
            <a href="index.php?page=carrito&action=solicitar" class="btn btn-primary" style="padding:12px 22px;font-size:14px;"
               onclick="return confirmarAccion(this.href, 'Esto va a generar un pedido de compra con todo lo que hay en el carrito y lo va a vaciar. ¿Confirmás?')">
                📤 Solicitar al Proveedor
            </a>
        </div>
    </div>

<?php endif; ?>

<?php require_once 'compartidoCREO/views/pie_pagina.php'; ?>

<?php require_once 'compartidoCREO/views/encabezado.php'; ?>

<div class="page-header">
    <h1>Mi Carrito</h1>
</div>

<?php if (empty($items)): ?>
    <p>Tu carrito está vacío. Agregá repuestos desde el listado de repuestos.</p>
<?php else: ?>

    <table class="tabla">
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
                <td>$<?= number_format($item['precio'], 2) ?></td>
                <td>
                    <div class="action-icons">
                        <a href="index.php?page=carrito&action=cambiar&id=<?= $item['id'] ?>&delta=-1"
                           class="action-edit" title="Quitar uno">➖</a>
                        <strong style="margin: 0 8px;"><?= $item['cantidad'] ?></strong>
                        <a href="index.php?page=carrito&action=cambiar&id=<?= $item['id'] ?>&delta=1"
                           class="action-edit" title="Agregar uno">➕</a>
                    </div>
                </td>
                <td>$<?= number_format($subtotal, 2) ?></td>
                <td>
                    <a href="index.php?page=carrito&action=quitar&id=<?= $item['id'] ?>"
                       class="action-edit">🗑️</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
        <tfoot>
            <tr>
                <td colspan="3"><strong>Total</strong></td>
                <td colspan="2"><strong>$<?= number_format($total, 2) ?></strong></td>
            </tr>
        </tfoot>
    </table>

<?php endif; ?>

<?php require_once 'compartidoCREO/views/pie_pagina.php'; ?>
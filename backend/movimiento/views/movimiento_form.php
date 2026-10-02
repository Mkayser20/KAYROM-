<?php
require_once 'compartidoCREO/views/encabezado.php';
?>

<div class="page-header">
    <h1>＋ Movimiento manual</h1>
    <a href="index.php?page=movimientos" class="btn btn-ghost">← Volver</a>
</div>

<div class="form-card">
    <form method="POST" action="index.php?page=movimientos&action=create">
        <!-- Si el StockService devolvió un error (ej: stock insuficiente), se muestra acá -->
        <div id="toast-error" class="toast-error"><?= !empty($data['error']) ? '⚠️ ' . htmlspecialchars($data['error']) : '' ?></div>

        <div class="form-group">
            <label>Repuesto</label>
            <select name="repuesto_id" required>
                <option value="">— Elegí un repuesto —</option>
                <?php foreach ($data['repuestos'] as $r): ?>
                <option value="<?= $r['id'] ?>"><?= htmlspecialchars($r['nombre']) ?> (stock actual: <?= (int)$r['stock'] ?>)</option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label>Tipo de Movimiento</label>
                <select name="tipo">
                    <option value="Entrada">📥 Entrada (suma stock)</option>
                    <option value="Salida">📤 Salida (resta stock)</option>
                </select>
            </div>
            <div class="form-group">
                <label>Cantidad</label>
                <input type="number" name="cantidad" value="1" min="1" required>
            </div>
        </div>

        <div class="form-group">
            <label>Motivo</label>
            <input type="text" name="descripcion" placeholder="Ej: Se rompió en el taller / Llegó sin pedido previo">
        </div>

        <button type="submit" class="btn btn-primary">✅ Registrar Movimiento</button>
    </form>
</div>

<?php require_once 'compartidoCREO/views/pie_pagina.php'; ?>
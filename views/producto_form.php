<?php
require_once 'views/encabezado.php';
$p = $data['producto'] ?? null;
$isEdit = !is_null($p);
$action = $isEdit ? "index.php?page=productos&action=edit&id={$p['id']}" : "index.php?page=productos&action=create";
$tipos = ['Repuesto','Lubricante','Filtro','Freno','Suspensión','Eléctrico','Carrocería','Motor','Transmisión','Otro'];
?>
<div class="page-header">
    <h1><?= $isEdit ? '✏️ Editar Producto' : '＋ Nuevo Producto' ?></h1>
    <a href="index.php?page=productos" class="btn btn-ghost">← Volver</a>
</div>
<div class="form-card">
    <form method="POST" action="<?= $action ?>">
        <div class="form-group">
            <label>Nombre del Producto</label>
            <input type="text" name="nombre_producto" value="<?= htmlspecialchars($p['nombre_producto'] ?? '') ?>" required placeholder="Ej: Frenos de Disco">
        </div>
        <div class="form-row">
            <div class="form-group">
                <label>Tipo de Producto</label>
                <select name="tipo_producto">
                    <?php foreach($tipos as $t): ?>
                    <option value="<?= $t ?>" <?= ($p['tipo_producto']??'')===$t?'selected':'' ?>><?= $t ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Costo ($)</label>
                <input type="number" name="costo_producto" value="<?= $p['costo_producto'] ?? 0 ?>" min="0" step="0.01" required>
            </div>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label>Cantidad en producto</label>
                <input type="number" name="cantidad_producto" value="<?= $p['cantidad_producto'] ?? 0 ?>" min="0" required>
            </div>
            <div class="form-group">
                <label>Stock disponible actual</label>
                <input type="number" name="cantidad_disponible" value="<?= $p['cantidad_disponible'] ?? 0 ?>" min="0">
            </div>
        </div>
        <div class="form-group">
            <label>Alerta de stock bajo (mínimo)</label>
            <input type="number" name="alerta_stockBajo" value="<?= $p['alerta_stockBajo'] ?? 5 ?>" min="0">
        </div>
        <button type="submit" class="btn btn-green"><?= $isEdit ? '💾 Actualizar' : '✅ Guardar' ?></button>
    </form>
</div>
<?php require_once 'views/pie_pagina.php'; ?>

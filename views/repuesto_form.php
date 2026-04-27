<?php
require_once 'views/encabezado.php';
$r = $data['repuesto'] ?? null;
$isEdit = !is_null($r);
$action = $isEdit ? "index.php?page=repuestos&action=edit&id={$r['id']}" : "index.php?page=repuestos&action=create";
$cats = ['Frenos','Filtros','Motor','Suspensión','Eléctrico','Lubricantes','Refrigeración','Carrocería','Transmisión'];
?>

<div class="page-header">
    <h1><?= $isEdit ? '✏️ Editar Repuesto' : '＋ Nuevo Repuesto' ?></h1>
    <a href="index.php?page=repuestos" class="btn btn-ghost">← Volver</a>
</div>

<div class="form-card">
    <form method="POST" action="<?= $action ?>">
        <div class="form-group">
            <label>Nombre</label>
            <input type="text" name="nombre" value="<?= htmlspecialchars($r['nombre'] ?? '') ?>" required placeholder="Frenos de Disco, Bujías...">
        </div>
        <div class="form-row">
            <div class="form-group">
                <label>Categoría</label>
                <select name="categoria">
                    <?php foreach ($cats as $cat): ?>
                    <option value="<?= $cat ?>" <?= ($r['categoria']??'')===$cat?'selected':'' ?>><?= $cat ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Precio ($)</label>
                <input type="number" name="precio" value="<?= $r['precio'] ?? 0 ?>" min="0" step="0.01" required>
            </div>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label>Stock Actual</label>
                <input type="number" name="stock" value="<?= $r['stock'] ?? 0 ?>" min="0" required>
            </div>
            <div class="form-group">
                <label>Stock Mínimo</label>
                <input type="number" name="stock_minimo" value="<?= $r['stock_minimo'] ?? 5 ?>" min="0" required>
            </div>
        </div>
        <button type="submit" class="btn btn-green"><?= $isEdit ? '💾 Actualizar' : '✅ Guardar' ?></button>
    </form>
</div>

<?php require_once 'views/pie_pagina.php'; ?>

<?php
require_once 'compartidoCREO/views/encabezado.php';
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
        <div id="toast-error" class="toast-error"></div>

        <div class="form-group">
            <label>Nombre del Producto</label>
            <input type="text" name="nombre_producto" value="<?= htmlspecialchars($p['nombre_producto'] ?? '') ?>"  placeholder="Ej: Frenos de Disco">
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
                <input type="number" name="costo_producto" value="<?= $p['costo_producto'] ?? 0 ?>" min="0" step="0.01" >
            </div>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label>Cantidad en producto</label>
                <input type="number" name="cantidad_producto" value="<?= $p['cantidad_producto'] ?? 0 ?>" min="0" >
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

    <script>
        function mostrarError(mensaje) {
            const toast = document.getElementById('toast-error');
            toast.textContent = mensaje;
            setTimeout(() => { toast.textContent = ''; }, 3500);
        }

        document.querySelector('form').addEventListener('submit', function(e) {
            const inputs = document.querySelectorAll('form input[name], form select[name]');
            for (const input of inputs) {
                if (!input.value.trim()) {
                    e.preventDefault();
                    const label = input.closest('.form-group')?.querySelector('label')?.textContent.replace('*', '').trim() || 'Este campo';
                    mostrarError('Completá: ' + label);
                    return;
                }
            }
        });
    </script>
    </form>
</div>
<?php require_once 'compartidoCREO/views/pie_pagina.php'; ?>

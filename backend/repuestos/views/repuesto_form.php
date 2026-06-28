<?php
require_once 'compartidoCREO/views/encabezado.php';
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
            <input type="text" name="nombre" value="<?= htmlspecialchars($r['nombre'] ?? '') ?>"  placeholder="Frenos de Disco, Bujías...">
        </div>

        <div id="toast-error" class="toast-error"></div>

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
                <input type="number" name="precio" value="<?= $r['precio'] ?? 0 ?>" min="0" step="0.01">
            </div>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label>Stock Actual</label>
                <input type="number" name="stock" value="<?= $r['stock'] ?? 0 ?>" min="0 >
            </div>
            <div class="form-group">
                <label>Stock Mínimo</label>
                <input type="number" name="stock_minimo" value="<?= $r['stock_minimo'] ?? 5 ?>" min="0">
            </div>
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

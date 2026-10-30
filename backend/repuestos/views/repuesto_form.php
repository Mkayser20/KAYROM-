<?php
require_once 'compartidoCREO/views/encabezado.php';
$r = $data['repuesto'] ?? null;
$formData = $data['formData'] ?? [];
$isEdit = !is_null($r);
$action = $isEdit ? "index.php?page=repuestos&action=edit&id={$r['id']}" : "index.php?page=repuestos&action=create";
$cats = ['Frenos','Filtros','Motor','Suspensión','Eléctrico','Lubricantes','Refrigeración','Carrocería','Transmisión'];
$categoriaActual = $formData['categoria'] ?? $r['categoria'] ?? '';
$categoriaEsFija = in_array($categoriaActual, $cats, true);
$categoriaOtro = ($formData['categoria'] ?? '') === '__otro__'
    ? ($formData['categoria_otro'] ?? '')
    : (!$categoriaEsFija ? $categoriaActual : '');
?>

<div class="page-header">
    <h1><?= $isEdit ? 'Editar Repuesto' : '＋ Nuevo Repuesto' ?></h1>
    <a href="index.php?page=repuestos" class="btn btn-ghost">← Volver</a>
</div>

<div class="form-card">
    <form method="POST" action="<?= $action ?>" enctype="multipart/form-data">
        <div class="form-group">
            <label>Nombre</label>
            <input type="text" name="nombre" value="<?= htmlspecialchars($formData['nombre'] ?? $r['nombre'] ?? '', ENT_QUOTES, 'UTF-8') ?>" placeholder="Frenos de Disco, Bujías..." required maxlength="100">
        </div>

        <div id="toast-error" class="toast-error"><?= htmlspecialchars($data['error'] ?? '') ?></div>

        <div class="form-row">
            <div class="form-group">
                <label>Categoría</label>
                <select name="categoria" id="categoria-repuesto" onchange="actualizarCategoriaFormulario(this)" required>
                    <?php foreach ($cats as $cat): ?>
                    <option value="<?= htmlspecialchars($cat, ENT_QUOTES, 'UTF-8') ?>" <?= $categoriaActual === $cat ? 'selected' : '' ?>><?= htmlspecialchars($cat, ENT_QUOTES, 'UTF-8') ?></option>
                    <?php endforeach; ?>
                    <option value="__otro__" <?= !$categoriaEsFija ? 'selected' : '' ?>>Otros...</option>
                </select>
                <input type="text" name="categoria_otro" id="categoria-repuesto-otra" value="<?= htmlspecialchars($categoriaOtro, ENT_QUOTES, 'UTF-8') ?>" placeholder="Escribí la categoría" maxlength="45" style="margin-top:8px;<?= !$categoriaEsFija ? '' : 'display:none;' ?>" <?= !$categoriaEsFija ? 'required' : 'disabled' ?>>
            </div>
            <div class="form-group">
                <label>Precio ($)</label>
                <input type="number" name="precio" value="<?= htmlspecialchars((string)($formData['precio'] ?? $r['precio'] ?? 0), ENT_QUOTES, 'UTF-8') ?>" min="0" step="0.01" required>
            </div>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label>SKU (opcional)</label>
                <input type="text" name="sku" data-optional="1" value="<?= htmlspecialchars($formData['sku'] ?? $r['sku'] ?? '', ENT_QUOTES, 'UTF-8') ?>" placeholder="Código interno o del fabricante">
            </div>
            <div class="form-group">
                <label>Proveedor habitual</label>
                <select name="proveedor_id" data-optional="1">
                    <option value="">— Sin especificar —</option>
                    <?php foreach ($data['proveedores'] as $prov): ?>
                    <option value="<?= $prov['id'] ?>" <?= (($formData['proveedor_id'] ?? $r['proveedor_id'] ?? null)==$prov['id'])?'selected':'' ?>><?= htmlspecialchars($prov['nombre_proveedor']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label>Stock Actual</label>
                <input type="number" name="stock" value="<?= htmlspecialchars((string)($formData['stock'] ?? $r['stock'] ?? 0), ENT_QUOTES, 'UTF-8') ?>" min="0">
            </div>
            <div class="form-group">
                <label>Stock Mínimo</label>
                <input type="number" name="stock_minimo" value="<?= htmlspecialchars((string)($formData['stock_minimo'] ?? $r['stock_minimo'] ?? 5), ENT_QUOTES, 'UTF-8') ?>" min="0" step="1" required>
            </div>
        </div>
        <div class="form-group">
            <label>Imagen (opcional)</label>
            <?php if (!empty($r['imagen_url'])): ?>
            <img src="<?= htmlspecialchars($r['imagen_url']) ?>" alt="Imagen actual" style="width:120px;height:120px;object-fit:cover;border-radius:12px;border:1px solid var(--border);display:block;margin-bottom:10px;">
            <label style="display:flex;align-items:center;gap:8px;margin-bottom:10px;">
                <input type="checkbox" name="quitar_imagen" value="1" data-optional="1"> Quitar la imagen actual
            </label>
            <?php endif; ?>
            <input type="file" name="imagen" accept="image/jpeg,image/png,image/webp" data-optional="1">
            <small style="color:var(--text-dim);">JPG, PNG o WebP · máx. 2 MB</small>
        </div>
        <button type="submit" class="btn btn-primary"><?= $isEdit ? 'Actualizar' : 'Guardar' ?></button>

    <script>
        function mostrarError(mensaje) {
            const toast = document.getElementById('toast-error');
            toast.textContent = mensaje;
            setTimeout(() => { toast.textContent = ''; }, 3500);
        }

        document.querySelector('form').addEventListener('submit', function(e) {
            const inputs = document.querySelectorAll('form input[name]:not([data-optional]), form select[name]:not([data-optional])');
            for (const input of inputs) {
                if (!input.value.trim()) {
                    e.preventDefault();
                    const label = input.closest('.form-group')?.querySelector('label')?.textContent.replace('*', '').trim() || 'Este campo';
                    mostrarError('Completá: ' + label);
                    return;
                }
            }
        });
        function actualizarCategoriaFormulario(select) {
            const input = document.getElementById('categoria-repuesto-otra');
            const otraCategoria = select.value === '__otro__';
            input.style.display = otraCategoria ? 'block' : 'none';
            input.disabled = !otraCategoria;
            input.required = otraCategoria;
        }
    </script>
    </form>
</div>

<?php require_once 'compartidoCREO/views/pie_pagina.php'; ?>

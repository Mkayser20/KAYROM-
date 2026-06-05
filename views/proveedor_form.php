<?php
require_once 'views/encabezado.php';
$p = $data['proveedor'] ?? [];
$isEdit = !empty($p);
?>

<div class="page-header">
    <h1><?= $isEdit ? '✏️ Editar Proveedor' : '+ Nuevo Proveedor' ?></h1>
    <a href="index.php?page=proveedores" class="btn btn-ghost">← Volver</a>
</div>

<div class="form-card">
    <form method="POST">
        <div id="toast-error" class="toast-error"></div>

        <div class="form-row">
            <div class="form-group">
                <label>Nombre del Proveedor</label>
                <input type="text" name="nombre_proveedor" value="<?= htmlspecialchars($p['nombre_proveedor'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label>CUIT</label>
                <input type="number" name="cuit" value="<?= $p['cuit'] ?? '' ?>">
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label>Teléfono</label>
                <input type="number" name="telefono" value="<?= $p['telefono'] ?? '' ?>">
            </div>
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" value="<?= htmlspecialchars($p['email'] ?? '') ?>">
            </div>
        </div>

        <button type="submit" class="btn btn-green"><?= $isEdit ? 'Actualizar' : 'Guardar' ?></button>
    </form>
</div>

<script>
function mostrarError(mensaje) {
    const toast = document.getElementById('toast-error');
    toast.textContent = mensaje;
    setTimeout(() => { toast.textContent = ''; }, 3500);
}

document.querySelector('form').addEventListener('submit', function(e) {
    const nombre   = document.querySelector('input[name="nombre_proveedor"]').value.trim();
    const cuit     = document.querySelector('input[name="cuit"]').value.trim();
    const telefono = document.querySelector('input[name="telefono"]').value.trim();
    const email    = document.querySelector('input[name="email"]').value.trim();

    if (!nombre)   { e.preventDefault(); mostrarError('Ingresá el nombre del proveedor'); return; }
    if (!cuit)     { e.preventDefault(); mostrarError('Ingresá el CUIT'); return; }
    if (!telefono) { e.preventDefault(); mostrarError('Ingresá el teléfono'); return; }
    if (!email)    { e.preventDefault(); mostrarError('Ingresá el email'); return; }
});
</script>

<?php require_once 'views/pie_pagina.php'; ?>
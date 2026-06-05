<?php
require_once 'views/encabezado.php';
$v = $data['vehiculo'] ?? null;
$error = $data['error'] ?? null;
$formData = $data['formData'] ?? null;
$isEdit = !is_null($v);
$action = $isEdit
    ? "index.php?page=vehiculos&action=edit&id={$v['id']}"
    : "index.php?page=vehiculos&action=create";
?>
<div class="page-header">
    <h1><?= $isEdit ? '✏️ Editar Vehículo' : '＋ Nuevo Vehículo' ?></h1>
    <a href="index.php?page=vehiculos" class="btn btn-ghost">← Volver</a>
</div>
<?php if ($error): ?>
<div style="background-color: #fee; border: 1px solid #fcc; color: #c00; padding: 12px; border-radius: 4px; margin-bottom: 20px;">
    <strong>❌ Error:</strong> <?= htmlspecialchars($error) ?>
</div>
<?php endif; ?>
<div class="form-card">
    <form method="POST" action="<?= $action ?>">
        <div class="form-row">
            <div class="form-group">
                <label>Patente</label>
                <input type="text" name="patente" value="<?= htmlspecialchars($formData['patente'] ?? $v['patente'] ?? '') ?>" placeholder="ABC123">
            </div>
        <div id="toast-error" class="toast-error"></div>

        <div class="form-group">
            <label>Cantidad</label>
            <input type="number" name="cantidad_vehiculo" value="1" min="1" max="1" readonly>
        </div>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label>Número de Chasis</label>
                <input type="text" name="numero_chasis" value="<?= htmlspecialchars($formData['numero_chasis'] ?? $v['numero_chasis'] ?? '') ?>" placeholder="Nro. chasis">
            </div>
            <div class="form-group">
                <label>Número de Motor</label>
                <input type="text" name="numero_motor" value="<?= htmlspecialchars($formData['numero_motor'] ?? $v['numero_motor'] ?? '') ?>" placeholder="Nro. motor">
            </div>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label>Modelo</label>
                <select name="modelo_vehiculo_id">
                    <option value="0">— Seleccionar —</option>
                    <?php foreach($data['modelos'] as $m): ?>
                    <option value="<?= $m['id'] ?>" <?= ($formData['modelo_vehiculo_id'] ?? $v['modelo_vehiculo_id'] ?? 0)==$m['id']?'selected':'' ?>>
                        <?= htmlspecialchars($m['modelo_vehiculo']) ?> (<?= $m['anio_vehiculo'] ?>)
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Tipo de Vehículo</label>
                <select name="tipo_vehiculo_id">
                    <option value="0">— Seleccionar —</option>
                    <?php foreach($data['tipos'] as $t): ?>
                    <option value="<?= $t['id'] ?>" <?= ($formData['tipo_vehiculo_id'] ?? $v['tipo_vehiculo_id'] ?? 0)==$t['id']?'selected':'' ?>>
                        <?= htmlspecialchars($t['tipo_vehiculo']) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        <div class="form-group">
            <label>Fecha de Ingreso</label>
            <input type="date" name="fecha_ingreso" value="<?= isset($formData['fecha_ingreso']) ? htmlspecialchars($formData['fecha_ingreso']) : (isset($v['fecha_ingreso']) ? substr($v['fecha_ingreso'],0,10) : date('Y-m-d')) ?>">
        </div>
        <button type="submit" class="btn btn-green"><?= $isEdit ? '💾 Actualizar' : '✅ Guardar' ?></button>

    <script>
        function mostrarError(mensaje) {
            const toast = document.getElementById('toast-error');
            toast.textContent = mensaje;
            setTimeout(() => { toast.textContent = ''; }, 3500);
        }

        document.querySelector('form').addEventListener('submit', function(e) {
            const patente  = document.querySelector('input[name="patente"]').value.trim();
            const chasis   = document.querySelector('input[name="numero_chasis"]').value.trim();
            const motor    = document.querySelector('input[name="numero_motor"]').value.trim();
            const modelo   = document.querySelector('select[name="modelo_vehiculo_id"]')?.value;
            const tipo     = document.querySelector('select[name="tipo_vehiculo_id"]')?.value;

            if (!patente) { e.preventDefault(); mostrarError('Ingresá la patente'); return; }
            if (!chasis)  { e.preventDefault(); mostrarError('Ingresá el número de chasis'); return; }
            if (!motor)   { e.preventDefault(); mostrarError('Ingresá el número de motor'); return; }
            if (!modelo)  { e.preventDefault(); mostrarError('Seleccioná el modelo'); return; }
            if (!tipo)    { e.preventDefault(); mostrarError('Seleccioná el tipo de vehículo'); return; }
        });
    </script>
    </form>
</div>
<?php require_once 'views/pie_pagina.php'; ?>

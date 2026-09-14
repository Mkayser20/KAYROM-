<?php
require_once 'compartidoCREO/views/encabezado.php';
$v = $data['vehiculo'] ?? null;
$error = $data['error'] ?? null;
$formData = $data['formData'] ?? null;
$isEdit = !is_null($v);
$action = $isEdit
    ? "index.php?page=vehiculos&action=edit&id={$v['id']}"
    : "index.php?page=vehiculos&action=create";
// El wizard de pasos solo aplica al crear un vehículo nuevo; al editar se ve todo junto
$wizard = !$isEdit;
?>
<div class="page-header">
    <h1><?= $isEdit ? '✏️ Editar Vehículo' : '＋ Nuevo Vehículo' ?></h1>
    <a href="index.php?page=vehiculos" class="btn btn-ghost">← Volver</a>
</div>
<?php if ($error): ?>
<div class="toast-error" style="position:static;display:flex;margin-bottom:20px;">⚠️ <?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<?php if ($wizard): ?>
<!-- Indicador de pasos -->
<div class="wizard-steps" id="wizard-steps">
    <div class="wizard-step active" data-step-dot="1"><span class="wizard-step-num">1</span>Vehículo</div>
    <div class="wizard-line"></div>
    <div class="wizard-step" data-step-dot="2"><span class="wizard-step-num">2</span>Cliente</div>
    <div class="wizard-line"></div>
    <div class="wizard-step" data-step-dot="3"><span class="wizard-step-num">3</span>Confirmar</div>
</div>
<?php endif; ?>

<div class="form-card">
    <div id="toast-error" class="toast-error"></div>
    <form method="POST" action="<?= $action ?>" id="form-vehiculo-completo">

        <div class="wizard-panel" data-step="1">
            <div class="form-row">
                <div class="form-group">
                    <label>Patente</label>
                    <input type="text" name="patente" value="<?= htmlspecialchars($formData['patente'] ?? $v['patente'] ?? '') ?>" placeholder="ABC123">
                </div>
                <div class="form-group">
                    <label>Año</label>
                    <input type="number" name="anio" min="1950" max="2100" placeholder="2024"
                           value="<?= htmlspecialchars($formData['anio'] ?? $v['anio'] ?? '') ?>">
                    <input type="hidden" name="cantidad_vehiculo" value="1">
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
                    <div class="combo-search" id="combo-modelo">
                        <input type="text" class="combo-input" placeholder="Buscar modelo..." autocomplete="off">
                        <input type="hidden" name="modelo_vehiculo_id" class="combo-value">
                        <div class="combo-dropdown"></div>
                    </div>
                </div>
                <div class="form-group">
                    <label>Tipo de Vehículo</label>
                    <div class="combo-search" id="combo-tipo">
                        <input type="text" class="combo-input" placeholder="Buscar tipo..." autocomplete="off">
                        <input type="hidden" name="tipo_vehiculo_id" class="combo-value">
                        <div class="combo-dropdown"></div>
                    </div>
                </div>
            </div>
            <?php if ($wizard): ?>
            <div class="wizard-nav">
                <span></span>
                <button type="button" class="btn btn-primary" onclick="wizardIr(2)">Siguiente →</button>
            </div>
            <?php endif; ?>
        </div>

        <div class="wizard-panel" data-step="2" <?= $wizard ? 'style="display:none;"' : '' ?>>
            <div class="form-row">
                <div class="form-group">
                    <label>Nombre del Cliente</label>
                    <input type="text" name="cliente_nombre" value="<?= htmlspecialchars($formData['cliente_nombre'] ?? $v['cliente_nombre'] ?? '') ?>" placeholder="Nombre">
                </div>
                <div class="form-group">
                    <label>Apellido del Cliente</label>
                    <input type="text" name="cliente_apellido" value="<?= htmlspecialchars($formData['cliente_apellido'] ?? $v['cliente_apellido'] ?? '') ?>" placeholder="Apellido">
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>DNI del Cliente</label>
                    <input type="text" name="cliente_dni" value="<?= htmlspecialchars($formData['cliente_dni'] ?? $v['cliente_dni'] ?? '') ?>" placeholder="DNI">
                </div>
                <div class="form-group">
                    <label>Teléfono del Cliente</label>
                    <input type="text" name="cliente_telefono" value="<?= htmlspecialchars($formData['cliente_telefono'] ?? $v['cliente_telefono'] ?? '') ?>" placeholder="Teléfono">
                </div>
            </div>
            <?php if ($wizard): ?>
            <div class="wizard-nav">
                <button type="button" class="btn btn-ghost" onclick="wizardIr(1)">← Atrás</button>
                <button type="button" class="btn btn-primary" onclick="wizardIr(3)">Siguiente →</button>
            </div>
            <?php endif; ?>
        </div>

        <div class="wizard-panel" data-step="3" <?= $wizard ? 'style="display:none;"' : '' ?>>
            <div class="form-row">
                <div class="form-group">
                    <label>Estado en Taller</label>
                    <?php $estActual = $formData['estado_taller'] ?? $v['estado_taller'] ?? 'En Diagnóstico'; ?>
                    <select name="estado_taller">
                        <?php foreach (['En Diagnóstico','En Reparación','Esperando Repuestos','Listo para Entrega'] as $est): ?>
                        <option value="<?= $est ?>" <?= $estActual===$est?'selected':'' ?>><?= $est ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Kilometraje</label>
                    <input type="number" name="kilometraje" min="0" value="<?= htmlspecialchars($formData['kilometraje'] ?? $v['kilometraje'] ?? 0) ?>">
                </div>
            </div>
            <div class="form-group">
                <label>Fecha de Ingreso</label>
                <input type="date" name="fecha_ingreso" value="<?= isset($formData['fecha_ingreso']) ? htmlspecialchars($formData['fecha_ingreso']) : (isset($v['fecha_ingreso']) ? substr($v['fecha_ingreso'],0,10) : date('Y-m-d')) ?>">
            </div>
            <div class="wizard-nav">
                <?php if ($wizard): ?><button type="button" class="btn btn-ghost" onclick="wizardIr(2)">← Atrás</button><?php else: ?><span></span><?php endif; ?>
                <button type="submit" class="btn btn-primary"><?= $isEdit ? '💾 Actualizar' : '✅ Guardar' ?></button>
            </div>
        </div>

    </form>
</div>

<script>
    // Opciones para los combos de Modelo y Tipo (vienen del backend)
    const opcionesModelos = <?= json_encode(array_map(fn($m) => ['id' => $m['id'], 'label' => $m['modelo_vehiculo']], $data['modelos'])) ?>;
    const opcionesTipos   = <?= json_encode(array_map(fn($t) => ['id' => $t['id'], 'label' => $t['tipo_vehiculo']], $data['tipos'])) ?>;
    crearCombo('combo-modelo', opcionesModelos);
    crearCombo('combo-tipo', opcionesTipos);
    // Si estoy editando, precargo el modelo/tipo que ya tenía este vehículo
    document.getElementById('combo-modelo')._setValor(<?= (int)($formData['modelo_vehiculo_id'] ?? $v['modelo_vehiculo_id'] ?? 0) ?>);
    document.getElementById('combo-tipo')._setValor(<?= (int)($formData['tipo_vehiculo_id'] ?? $v['tipo_vehiculo_id'] ?? 0) ?>);

    function mostrarError(mensaje) {
        const toast = document.getElementById('toast-error');
        toast.textContent = mensaje;
        setTimeout(() => { toast.textContent = ''; }, 3500);
    }

    <?php if ($wizard): ?>
    // Navegación entre pasos del wizard: valida el paso actual antes de dejar avanzar
    let pasoActual = 1;
    function wizardIr(paso) {
        // Solo valido si se está avanzando (no al volver atrás)
        if (paso > pasoActual && pasoActual === 1) {
            const patente = document.querySelector('input[name="patente"]').value.trim();
            const chasis  = document.querySelector('input[name="numero_chasis"]').value.trim();
            const motor   = document.querySelector('input[name="numero_motor"]').value.trim();
            const modelo  = document.querySelector('input[name="modelo_vehiculo_id"]').value;
            const tipo    = document.querySelector('input[name="tipo_vehiculo_id"]').value;
            if (!patente) { mostrarError('Ingresá la patente'); return; }
            if (!chasis)  { mostrarError('Ingresá el número de chasis'); return; }
            if (!motor)   { mostrarError('Ingresá el número de motor'); return; }
            if (!modelo)  { mostrarError('Seleccioná el modelo'); return; }
            if (!tipo)    { mostrarError('Seleccioná el tipo de vehículo'); return; }
        }

        pasoActual = paso;
        document.querySelectorAll('.wizard-panel').forEach(p => p.style.display = 'none');
        document.querySelector('.wizard-panel[data-step="' + paso + '"]').style.display = '';

        document.querySelectorAll('.wizard-step').forEach(s => {
            s.classList.toggle('active', parseInt(s.dataset.stepDot, 10) <= paso);
        });
    }
    <?php else: ?>
    // Modo edición: todo visible, solo valido al enviar
    document.getElementById('form-vehiculo-completo').addEventListener('submit', function(e) {
        const patente  = document.querySelector('input[name="patente"]').value.trim();
        const chasis   = document.querySelector('input[name="numero_chasis"]').value.trim();
        const motor    = document.querySelector('input[name="numero_motor"]').value.trim();
        const modelo   = document.querySelector('input[name="modelo_vehiculo_id"]')?.value;
        const tipo     = document.querySelector('input[name="tipo_vehiculo_id"]')?.value;
        if (!patente) { e.preventDefault(); mostrarError('Ingresá la patente'); return; }
        if (!chasis)  { e.preventDefault(); mostrarError('Ingresá el número de chasis'); return; }
        if (!motor)   { e.preventDefault(); mostrarError('Ingresá el número de motor'); return; }
        if (!modelo)  { e.preventDefault(); mostrarError('Seleccioná el modelo'); return; }
        if (!tipo)    { e.preventDefault(); mostrarError('Seleccioná el tipo de vehículo'); return; }
    });
    <?php endif; ?>
</script>

<?php require_once 'compartidoCREO/views/pie_pagina.php'; ?>

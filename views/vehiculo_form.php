<?php
require_once 'views/encabezado.php';
$v = $data['vehiculo'] ?? null;
$isEdit = !is_null($v);
$action = $isEdit
    ? "index.php?page=vehiculos&action=edit&id={$v['id']}"
    : "index.php?page=vehiculos&action=create";
?>
<div class="page-header">
    <h1><?= $isEdit ? '✏️ Editar Vehículo' : '＋ Nuevo Vehículo' ?></h1>
    <a href="index.php?page=vehiculos" class="btn btn-ghost">← Volver</a>
</div>
<div class="form-card">
    <form method="POST" action="<?= $action ?>">
        <div class="form-row">
            <div class="form-group">
                <label>Patente</label>
                <input type="text" name="patente" value="<?= htmlspecialchars($v['patente'] ?? '') ?>" required placeholder="ABC123">
            </div>
        
        <div class="form-group">
            <label>Cantidad</label>
            <input type="number" name="cantidad_vehiculo" value="1" min="1" max="1" readonly>
        </div>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label>Número de Chasis</label>
                <input type="text" name="numero_chasis" value="<?= htmlspecialchars($v['numero_chasis'] ?? '') ?>" placeholder="Nro. chasis">
            </div>
            <div class="form-group">
                <label>Número de Motor</label>
                <input type="text" name="numero_motor" value="<?= htmlspecialchars($v['numero_motor'] ?? '') ?>" placeholder="Nro. motor">
            </div>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label>Modelo</label>
                <select name="modelo_vehiculo_id">
                    <option value="0">— Seleccionar —</option>
                    <?php foreach($data['modelos'] as $m): ?>
                    <option value="<?= $m['id'] ?>" <?= ($v['modelo_vehiculo_id'] ?? 0)==$m['id']?'selected':'' ?>>
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
                    <option value="<?= $t['id'] ?>" <?= ($v['tipo_vehiculo_id'] ?? 0)==$t['id']?'selected':'' ?>>
                        <?= htmlspecialchars($t['tipo_vehiculo']) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        <div class="form-group">
            <label>Fecha de Ingreso</label>
            <input type="date" name="fecha_ingreso" value="<?= isset($v['fecha_ingreso']) ? substr($v['fecha_ingreso'],0,10) : date('Y-m-d') ?>">
        </div>
        <button type="submit" class="btn btn-green"><?= $isEdit ? '💾 Actualizar' : '✅ Guardar' ?></button>
    </form>
</div>
<?php require_once 'views/pie_pagina.php'; ?>

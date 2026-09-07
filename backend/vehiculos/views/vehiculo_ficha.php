<?php
require_once 'compartidoCREO/views/encabezado.php';
$v = $data['vehiculo'];
?>

<div class="page-header">
    <h1>🚘 Ficha Técnica — <?= htmlspecialchars($v['patente'] ?? 'Vehículo') ?></h1>
    <a href="index.php?page=vehiculos" class="btn btn-ghost">&larr; Volver al listado</a>
</div>

<?php if (!empty($_GET['ot_error'])): ?>
    <div class="toast-error" style="position:static;display:flex;margin-bottom:16px;">⚠️ <?= htmlspecialchars($_GET['ot_error']) ?></div>
<?php endif; ?>

<?php if (!$v): ?>
    <div class="panel"><div class="panel-body" style="padding:30px;text-align:center;color:var(--text-muted);">
        Vehículo no encontrado.
    </div></div>
<?php else: ?>

<div class="panel" style="margin-bottom:20px;">
    <div class="panel-header"><div class="panel-title">Datos del vehículo</div></div>
    <div class="panel-body" style="padding:20px;">
        <div class="spec-grid">
            <div class="spec-item"><div class="spec-label">Patente</div><div class="spec-value"><?= htmlspecialchars($v['patente'] ?? '-') ?></div></div>
            <div class="spec-item"><div class="spec-label">Modelo</div><div class="spec-value"><?= htmlspecialchars($v['modelo_vehiculo'] ?? '-') ?></div></div>
            <div class="spec-item"><div class="spec-label">Tipo</div><div class="spec-value"><?= htmlspecialchars($v['tipo_vehiculo'] ?? '-') ?></div></div>
            <div class="spec-item"><div class="spec-label">Año</div><div class="spec-value"><?= htmlspecialchars($v['anio'] ?? '-') ?></div></div>
            <div class="spec-item"><div class="spec-label">N° Chasis</div><div class="spec-value"><?= htmlspecialchars($v['numero_chasis'] ?? '-') ?></div></div>
            <div class="spec-item"><div class="spec-label">N° Motor</div><div class="spec-value"><?= htmlspecialchars($v['numero_motor'] ?? '-') ?></div></div>
            <div class="spec-item"><div class="spec-label">Fecha de ingreso</div><div class="spec-value"><?= isset($v['fecha_ingreso']) ? substr($v['fecha_ingreso'],0,10) : '-' ?></div></div>
            <div class="spec-item"><div class="spec-label">Estado en Taller</div><div class="spec-value"><?= htmlspecialchars($v['estado_taller'] ?? '-') ?></div></div>
            <div class="spec-item"><div class="spec-label">Kilometraje</div><div class="spec-value"><?= number_format($v['kilometraje'] ?? 0, 0, ',', '.') ?> km</div></div>
        </div>
    </div>
</div>

<a href="index.php?page=vehiculos&action=edit&id=<?= $v['id'] ?>" class="btn btn-primary">Editar vehículo</a>

<!-- Panel de Órdenes de Trabajo: qué mecánico trabajó en este vehículo y qué repuestos usó -->
<div class="panel" style="margin-top:20px;">
    <div class="panel-header">
        <div class="panel-title">🔧 Órdenes de Trabajo</div>
        <button type="button" class="btn btn-primary" onclick="abrirModal('modal-nueva-orden')">＋ Nueva Orden</button>
    </div>
    <div class="panel-body">
        <?php if (empty($data['ordenes'])): ?>
            <div style="padding:30px;text-align:center;color:var(--text-muted);">Todavía no hay órdenes de trabajo cargadas para este vehículo.</div>
        <?php else: ?>
            <table>
                <thead>
                    <tr><th>Fecha</th><th>Mecánico</th><th>Descripción</th><th>Repuestos usados</th><th>Estado</th><th>Acciones</th></tr>
                </thead>
                <tbody>
                <?php foreach ($data['ordenes'] as $orden): ?>
                    <tr>
                        <td style="color:var(--text-muted);"><?= date('d/m/Y', strtotime($orden['fecha'])) ?></td>
                        <td>
                            <?php
                                // Si no se asignó mecánico, muestro un guión
                                $nombreMec = trim(($orden['mecanico_nombre'] ?? '') . ' ' . ($orden['mecanico_apellido'] ?? ''));
                                echo htmlspecialchars($nombreMec !== '' ? $nombreMec : '—');
                            ?>
                        </td>
                        <td style="color:var(--text-muted);"><?= htmlspecialchars($orden['descripcion'] ?: '—') ?></td>
                        <td>
                            <?php if (empty($orden['items'])): ?>
                                <span style="color:var(--text-dim);">Sin repuestos</span>
                            <?php else: ?>
                                <?php foreach ($orden['items'] as $it): ?>
                                    <div style="font-size:12px;color:var(--text-muted);"><?= $it['cantidad'] ?>x <?= htmlspecialchars($it['nombre']) ?></div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </td>
                        <td>
                            <span class="badge-status <?= $orden['estado'] === 'Cerrada' ? 'disponible' : 'reparacion' ?>"><?= htmlspecialchars($orden['estado']) ?></span>
                        </td>
                        <td>
                            <?php if ($orden['estado'] !== 'Cerrada'): ?>
                            <a href="index.php?page=ordenes&action=cerrar&id=<?= $orden['id'] ?>&vehiculo_id=<?= $v['id'] ?>"
                               class="btn btn-ghost" style="font-size:11px;padding:4px 10px;"
                               onclick="return confirmarAccion(this.href, '¿Marcar esta orden como cerrada?')">Cerrar</a>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>

<!-- Modal: nueva orden de trabajo (mecánico + repuestos usados, con cantidad de cada uno) -->
<div id="modal-nueva-orden" class="quick-modal-overlay">
    <div class="quick-modal-box">
        <div class="quick-modal-header">
            <h3>＋ Nueva Orden de Trabajo</h3>
            <button type="button" class="quick-modal-close" onclick="cerrarModal('modal-nueva-orden')">&times;</button>
        </div>
        <div class="quick-modal-body">
            <form method="POST" action="index.php?page=ordenes&action=create">
                <!-- El vehículo ya se sabe: viene fijo desde esta ficha técnica -->
                <input type="hidden" name="vehiculo_id" value="<?= $v['id'] ?>">

                <div class="form-group">
                    <label>Mecánico asignado</label>
                    <select name="mecanico_id">
                        <option value="">— Sin asignar —</option>
                        <?php foreach ($data['mecanicos'] as $mec): ?>
                        <option value="<?= $mec['id'] ?>">
                            <?= htmlspecialchars(trim(($mec['nombre'] ?? '') . ' ' . ($mec['apellido'] ?? ''))) ?>
                            <?= $mec['rango_trabajo'] ? ' — ' . htmlspecialchars($mec['rango_trabajo']) : '' ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label>Descripción del trabajo</label>
                    <input type="text" name="descripcion" placeholder="Ej: cambio de pastillas de freno delanteras">
                </div>

                <div class="form-group">
                    <label>Repuestos usados</label>
                    <!-- Acá se arman las filas repuesto + cantidad de forma dinámica con JS -->
                    <div id="filas-repuestos"></div>
                    <button type="button" class="btn btn-ghost" style="margin-top:8px;" onclick="agregarFilaRepuesto()">＋ Agregar repuesto</button>
                </div>

                <button type="submit" class="btn btn-primary" style="margin-top:10px;">✅ Guardar Orden</button>
            </form>
        </div>
    </div>
</div>

<script>
// Lista de repuestos disponibles (con su stock actual) para armar el selector de cada fila
const repuestosDisponibles = <?= json_encode(array_map(fn($r) => ['id' => $r['id'], 'nombre' => $r['nombre'], 'stock' => (int)$r['stock']], $data['repuestos'])) ?>;

// Agrega una fila nueva (repuesto + cantidad) al formulario de la orden de trabajo
function agregarFilaRepuesto() {
    const cont = document.getElementById('filas-repuestos');
    const fila = document.createElement('div');
    fila.className = 'form-row';
    fila.style.marginTop = '8px';

    let opciones = '<option value="">— Elegir repuesto —</option>';
    repuestosDisponibles.forEach(r => {
        opciones += `<option value="${r.id}" data-stock="${r.stock}">${r.nombre} (stock: ${r.stock})</option>`;
    });

    fila.innerHTML = `
        <div class="form-group" style="margin-bottom:0;">
            <select name="repuesto_id[]">${opciones}</select>
        </div>
        <div class="form-group" style="margin-bottom:0; display:flex; gap:6px;">
            <input type="number" name="cantidad_usada[]" value="1" min="1" style="flex:1;">
            <button type="button" class="btn btn-ghost" onclick="this.closest('.form-row').remove()">🗑️</button>
        </div>`;
    cont.appendChild(fila);
}

// Arranca con una fila ya cargada, para no obligar a apretar "Agregar repuesto" la primera vez
agregarFilaRepuesto();

// Antes de mandar el formulario, aviso en el momento si alguna cantidad pedida supera el stock actual
// (la validación real y definitiva es del lado del servidor, esto es solo para avisar más rápido)
document.querySelector('#modal-nueva-orden form').addEventListener('submit', function (e) {
    const filas = document.querySelectorAll('#filas-repuestos .form-row');
    for (const fila of filas) {
        const select   = fila.querySelector('select[name="repuesto_id[]"]');
        const cantidad = parseInt(fila.querySelector('input[name="cantidad_usada[]"]').value || '0', 10);
        const opcion   = select.options[select.selectedIndex];
        if (!select.value) continue; // fila vacía, se ignora

        const stockDisponible = parseInt(opcion.dataset.stock || '0', 10);
        if (cantidad > stockDisponible) {
            e.preventDefault();
            alert(`No hay stock suficiente de "${opcion.textContent.split(' (stock:')[0]}" (pediste ${cantidad}, quedan ${stockDisponible}).`);
            return;
        }
    }
});
</script>

<?php endif; ?>
<?php require_once 'compartidoCREO/views/pie_pagina.php'; ?>

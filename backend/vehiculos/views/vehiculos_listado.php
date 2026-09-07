<?php require_once 'compartidoCREO/views/encabezado.php'; ?>

<div class="page-header">
    <h1>🚘 Vehículos</h1>
    <button type="button" class="btn btn-primary" onclick="abrirVehiculo()">＋ Nuevo Vehículo</button>
</div>

<div class="panel">
    <div class="panel-body">
        <table>
            <thead>
                <tr><th>#</th><th>Patente</th><th>N° Chasis</th><th>N° Motor</th><th>Modelo</th><th>Tipo</th><th>Año</th><th>Km</th><th>Estado Taller</th><th>Fecha Ingreso</th><th>Acciones</th></tr>
            </thead>
            <tbody>
            <?php if(empty($data['vehiculos'])): ?>
                <tr><td colspan="11" style="text-align:center;color:var(--text-muted);padding:30px;">No hay vehículos registrados</td></tr>
            <?php else: foreach($data['vehiculos'] as $v): ?>
                <tr>
                    <td style="color:var(--text-muted);"><?= $v['id'] ?></td>
                    <td><strong><?= htmlspecialchars($v['patente'] ?? '-') ?></strong></td>
                    <td style="color:var(--text-muted);"><?= htmlspecialchars($v['numero_chasis'] ?? '-') ?></td>
                    <td style="color:var(--text-muted);"><?= htmlspecialchars($v['numero_motor'] ?? '-') ?></td>
                    <td><?= htmlspecialchars($v['modelo_vehiculo'] ?? '-') ?></td>
                    <td><span class="badge-status disponible"><?= htmlspecialchars($v['tipo_vehiculo'] ?? '-') ?></span></td>
                    <td><?= $v['anio'] ?? '-' ?></td>
                    <td style="color:var(--text-muted);"><?= number_format($v['kilometraje'] ?? 0, 0, ',', '.') ?> km</td>
                    <td>
                        <?php
                            $estadoBadge = ['En Diagnóstico'=>'reparacion','En Reparación'=>'reparacion','Esperando Repuestos'=>'vendido','Listo para Entrega'=>'disponible','Entregado'=>'disponible'];
                            $et = $v['estado_taller'] ?? 'En Diagnóstico';
                        ?>
                        <span class="badge-status <?= $estadoBadge[$et] ?? 'reparacion' ?>"><?= htmlspecialchars($et) ?></span>
                    </td>
                    <td style="color:var(--text-muted);"><?= isset($v['fecha_ingreso']) ? substr($v['fecha_ingreso'],0,10) : '-' ?></td>
                    <td>
                        <div class="action-icons">
                            <a href="index.php?page=vehiculos&action=verFicha&id=<?= $v['id'] ?>" class="action-edit" title="Ver ficha técnica"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg></a>
                            <a href="#" class="action-edit"
                               onclick="return abrirVehiculo({
                                    id: <?= $v['id'] ?>,
                                    patente: <?= htmlspecialchars(json_encode($v['patente']), ENT_QUOTES, 'UTF-8') ?>,
                                    numero_chasis: <?= htmlspecialchars(json_encode($v['numero_chasis']), ENT_QUOTES, 'UTF-8') ?>,
                                    numero_motor: <?= htmlspecialchars(json_encode($v['numero_motor']), ENT_QUOTES, 'UTF-8') ?>,
                                    modelo_vehiculo_id: <?= (int)($v['modelo_vehiculo_id'] ?? 0) ?>,
                                    tipo_vehiculo_id: <?= (int)($v['tipo_vehiculo_id'] ?? 0) ?>,
                                    estado_taller: <?= htmlspecialchars(json_encode($v['estado_taller'] ?? 'En Diagnóstico'), ENT_QUOTES, 'UTF-8') ?>,
                                    anio: <?= (int)($v['anio'] ?? 0) ?>,
                                    kilometraje: <?= (int)($v['kilometraje'] ?? 0) ?>,
                                    fecha_ingreso: <?= htmlspecialchars(json_encode(substr($v['fecha_ingreso'] ?? '', 0, 10)), ENT_QUOTES, 'UTF-8') ?>
                               })"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg></a>
                            <a href="index.php?page=vehiculos&action=delete&id=<?= $v['id'] ?>" class="action-delete"
                               onclick="return confirmarAccion(this.href, '¿Eliminar este vehículo?')"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg></a>
                        </div>
                    </td>
                </tr>
            <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>
<!-- Modal: alta y edición de vehículo (un solo modal para las dos cosas) -->
<div id="modal-nuevo-vehiculo" class="quick-modal-overlay">
    <div class="quick-modal-box">
        <div class="quick-modal-header">
            <h3 id="veh-titulo">＋ Nuevo Vehículo</h3>
            <button type="button" class="quick-modal-close" onclick="cerrarModal('modal-nuevo-vehiculo')">&times;</button>
        </div>
        <div class="quick-modal-body">
            <form method="POST" id="form-vehiculo" action="index.php?page=vehiculos&action=create" onsubmit="return enviarModalForm(this, event, 'toast-error-vehiculo')">
                <input type="hidden" name="ajax" value="1">
                <input type="hidden" name="fecha_ingreso" id="veh-fecha">
                <div id="toast-error-vehiculo" class="toast-error"></div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Patente</label>
                        <input type="text" name="patente" id="veh-patente" placeholder="ABC123">
                    </div>
                    <div class="form-group">
                        <label>Año</label>
                        <input type="number" name="anio" id="veh-anio" placeholder="2024" min="1950" max="2100">
                        <input type="hidden" name="cantidad_vehiculo" value="1">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Número de Chasis</label>
                        <input type="text" name="numero_chasis" id="veh-chasis" placeholder="Nro. chasis">
                    </div>
                    <div class="form-group">
                        <label>Número de Motor</label>
                        <input type="text" name="numero_motor" id="veh-motor" placeholder="Nro. motor">
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
                <div class="form-row">
                    <div class="form-group">
                        <label>Estado en Taller</label>
                        <select name="estado_taller" id="veh-estado">
                            <option value="En Diagnóstico">En Diagnóstico</option>
                            <option value="En Reparación">En Reparación</option>
                            <option value="Esperando Repuestos">Esperando Repuestos</option>
                            <option value="Listo para Entrega">Listo para Entrega</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Kilometraje</label>
                        <input type="number" name="kilometraje" id="veh-km" value="0" min="0">
                    </div>
                </div>
                <button type="submit" class="btn btn-primary" id="veh-submit">✅ Guardar</button>
            </form>
        </div>
    </div>
</div>

<script>
// Opciones para los combos de Modelo y Tipo (vienen del backend, no invento nada acá)
const opcionesModelos = <?= json_encode(array_map(fn($m) => ['id' => $m['id'], 'label' => $m['modelo_vehiculo']], $data['modelos'])) ?>;
const opcionesTipos   = <?= json_encode(array_map(fn($t) => ['id' => $t['id'], 'label' => $t['tipo_vehiculo']], $data['tipos'])) ?>;
crearCombo('combo-modelo', opcionesModelos);
crearCombo('combo-tipo', opcionesTipos);

// Un solo modal para alta y edición de vehículo: sin datos = alta; con datos = edición.
function abrirVehiculo(v) {
    const form   = document.getElementById('form-vehiculo');
    const titulo = document.getElementById('veh-titulo');
    const submit = document.getElementById('veh-submit');

    if (v && v.id) {
        form.action = 'index.php?page=vehiculos&action=edit&id=' + v.id;
        titulo.textContent = '✏️ Editar Vehículo';
        submit.textContent = '💾 Actualizar';
        document.getElementById('veh-patente').value = v.patente || '';
        document.getElementById('veh-anio').value    = v.anio || '';
        document.getElementById('veh-chasis').value  = v.numero_chasis || '';
        document.getElementById('veh-motor').value   = v.numero_motor || '';
        document.getElementById('combo-modelo')._setValor(v.modelo_vehiculo_id);
        document.getElementById('combo-tipo')._setValor(v.tipo_vehiculo_id);
        document.getElementById('veh-estado').value  = v.estado_taller || 'En Diagnóstico';
        document.getElementById('veh-km').value      = v.kilometraje || 0;
        document.getElementById('veh-fecha').value   = v.fecha_ingreso || '';
    } else {
        form.reset();
        form.action = 'index.php?page=vehiculos&action=create';
        titulo.textContent = '＋ Nuevo Vehículo';
        submit.textContent = '✅ Guardar';
        document.getElementById('veh-anio').value = '';
        document.getElementById('combo-modelo')._limpiar();
        document.getElementById('combo-tipo')._limpiar();
        document.getElementById('veh-fecha').value = new Date().toISOString().slice(0,10);
    }
    abrirModal('modal-nuevo-vehiculo');
    return false;
}
</script>

<?php require_once 'compartidoCREO/views/pie_pagina.php'; ?>

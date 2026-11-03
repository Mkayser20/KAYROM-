<?php
require_once 'compartidoCREO/views/encabezado.php';
$v = $data['vehiculo'];
?>

<div class="page-header">
    <h1><?= icono('vehiculo') ?> Ficha Técnica — <?= htmlspecialchars($v['patente'] ?? 'Vehículo') ?></h1>
    <a href="index.php?page=vehiculos" class="btn btn-ghost">&larr; Volver al listado</a>
</div>

<?php if (!empty($_GET['ot_error'])): ?>
    <div class="toast-error" style="position:static;display:flex;margin-bottom:16px;"><?= htmlspecialchars($_GET['ot_error']) ?></div>
<?php endif; ?>

<?php if (!empty($_GET['ot_aviso'])): ?>
    <div style="position:static;display:flex;align-items:center;gap:10px;background:var(--bg-card);border:1px solid var(--border);border-left:3px solid var(--accent-blue);color:var(--text-primary);padding:13px 20px;border-radius:10px;margin-bottom:16px;font-size:13.5px;">
        <?= htmlspecialchars(str_replace(' | ', ' — ', $_GET['ot_aviso'])) ?>
    </div>
<?php endif; ?>

<?php if (!$v): ?>
    <div class="panel"><div class="panel-body" style="padding:30px;text-align:center;color:var(--text-muted);">
        Vehículo no encontrado.
    </div></div>
<?php else: ?>

<div class="panel" style="margin-bottom:20px;">
    <div class="panel-header"><div class="panel-title"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align:-3px;margin-right:4px;"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg> Cliente</div></div>
    <div class="panel-body" style="padding:20px;">
        <?php if (!empty($v['cliente_nombre'])): ?>
        <div class="spec-grid">
            <div class="spec-item"><div class="spec-label">Nombre</div><div class="spec-value"><?= htmlspecialchars($v['cliente_nombre'] . ' ' . $v['cliente_apellido']) ?></div></div>
            <div class="spec-item"><div class="spec-label">DNI</div><div class="spec-value"><?= htmlspecialchars($v['cliente_dni'] ?? '-') ?></div></div>
            <div class="spec-item"><div class="spec-label">Teléfono</div><div class="spec-value"><?= htmlspecialchars($v['cliente_telefono'] ?: '—') ?></div></div>
        </div>
        <a href="index.php?page=clientes&action=verFicha&id=<?= $v['cliente_id'] ?>" class="btn btn-ghost" style="margin-top:14px;font-size:12px;">Ver ficha del cliente &rsaquo;</a>
        <?php else: ?>
            <div style="color:var(--text-muted);">Este vehículo no tiene un cliente asociado.</div>
        <?php endif; ?>
    </div>
</div>

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
<?php $totalGeneralOrdenes = array_sum(array_map(static fn($orden) => (float)$orden['total_repuestos'], $data['ordenes'])); ?>
<div class="panel" style="margin-top:20px;">
    <div class="panel-header">
        <div class="panel-title"><?= icono('llave', 16) ?> Órdenes de Trabajo</div>
        <div style="display:flex;align-items:center;gap:12px;flex-wrap:wrap;">
            <strong>Total general: <?= '$' . number_format($totalGeneralOrdenes, 2, ',', '.') ?></strong>
            <button type="button" class="btn btn-ghost" onclick="imprimirResumenOrdenes()" <?= empty($data['ordenes']) ? 'disabled' : '' ?>>Imprimir PDF</button>
            <button type="button" class="btn btn-primary" onclick="abrirModal('modal-nueva-orden')">＋ Nueva Orden</button>
        </div>
    </div>
    <div class="panel-body">
        <?php if (empty($data['ordenes'])): ?>
            <div style="padding:30px;text-align:center;color:var(--text-muted);">Todavía no hay órdenes de trabajo cargadas para este vehículo.</div>
        <?php else: ?>
            <table>
                <thead>
                    <tr><th>Fecha</th><th>Mecánico</th><th>Descripción</th><th>Repuestos usados</th><th>Total repuestos</th><th>Estado</th><th>Acciones</th></tr>
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
                                    <div style="font-size:12px;color:var(--text-muted);"><?= (int)$it['cantidad'] ?>x <?= htmlspecialchars($it['nombre']) ?></div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </td>
                        <td style="white-space:nowrap;"><?= '$' . number_format($orden['total_repuestos'], 2, ',', '.') ?></td>
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
        <section id="resumen-ordenes-imprimible" class="orden-imprimible" aria-hidden="true">
            <h1>Resumen de Órdenes de Trabajo</h1>
            <p>Vehículo: <?= htmlspecialchars($v['patente'] ?? '-') ?> — <?= htmlspecialchars($v['modelo_vehiculo'] ?? '-') ?></p>
            <?php foreach ($data['ordenes'] as $orden): ?>
                <section class="orden-resumen">
                    <h2>Orden de Trabajo #<?= (int)$orden['id'] ?></h2>
                    <p>Fecha: <?= date('d/m/Y', strtotime($orden['fecha'])) ?></p>
                    <p>Mecánico: <?= htmlspecialchars(trim(($orden['mecanico_nombre'] ?? '') . ' ' . ($orden['mecanico_apellido'] ?? '')) ?: '—') ?></p>
                    <p>Descripción: <?= htmlspecialchars($orden['descripcion'] ?: '—') ?></p>
                    <table>
                        <thead>
                            <tr><th>Repuesto</th><th>Cantidad</th><th>Precio unitario</th><th>Subtotal</th></tr>
                        </thead>
                        <tbody>
                            <?php foreach ($orden['items'] as $it): ?>
                                <tr>
                                    <td><?= htmlspecialchars($it['nombre']) ?></td>
                                    <td><?= (int)$it['cantidad'] ?></td>
                                    <td><?= '$' . number_format((float)$it['precio'], 2, ',', '.') ?></td>
                                    <td><?= '$' . number_format((float)$it['precio'] * (int)$it['cantidad'], 2, ',', '.') ?></td>
                                </tr>
                            <?php endforeach; ?>
                            <?php if (empty($orden['items'])): ?>
                                <tr><td colspan="4">Sin repuestos usados</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                    <p class="total-orden">Total de la orden: <?= '$' . number_format($orden['total_repuestos'], 2, ',', '.') ?></p>
                </section>
            <?php endforeach; ?>
            <h2 class="total-general">Total general de repuestos: <?= '$' . number_format($totalGeneralOrdenes, 2, ',', '.') ?></h2>
        </section>
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
                    <div style="margin-top:12px;font-weight:600;">Total de repuestos: <span id="total-orden-repuestos">$ 0,00</span></div>
                </div>

                <button type="submit" class="btn btn-primary" style="margin-top:10px;">Guardar Orden</button>
            </form>
        </div>
    </div>
</div>

<script>
// Lista de repuestos disponibles (con su stock actual) para armar el selector de cada fila
const repuestosDisponibles = <?= json_encode(array_map(fn($r) => ['id' => $r['id'], 'nombre' => $r['nombre'], 'stock' => (int)$r['stock'], 'precio' => (float)$r['precio']], $data['repuestos']), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>;
const formatoMoneda = new Intl.NumberFormat('es-AR', { style: 'currency', currency: 'ARS' });

function actualizarTotalOrden() {
    let total = 0;
    document.querySelectorAll('#filas-repuestos .form-row').forEach(fila => {
        const select = fila.querySelector('select[name="repuesto_id[]"]');
        const cantidad = Math.max(1, parseInt(fila.querySelector('input[name="cantidad_usada[]"]').value || '1', 10) || 1);
        const repuesto = repuestosDisponibles.find(r => String(r.id) === select.value);
        if (repuesto) total += Math.round(repuesto.precio * 100) * cantidad;
    });
    document.getElementById('total-orden-repuestos').textContent = formatoMoneda.format(total / 100);
}

// Agrega una fila nueva (repuesto + cantidad) al formulario de la orden de trabajo
function agregarFilaRepuesto() {
    const cont = document.getElementById('filas-repuestos');
    const fila = document.createElement('div');
    fila.className = 'form-row';
    fila.style.marginTop = '8px';

    const select = document.createElement('select');
    select.name = 'repuesto_id[]';
    const opcionVacia = document.createElement('option');
    opcionVacia.value = '';
    opcionVacia.textContent = '— Elegir repuesto —';
    select.appendChild(opcionVacia);
    repuestosDisponibles.forEach(r => {
        const opcion = document.createElement('option');
        opcion.value = r.id;
        opcion.dataset.stock = r.stock;
        opcion.textContent = `${r.nombre} (stock: ${r.stock})`;
        select.appendChild(opcion);
    });

    const grupoRepuesto = document.createElement('div');
    grupoRepuesto.className = 'form-group';
    grupoRepuesto.style.marginBottom = '0';
    grupoRepuesto.appendChild(select);

    const grupoCantidad = document.createElement('div');
    grupoCantidad.className = 'form-group';
    grupoCantidad.style.cssText = 'margin-bottom:0; display:flex; gap:6px;';
    const cantidad = document.createElement('input');
    cantidad.type = 'number';
    cantidad.name = 'cantidad_usada[]';
    cantidad.value = '1';
    cantidad.min = '1';
    cantidad.style.flex = '1';
    const eliminar = document.createElement('button');
    eliminar.type = 'button';
    eliminar.className = 'btn btn-ghost';
    eliminar.innerHTML = <?= json_encode(icono('eliminar', 14), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>;
    eliminar.addEventListener('click', () => fila.remove());
    grupoCantidad.append(cantidad, eliminar);
    fila.append(grupoRepuesto, grupoCantidad);
    cont.appendChild(fila);
}

// Arranca con una fila ya cargada, para no obligar a apretar "Agregar repuesto" la primera vez
agregarFilaRepuesto();
document.getElementById('filas-repuestos').addEventListener('input', actualizarTotalOrden);
document.getElementById('filas-repuestos').addEventListener('change', actualizarTotalOrden);
document.getElementById('filas-repuestos').addEventListener('click', event => {
    if (event.target.closest('button')) actualizarTotalOrden();
});

// Antes de mandar, si alguna cantidad supera el stock actual, aviso que se va a generar
// un pedido automático al proveedor por la diferencia — pero NO bloqueo el envío.
document.querySelector('#modal-nueva-orden form').addEventListener('submit', function (e) {
    const filas = document.querySelectorAll('#filas-repuestos .form-row');
    const faltantes = [];
    for (const fila of filas) {
        const select   = fila.querySelector('select[name="repuesto_id[]"]');
        const cantidad = parseInt(fila.querySelector('input[name="cantidad_usada[]"]').value || '0', 10);
        const opcion   = select.options[select.selectedIndex];
        if (!select.value) continue; // fila vacía, se ignora

        const stockDisponible = parseInt(opcion.dataset.stock || '0', 10);
        if (cantidad > stockDisponible) {
            faltantes.push(`${opcion.textContent.split(' (stock:')[0]} (faltan ${cantidad - stockDisponible})`);
        }
    }
    if (faltantes.length > 0) {
        // freno el envío, pregunto con el diálogo moderno y recién si confirma mando el formulario
        e.preventDefault();
        const formulario = this;
        preguntar(
            'Falta stock de algunos repuestos',
            `${faltantes.join(', ')}. Se va a generar un pedido automático al proveedor por la diferencia.`,
            { confirmar: 'Sí, continuar' }
        ).then(function (ok) {
            if (ok) { formulario.submit(); }
        });
    }
});

function imprimirResumenOrdenes() {
    const recibo = document.getElementById('resumen-ordenes-imprimible');
    const ventana = window.open('', '_blank');
    if (!ventana) {
        alert('No se pudo abrir la ventana de impresión. Permití las ventanas emergentes para este sitio e intentá nuevamente.');
        return;
    }

    ventana.document.open();
    ventana.document.write(`<!doctype html>
        <html lang="es">
        <head>
            <meta charset="UTF-8">
            <title>Resumen de Órdenes de Trabajo</title>
            <style>
                @page { size: auto; margin: 0; }
                * { box-sizing: border-box; }
                body { margin: 0; padding: 14mm 12mm; color: #111; background: #fff; font: 14px Arial, sans-serif; }
                h1 { margin: 0 0 20px; font-size: 22px; }
                h2 { margin: 0 0 12px; font-size: 17px; }
                p { margin: 8px 0; }
                table { width: 100%; margin-top: 24px; border-collapse: collapse; }
                th, td { padding: 9px; border: 1px solid #999; text-align: left; }
                th { background: #eee; }
                .orden-resumen { margin-top: 28px; break-inside: avoid; page-break-inside: avoid; }
                .total-orden { margin-top: 12px; text-align: right; font-weight: bold; }
                .total-general { margin-top: 32px; padding-top: 14px; border-top: 2px solid #222; text-align: right; }
                @media print { body { -webkit-print-color-adjust: exact; print-color-adjust: exact; } }
            </style>
        </head>
        <body>${recibo.innerHTML}</body>
        </html>`);
    ventana.document.close();
    ventana.focus();
    ventana.setTimeout(() => {
        ventana.print();
        ventana.close();
    }, 250);
}
</script>

<style>
.orden-imprimible { display: none; }
</style>

<?php endif; ?>
<?php require_once 'compartidoCREO/views/pie_pagina.php'; ?>

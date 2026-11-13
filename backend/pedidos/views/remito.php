<?php require_once 'compartidoCREO/views/encabezado.php'; ?>
<?php
// ---------- Preparo los datos del remito ----------
$p     = $data['pedido'];
$items = $data['items'];
$total = $data['total'];

// N° de remito armado a partir del ID del pedido: punto de venta 0001 + número de 8 dígitos
// Ej: pedido 25  ->  0001-00000025  (no hace falta guardarlo en la base, siempre da lo mismo)
$numeroRemito = '0001-' . str_pad($p['id'], 8, '0', STR_PAD_LEFT);

// CUIT con guiones (30712345678 -> 30-71234567-8). Si no tiene 11 dígitos lo muestro como está
$cuit = (string)($p['cuit'] ?? '');
if (strlen($cuit) === 11) {
    $cuit = substr($cuit, 0, 2) . '-' . substr($cuit, 2, 8) . '-' . substr($cuit, 10, 1);
}

// Texto del estado (en la base se guarda "Entregado", pero en pantalla decimos "Recibido")
$estadoTexto = ($p['estado_pedido'] === 'Entregado') ? 'Recibido' : ($p['estado_pedido'] ?: 'Pendiente');

// Formato de plata argentino: $ 1.500.000,00
if (!function_exists('plata')) {
    function plata($n) { return '$ ' . number_format((float)$n, 2, ',', '.'); }
}

// Fecha dd/mm/aaaa (o un guion si no hay)
if (!function_exists('fechaRemito')) {
    function fechaRemito($f) { return $f ? date('d/m/Y', strtotime($f)) : '—'; }
}
?>

<div class="page-header">
    <h1><?= icono('remito') ?> Remito del pedido #<?= (int)$p['id'] ?></h1>
    <div style="display:flex;gap:8px;flex-wrap:wrap;align-items:center;">
        <!-- Check para sacar los precios antes de imprimir (por si el remito va sin valores) -->
        <label class="remito-check">
            <input type="checkbox" id="check-precios" checked onchange="mostrarPrecios(this.checked)">
            Mostrar precios
        </label>
        <a href="index.php?page=pedidos" class="btn btn-ghost"><?= icono('volver', 14) ?> Volver</a>
        <button type="button" class="btn btn-primary" onclick="imprimirRemito()"><?= icono('imprimir', 14) ?> Imprimir</button>
    </div>
</div>

<!-- La "hoja" del remito. Todo lo que está acá adentro es lo que se imprime -->
<div class="remito-contenedor">
<div id="remito-hoja" class="remito-hoja">

    <!-- Cabecera: datos del taller a la izquierda, letra "R" al medio, número y fecha a la derecha -->
    <div class="remito-cabecera">
        <div class="remito-taller">
            <img src="public/img/logo-emblema.png" alt="KAYROM" class="remito-logo">
            <div>
                <div class="remito-taller-nombre">KAY<span>ROM</span></div>
                <div class="remito-chico">Sistema de inventario — Taller mecánico</div>
            </div>
        </div>
        <div class="remito-letra">R<small>Remito</small></div>
        <div class="remito-numero">
            <div class="remito-titulo">REMITO</div>
            <div><b>N°:</b> <?= $numeroRemito ?></div>
            <div><b>Fecha:</b> <?= fechaRemito($p['fecha_pedidos']) ?></div>
            <div class="remito-chico">Documento no válido como factura</div>
        </div>
    </div>

    <!-- Datos del proveedor y del pedido -->
    <div class="remito-datos">
        <div>
            <div class="remito-subtitulo">Proveedor</div>
            <div><b><?= htmlspecialchars($p['nombre_proveedor'] ?? 'Sin especificar') ?></b></div>
            <div>CUIT: <?= $cuit !== '' ? htmlspecialchars($cuit) : '—' ?></div>
            <div>Domicilio: <?= htmlspecialchars($p['domicilio_proveedor'] ?? '—') ?></div>
            <div>Tel.: <?= htmlspecialchars($p['telefono'] ?? '—') ?></div>
            <div>Email: <?= htmlspecialchars($p['email'] ?? '—') ?></div>
        </div>
        <div>
            <div class="remito-subtitulo">Pedido</div>
            <div>Pedido N° único: <b>#<?= htmlspecialchars($p['numero_unico'] ?? '') ?></b></div>
            <div>Estado: <b><?= htmlspecialchars($estadoTexto) ?></b></div>
            <div>Responsable: <?= htmlspecialchars($p['responsable_pedido'] ?? '—') ?></div>
            <div>Fecha de recepción: <?= fechaRemito($p['fecha_recepcion'] ?? null) ?></div>
            <?php if (!empty($p['patente'])): ?>
            <div>Vehículo: <?= htmlspecialchars($p['patente']) ?></div>
            <?php endif; ?>
            <?php if (!empty($p['orden_trabajo_id'])): ?>
            <div>Orden de trabajo: #<?= (int)$p['orden_trabajo_id'] ?></div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Renglones del remito -->
    <div class="remito-tabla-scroll">
    <table class="remito-tabla">
        <thead>
            <tr>
                <th style="width:70px;">Cant.</th>
                <th>Descripción</th>
                <th>SKU</th>
                <th class="col-precio" style="text-align:right;">P. unitario</th>
                <th class="col-precio" style="text-align:right;">Subtotal</th>
            </tr>
        </thead>
        <tbody>
        <?php if (empty($items)): ?>
            <!-- Pedido viejo (de texto libre, sin renglones): muestro el detalle tal cual se cargó -->
            <tr>
                <td style="text-align:center;"><?= (int)($p['cantidad'] ?? 0) ?></td>
                <td colspan="2"><?= htmlspecialchars($p['detalle_pedido'] ?? '—') ?></td>
                <td class="col-precio" style="text-align:right;">—</td>
                <td class="col-precio" style="text-align:right;">—</td>
            </tr>
        <?php else: foreach ($items as $it): ?>
            <tr>
                <td style="text-align:center;"><?= (int)$it['cantidad'] ?></td>
                <td>
                    <?= htmlspecialchars($it['nombre']) ?>
                    <?php if (!empty($it['categoria'])): ?>
                    <span class="remito-chico">(<?= htmlspecialchars($it['categoria']) ?>)</span>
                    <?php endif; ?>
                </td>
                <td><?= htmlspecialchars($it['sku'] ?: '—') ?></td>
                <td class="col-precio" style="text-align:right;"><?= plata($it['precio_unitario']) ?></td>
                <td class="col-precio" style="text-align:right;"><?= plata($it['subtotal']) ?></td>
            </tr>
        <?php endforeach; endif; ?>
        </tbody>
        <tfoot>
            <tr>
                <td style="text-align:center;"><b><?= (int)($p['cantidad'] ?? 0) ?></b></td>
                <td colspan="2"><b>Total de unidades</b></td>
                <td class="col-precio" style="text-align:right;"><b>TOTAL</b></td>
                <td class="col-precio" style="text-align:right;"><b><?= plata($total) ?></b></td>
            </tr>
        </tfoot>
    </table>
    </div>

    <!-- Firmas: el que entrega (proveedor) y el que recibe (taller) -->
    <div class="remito-firmas">
        <div>
            <div class="remito-linea"></div>
            Entregó (proveedor)<br><span class="remito-chico">Firma y aclaración</span>
        </div>
        <div>
            <div class="remito-linea"></div>
            Recibió conforme (taller)<br><span class="remito-chico">Firma, aclaración y fecha</span>
        </div>
    </div>

    <div class="remito-pie remito-chico">
        Remito generado por SISTEMA-KAYROM el <?= date('d/m/Y H:i') ?>
    </div>
</div>
</div>

<!-- Estilos del remito. Tienen id para que la función de imprimir los copie a la ventana nueva -->
<style id="estilos-remito">
.remito-contenedor { display: flex; justify-content: center; }
.remito-hoja {
    width: 100%; max-width: 820px; padding: 32px;
    background: #fff; color: #111; border-radius: 8px;
    font: 13px Arial, Helvetica, sans-serif; line-height: 1.5;
}
.remito-hoja b { color: #111; }
.remito-chico { font-size: 11px; color: #555; }
.remito-cabecera {
    display: grid; grid-template-columns: 1fr auto 1fr; gap: 16px; align-items: center;
    padding-bottom: 16px; border-bottom: 2px solid #111;
}
.remito-taller { display: flex; align-items: center; gap: 10px; }
.remito-logo { width: 90px; height: auto; }
.remito-taller-nombre { font-size: 24px; font-weight: 800; letter-spacing: 1px; color: #111; }
.remito-taller-nombre span { color: #2563EB; }
.remito-letra {
    width: 64px; height: 64px; border: 2px solid #111; border-radius: 6px;
    display: flex; flex-direction: column; align-items: center; justify-content: center;
    font-size: 32px; font-weight: 800; line-height: 1;
}
.remito-letra small { font-size: 9px; font-weight: 400; margin-top: 4px; }
.remito-numero { text-align: right; }
.remito-titulo { font-size: 20px; font-weight: 800; letter-spacing: 2px; }
.remito-datos {
    display: grid; grid-template-columns: 1fr 1fr; gap: 20px;
    padding: 16px 0; border-bottom: 1px solid #999;
}
.remito-subtitulo { font-size: 11px; font-weight: 700; text-transform: uppercase; color: #2563EB; margin-bottom: 4px; }
.remito-tabla-scroll { overflow-x: auto; }
.remito-tabla { width: 100%; margin-top: 16px; border-collapse: collapse; font: inherit; }
.remito-tabla th, .remito-tabla td { padding: 8px; border: 1px solid #999; color: #111; text-align: left; }
.remito-tabla th { background: #eee; font-size: 11px; text-transform: uppercase; }
.remito-tabla tfoot td { background: #f5f5f5; }
.remito-firmas { display: grid; grid-template-columns: 1fr 1fr; gap: 60px; margin-top: 70px; text-align: center; }
.remito-linea { border-top: 1px solid #111; margin-bottom: 6px; }
.remito-pie { margin-top: 30px; text-align: center; }
/* cuando se destilda "Mostrar precios" se esconden las columnas de plata */
.sin-precios .col-precio { display: none; }
/* en celular la cabecera y los datos pasan a una sola columna */
@media (max-width: 640px) {
    .remito-hoja { padding: 18px; }
    .remito-cabecera, .remito-datos, .remito-firmas { grid-template-columns: 1fr; }
    .remito-numero { text-align: left; }
}
</style>

<style>
/* este estilo es solo de la pantalla del sistema (no se copia a la impresión) */
.remito-check { display: inline-flex; align-items: center; gap: 6px; font-size: 13px; color: var(--text-muted); cursor: pointer; }
.remito-check input { accent-color: var(--accent-blue); }
</style>

<script>
// Muestra u oculta las columnas de precios del remito
function mostrarPrecios(mostrar) {
    document.getElementById('remito-hoja').classList.toggle('sin-precios', !mostrar);
}

// Imprime SOLO la hoja del remito: abre una ventana nueva con la hoja y sus estilos,
// llama a imprimir y después la cierra (misma idea que el resumen de órdenes de la ficha del vehículo)
function imprimirRemito() {
    const hoja    = document.getElementById('remito-hoja');
    const estilos = document.getElementById('estilos-remito').innerHTML;
    const ventana = window.open('', '_blank');
    if (!ventana) {
        notificar('error', 'No se pudo abrir la ventana de impresión. Permití las ventanas emergentes para este sitio.');
        return;
    }

    // <base> hace que la imagen del logo se encuentre igual que en el sistema
    const base = location.href.substring(0, location.href.lastIndexOf('/') + 1);

    ventana.document.open();
    ventana.document.write(`<!doctype html>
        <html lang="es">
        <head>
            <meta charset="UTF-8">
            <base href="${base}">
            <title>Remito <?= $numeroRemito ?></title>
            <style>
                ${estilos}
                @page { size: A4; margin: 10mm; }
                body { margin: 0; background: #fff; }
                .remito-hoja { max-width: none; padding: 0; border-radius: 0; }
                .remito-tabla tr { break-inside: avoid; page-break-inside: avoid; }
                @media print { body { -webkit-print-color-adjust: exact; print-color-adjust: exact; } }
            </style>
        </head>
        <body>${hoja.outerHTML}</body>
        </html>`);
    ventana.document.close();
    ventana.focus();

    // espero a que cargue el logo antes de imprimir (si no, a veces sale sin imagen)
    const lanzar = () => { ventana.print(); ventana.close(); };
    const logo = ventana.document.querySelector('img');
    if (!logo || logo.complete) {
        ventana.setTimeout(lanzar, 250);
    } else {
        logo.onload  = lanzar;
        logo.onerror = lanzar; // si el logo no carga, imprimo igual
    }
}
</script>

<?php require_once 'compartidoCREO/views/pie_pagina.php'; ?>

<?php require_once 'compartidoCREO/views/encabezado.php'; ?>

<div class="page-header">
    <h1>📦 Pedidos a proveedores</h1>
    <button type="button" class="btn btn-primary" onclick="abrirModal('modal-nuevo-pedido')">＋ Nuevo Pedido</button>
</div>

<div class="panel">
    <div class="panel-body">
        <table>
            <thead>
                <tr><th>#</th><th>Fecha</th><th>N° Único</th><th>Proveedor</th><th>Vehículo</th><th>Estado</th><th>Responsable</th><th>Cant.</th><th>Detalle</th><th>Acciones</th></tr>
            </thead>
            <tbody>
            <?php if(empty($data['pedidos'])): ?>
                <tr><td colspan="10" style="text-align:center;color:var(--text-muted);padding:30px;">No hay pedidos registrados</td></tr>
            <?php else: foreach($data['pedidos'] as $p): ?>
                <?php
                $estado = strtolower($p['estado_pedido'] ?? '');
                $recibido = ($estado === 'entregado');
                if ($recibido)                 { $ecls = 'disponible'; }
                elseif ($estado === 'cancelado') { $ecls = 'vendido'; }
                else                           { $ecls = 'reparacion'; }
                ?>
                <tr>
                    <td style="color:var(--text-muted);"><?= $p['id'] ?></td>
                    <td style="color:var(--text-muted);"><?= substr($p['fecha_pedidos'],0,10) ?></td>
                    <td><strong>#<?= $p['numero_unico'] ?></strong></td>
                    <td style="color:var(--text-muted);"><?= htmlspecialchars($p['nombre_proveedor'] ?? '—') ?></td>
                    <td style="color:var(--text-muted);"><?= htmlspecialchars($p['patente'] ?? '—') ?></td>
                    <td><span class="badge-status <?= $ecls ?>"><?= $recibido ? 'Recibido' : htmlspecialchars($p['estado_pedido']) ?></span></td>
                    <td><?= htmlspecialchars($p['responsable_pedido'] ?? '-') ?></td>
                    <td><?= $p['cantidad'] ?></td>
                    <td style="color:var(--text-muted);font-size:12px;"><?= htmlspecialchars(substr($p['detalle_pedido'] ?? '-', 0, 60)) ?></td>
                    <td>
                        <div class="action-icons">
                            <?php if(!$recibido && $estado !== 'cancelado'): ?>
                            <!-- RECIBIR: suma lo pedido al stock -->
                            <a href="index.php?page=pedidos&action=recibir&id=<?= $p['id'] ?>"
                               onclick="return confirmarAccion(this.href, '¿Llegó este pedido? Se va a sumar al stock lo que se pidió.')"
                               class="action-edit" title="Recibir pedido (suma al stock)">📥</a>
                            <?php endif; ?>
                            <?php if(!$recibido): ?>
                            <a href="index.php?page=pedidos&action=delete&id=<?= $p['id'] ?>"
                               onclick="return confirmarAccion(this.href, '¿Eliminar este pedido?')"
                               style="color:var(--text-primary);"
                               title="Eliminar"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg></a>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
            <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal: nuevo pedido, con renglones (repuesto + cantidad) -->
<div id="modal-nuevo-pedido" class="quick-modal-overlay">
    <div class="quick-modal-box">
        <div class="quick-modal-header">
            <h3>＋ Nuevo Pedido a proveedor</h3>
            <button type="button" class="quick-modal-close" onclick="cerrarModal('modal-nuevo-pedido')">&times;</button>
        </div>
        <div class="quick-modal-body">
            <form method="POST" action="index.php?page=pedidos&action=create" onsubmit="return enviarModalForm(this, event, 'toast-error-pedido')">
                <input type="hidden" name="ajax" value="1">
                <div id="toast-error-pedido" class="toast-error"></div>
                <input type="hidden" name="numero_unico" value="<?= rand(1000,9999) ?>">

                <div class="form-group">
                    <label>Proveedor</label>
                    <select name="proveedor_id">
                        <option value="">— Sin especificar —</option>
                        <?php foreach ($data['proveedores'] as $prov): ?>
                        <option value="<?= $prov['id'] ?>"><?= htmlspecialchars($prov['nombre_proveedor']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label>Responsable</label>
                    <input type="text" name="responsable_pedido" placeholder="Si lo dejás vacío se usa tu usuario">
                </div>

                <!-- Renglones: cada uno es un repuesto + cantidad -->
                <label style="font-size:13px;">Repuestos del pedido</label>
                <div id="renglones-pedido"></div>
                <button type="button" class="btn btn-ghost" onclick="agregarRenglonPedido()" style="margin:6px 0 14px;">＋ Agregar otro repuesto</button>

                <button type="submit" class="btn btn-primary">✅ Registrar Pedido</button>
            </form>
        </div>
    </div>
</div>

<!-- Molde de un renglón (el JavaScript lo copia cada vez que se agrega uno) -->
<template id="tpl-renglon-pedido">
    <div class="form-row renglon-pedido" style="align-items:flex-end;">
        <div class="form-group" style="flex:3;">
            <select name="repuesto_id[]">
                <option value="">— Repuesto —</option>
                <?php foreach ($data['repuestos'] as $r): ?>
                <option value="<?= $r['id'] ?>"><?= htmlspecialchars($r['nombre']) ?> (stock: <?= (int)$r['stock'] ?>)</option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group" style="flex:1;">
            <input type="number" name="cantidad[]" value="1" min="1">
        </div>
        <div class="form-group">
            <button type="button" class="btn btn-ghost" onclick="this.closest('.renglon-pedido').remove()" title="Quitar">✖</button>
        </div>
    </div>
</template>

<script>
// Agrega un renglón vacío al formulario del pedido
function agregarRenglonPedido() {
    const molde = document.getElementById('tpl-renglon-pedido');
    document.getElementById('renglones-pedido').appendChild(molde.content.cloneNode(true));
}
agregarRenglonPedido(); // arranca con un renglón ya puesto
</script>

<?php require_once 'compartidoCREO/views/pie_pagina.php'; ?>
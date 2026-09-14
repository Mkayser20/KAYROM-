<?php
require_once 'compartidoCREO/views/encabezado.php';
$c = $data['cliente']; //datos del cliente que llegan desde el controller
?>

<div class="page-header">
    <h1><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align:-4px;margin-right:4px;"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg> Ficha de Cliente — <?= htmlspecialchars(($c['nombre'] ?? '') . ' ' . ($c['apellido'] ?? '')) ?></h1>
    <a href="index.php?page=clientes" class="btn btn-ghost">&larr; Volver al listado</a>
</div>

<?php if (!$c): ?>
    <div class="panel"><div class="panel-body" style="padding:30px;text-align:center;color:var(--text-muted);">Cliente no encontrado.</div></div>
<?php else: ?>

<!-- Datos básicos del cliente -->
<div class="panel" style="margin-bottom:20px;">
    <div class="panel-header"><div class="panel-title">Datos del cliente</div></div>
    <div class="panel-body" style="padding:20px;">
        <div class="spec-grid">
            <div class="spec-item"><div class="spec-label">Nombre</div><div class="spec-value"><?= htmlspecialchars($c['nombre'] . ' ' . $c['apellido']) ?></div></div>
            <div class="spec-item"><div class="spec-label">DNI</div><div class="spec-value"><?= htmlspecialchars($c['dni']) ?></div></div>
            <div class="spec-item"><div class="spec-label">Teléfono</div><div class="spec-value"><?= htmlspecialchars($c['telefono'] ?: '—') ?></div></div>
        </div>
    </div>
</div>

<!-- Todos los vehículos que tiene este cliente a su nombre -->
<div class="panel">
    <div class="panel-header"><div class="panel-title">🚘 Vehículos de este cliente</div></div>
    <div class="panel-body">
        <?php if (empty($data['vehiculos'])): ?>
            <div style="padding:30px;text-align:center;color:var(--text-muted);">Todavía no tiene vehículos cargados.</div>
        <?php else: ?>
            <table>
                <thead><tr><th>Patente</th><th>Modelo</th><th>Tipo</th><th>Año</th><th>Estado</th><th></th></tr></thead>
                <tbody>
                <?php foreach ($data['vehiculos'] as $v): ?>
                    <tr>
                        <td><strong><?= htmlspecialchars($v['patente']) ?></strong></td>
                        <td><?= htmlspecialchars($v['modelo_vehiculo'] ?? '-') ?></td>
                        <td style="color:var(--text-muted);"><?= htmlspecialchars($v['tipo_vehiculo'] ?? '-') ?></td>
                        <td style="color:var(--text-muted);"><?= $v['anio'] ?? '-' ?></td>
                        <td><span class="badge-status reparacion"><?= htmlspecialchars($v['estado_taller'] ?? '-') ?></span></td>
                        <td><a href="index.php?page=vehiculos&action=verFicha&id=<?= $v['id'] ?>" class="action-edit" style="text-decoration:none;width:26px;height:26px;border-radius:6px;display:inline-flex;align-items:center;justify-content:center;">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                        </a></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>

<?php endif; ?>
<?php require_once 'compartidoCREO/views/pie_pagina.php'; ?>

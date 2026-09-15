<?php require_once 'compartidoCREO/views/encabezado.php'; ?>

<div class="page-header">
    <h1>📋 Auditoría</h1>
</div>

<div class="stats-grid" style="margin-bottom: 20px;">
    <?php
        $totalAuditorias = count($data['auditorias'] ?? []);
        $totalCreaciones = 0;
        $totalEdiciones = 0;
        $totalEliminaciones = 0;

        foreach ($data['auditorias'] ?? [] as $registro) {
            switch (strtoupper($registro['accion'] ?? '')) {
                case 'CREAR':
                    $totalCreaciones++;
                    break;
                case 'EDITAR':
                    $totalEdiciones++;
                    break;
                case 'ELIMINAR':
                    $totalEliminaciones++;
                    break;
            }
        }
    ?>

    <div class="stat-card blue">
        <div class="stat-icon">📊</div>
        <div class="stat-info">
            <div class="stat-label">Eventos</div>
            <div class="stat-value"><?= $totalAuditorias ?></div>
        </div>
    </div>

    <div class="stat-card green">
        <div class="stat-icon">✅</div>
        <div class="stat-info">
            <div class="stat-label">Creaciones</div>
            <div class="stat-value"><?= $totalCreaciones ?></div>
        </div>
    </div>

    <div class="stat-card orange">
        <div class="stat-icon">✏️</div>
        <div class="stat-info">
            <div class="stat-label">Ediciones</div>
            <div class="stat-value"><?= $totalEdiciones ?></div>
        </div>
    </div>

    <div class="stat-card red">
        <div class="stat-icon">🗑️</div>
        <div class="stat-info">
            <div class="stat-label">Eliminaciones</div>
            <div class="stat-value"><?= $totalEliminaciones ?></div>
        </div>
    </div>
</div>

<div class="panel">
    <div class="panel-header">
        <div class="panel-title">Historial de movimientos</div>
    </div>

    <div class="panel-body">
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Fecha</th>
                    <th>Usuario</th>
                    <th>Acción</th>
                    <th>Módulo</th>
                    <th>Descripción</th>
                    <th>Registro</th>
                    <th>IP</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($data['auditorias'])): ?>
                    <tr>
                        <td colspan="8" style="text-align:center;color:var(--text-muted);padding:30px;">
                            No hay eventos de auditoría registrados.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($data['auditorias'] as $a): ?>
                        <?php
                            $accion = strtoupper($a['accion'] ?? '');
                            $claseBadge = 'disponible';
                            if ($accion === 'EDITAR') {
                                $claseBadge = 'reparacion';
                            } elseif ($accion === 'ELIMINAR') {
                                $claseBadge = 'vendido';
                            }
                        ?>
                        <tr>
                            <td style="color:var(--text-muted);"><?= (int)($a['id'] ?? 0) ?></td>
                            <td style="color:var(--text-muted); font-family:var(--font-mono);">
                                <?= htmlspecialchars($a['fecha'] ?? '') ?>
                            </td>
                            <td>
                                <strong><?= htmlspecialchars($a['nombre_usuario'] ?? 'Sistema') ?></strong>
                            </td>
                            <td>
                                <span class="badge-status <?= $claseBadge ?>"><?= htmlspecialchars($accion) ?></span>
                            </td>
                            <td style="color:var(--text-muted);"><?= htmlspecialchars($a['modulo'] ?? '') ?></td>
                            <td><?= htmlspecialchars($a['descripcion'] ?? '') ?></td>
                            <td style="color:var(--text-muted); font-family:var(--font-mono);">
                                <?= htmlspecialchars($a['registro_id'] ?? '-') ?>
                            </td>
                            <td style="color:var(--text-muted); font-family:var(--font-mono);">
                                <?= htmlspecialchars($a['ip'] ?? '-') ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once 'compartidoCREO/views/pie_pagina.php'; ?>

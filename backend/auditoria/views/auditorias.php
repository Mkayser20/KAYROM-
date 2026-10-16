<?php require_once 'compartidoCREO/views/encabezado.php'; ?>

<div class="page-header">
    <h1><?= icono('lista') ?> Auditoría</h1>
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

    <div class="stat-card blue filtro-auditoria activo" data-filtro="" role="button" tabindex="0" aria-pressed="true" title="Filtrar: Eventos">
        <div class="stat-icon"><?= icono('grafico', 22) ?></div>
        <div class="stat-info">
            <div class="stat-label">Eventos</div>
            <div class="stat-value"><?= $totalAuditorias ?></div>
        </div>
    </div>

    <div class="stat-card green filtro-auditoria" data-filtro="CREAR" role="button" tabindex="0" aria-pressed="false" title="Filtrar: Creaciones">
        <div class="stat-icon"><?= icono('check', 22) ?></div>
        <div class="stat-info">
            <div class="stat-label">Creaciones</div>
            <div class="stat-value"><?= $totalCreaciones ?></div>
        </div>
    </div>

    <div class="stat-card orange filtro-auditoria" data-filtro="EDITAR" role="button" tabindex="0" aria-pressed="false" title="Filtrar: Ediciones">
        <div class="stat-icon"><?= icono('editar', 22) ?></div>
        <div class="stat-info">
            <div class="stat-label">Ediciones</div>
            <div class="stat-value"><?= $totalEdiciones ?></div>
        </div>
    </div>

    <div class="stat-card red filtro-auditoria" data-filtro="ELIMINAR" role="button" tabindex="0" aria-pressed="false" title="Filtrar: Eliminaciones">
        <div class="stat-icon"><?= icono('eliminar', 22) ?></div>
        <div class="stat-info">
            <div class="stat-label">Eliminaciones</div>
            <div class="stat-value"><?= $totalEliminaciones ?></div>
        </div>
    </div>
</div>

<div class="panel">
    <div class="panel-header">
        <div class="panel-title">Historial de movimientos <span id="filtroAuditoriaEtiqueta" style="color:var(--text-muted);font-weight:normal;"></span></div>
    </div>

    <div class="panel-body">
        <table class="tabla-cards">
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
            <tbody id="tablaAuditorias">
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
                        <tr data-accion="<?= htmlspecialchars($accion) ?>">
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
                <tr id="filaSinResultados" style="display:none;">
                    <td colspan="8" style="text-align:center;color:var(--text-muted);padding:30px;">
                        No hay eventos de este tipo.
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<style>
    .filtro-auditoria { cursor: pointer; user-select: none; }
    .filtro-auditoria.blue.activo   { border-color: #60A5FA; box-shadow: 0 0 0 1px #60A5FA inset; }
    .filtro-auditoria.green.activo  { border-color: #10B981; box-shadow: 0 0 0 1px #10B981 inset; }
    .filtro-auditoria.orange.activo { border-color: #F59E0B; box-shadow: 0 0 0 1px #F59E0B inset; }
    .filtro-auditoria.red.activo    { border-color: #EF4444; box-shadow: 0 0 0 1px #EF4444 inset; }
    .filtro-auditoria:focus-visible { outline: 2px solid #60A5FA; outline-offset: 2px; }
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const tarjetas = document.querySelectorAll('.filtro-auditoria');
    const filas = document.querySelectorAll('#tablaAuditorias tr[data-accion]');
    const sinResultados = document.getElementById('filaSinResultados');
    const etiqueta = document.getElementById('filtroAuditoriaEtiqueta');
    const nombres = { CREAR: 'Creaciones', EDITAR: 'Ediciones', ELIMINAR: 'Eliminaciones' };

    function aplicarFiltro(filtro) {
        let visibles = 0;
        filas.forEach(function (fila) {
            const mostrar = !filtro || fila.dataset.accion === filtro;
            fila.style.display = mostrar ? '' : 'none';
            if (mostrar) visibles++;
        });

        tarjetas.forEach(function (t) {
            const activa = t.dataset.filtro === filtro;
            t.classList.toggle('activo', activa);
            t.setAttribute('aria-pressed', activa ? 'true' : 'false');
        });

        sinResultados.style.display = (filas.length && visibles === 0) ? '' : 'none';
        etiqueta.textContent = filtro ? '· ' + nombres[filtro] + ' (' + visibles + ')' : '';
    }

    tarjetas.forEach(function (t) {
        // Clic en la tarjeta activa vuelve a mostrar todo
        function alternar() {
            const yaActiva = t.classList.contains('activo');
            aplicarFiltro(yaActiva ? '' : t.dataset.filtro);
        }
        t.addEventListener('click', alternar);
        t.addEventListener('keydown', function (e) {
            if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); alternar(); }
        });
    });
});
</script>

<?php require_once 'compartidoCREO/views/pie_pagina.php'; ?>

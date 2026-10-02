<?php
require_once 'compartidoCREO/views/encabezado.php';
?>

<div class="page-header">
    <h1>🔄 Movimientos de stock</h1>
    <a href="index.php?page=movimientos&action=create" class="btn btn-primary">＋ Movimiento manual</a>
</div>

<div class="panel">
    <div class="panel-body">
        <table>
            <thead>
                <tr>
                    <th>#</th><th>Fecha</th><th>Tipo</th><th>Repuesto</th><th>Cantidad</th><th>Origen</th><th>Usuario</th><th>Descripción</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($data['movimientos'])): ?>
                <tr><td colspan="8" style="text-align:center;color:var(--text-muted);padding:30px;">Todavía no hay movimientos</td></tr>
                <?php else: foreach ($data['movimientos'] as $m): ?>
                <?php
                // Color e ícono según el tipo de movimiento
                if ($m['tipo'] === 'Entrada')     { $tipoCls = 'disponible'; $tipoIcon = '📥'; }
                elseif ($m['tipo'] === 'Salida')  { $tipoCls = 'vendido';    $tipoIcon = '📤'; }
                else                              { $tipoCls = 'reparacion'; $tipoIcon = '🔁'; }

                // Texto de origen: "Pedido #12", "Orden de Trabajo #5", "Manual"...
                $origen = $m['origen'] ?? 'Manual';
                if (!empty($m['origen_id'])) $origen .= ' #' . (int)$m['origen_id'];
                ?>
                <tr>
                    <td style="color:var(--text-muted);"><?= $m['id'] ?></td>
                    <td style="color:var(--text-muted);white-space:nowrap;"><?= substr($m['fecha'], 0, 16) ?></td>
                    <td><span class="badge-status <?= $tipoCls ?>"><?= $tipoIcon ?> <?= htmlspecialchars($m['tipo']) ?></span></td>
                    <td><?= htmlspecialchars($m['repuesto_nombre'] ?? '—') ?></td>
                    <td><strong><?= (int)$m['cantidad'] ?></strong></td>
                    <td style="color:var(--text-muted);"><?= htmlspecialchars($origen) ?></td>
                    <td style="color:var(--text-muted);"><?= htmlspecialchars($m['nombre_usuario'] ?? '—') ?></td>
                    <td style="font-size:12px;"><?= htmlspecialchars($m['descripcion'] ?? '') ?></td>
                </tr>
                <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once 'compartidoCREO/views/pie_pagina.php'; ?>
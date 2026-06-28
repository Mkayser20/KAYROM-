<?php
require_once 'compartidoCREO/views/encabezado.php';
?>

<div class="page-header">
    <h1>🔄 Movimientos</h1>
    <a href="index.php?page=movimientos&action=create" class="btn btn-primary">＋ Nuevo Movimiento</a>
</div>

<div class="panel">
    <div class="panel-body">
        <table>
            <thead>
                <tr>
                    <th>#</th><th>Fecha</th><th>Tipo</th><th>Descripción</th><th>Cantidad</th><th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($data['movimientos'] as $m): ?>
                <?php
                $tipoCls = match($m['tipo']) {
                    'Entrada' => 'disponible',
                    'Salida'  => 'vendido',
                    default   => 'reparacion'
                };
                $tipoIcon = match($m['tipo']) {
                    'Entrada' => '📥',
                    'Salida'  => '📤',
                    default   => '🔁'
                };
                ?>
                <tr>
                    <td style="color:var(--text-muted);"><?= $m['id'] ?></td>
                    <td style="color:var(--text-muted);white-space:nowrap;"><?= substr($m['fecha'],0,10) ?></td>
                    <td><span class="badge-status <?= $tipoCls ?>"><?= $tipoIcon ?> <?= $m['tipo'] ?></span></td>
                    <td><?= htmlspecialchars($m['descripcion']) ?></td>
                    <td><strong><?= $m['cantidad'] ?></strong></td>
                    <td>
                        <a href="index.php?page=movimientos&action=delete&id=<?= $m['id'] ?>" class="action-delete"
                           onclick="return confirm('¿Eliminar este movimiento?')" style="width:30px;height:30px;border-radius:7px;display:inline-flex;align-items:center;justify-content:center;background:rgba(239,68,68,0.15);color:var(--accent-red);text-decoration:none;">🗑️</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once 'compartidoCREO/views/pie_pagina.php'; ?>

<?php require_once 'views/encabezado.php'; ?>

<div class="page-header">
    <h1>🚘 Vehículos</h1>
    <a href="index.php?page=vehiculos&action=create" class="btn btn-green">＋ Nuevo Vehículo</a>
</div>

<div class="panel">
    <div class="panel-body">
        <table>
            <thead>
                <tr><th>#</th><th>Patente</th><th>N° Chasis</th><th>N° Motor</th><th>Modelo</th><th>Tipo</th><th>Año</th><th>Fecha Ingreso</th><th>Acciones</th></tr>
            </thead>
            <tbody>
            <?php if(empty($data['vehiculos'])): ?>
                <tr><td colspan="9" style="text-align:center;color:var(--text-muted);padding:30px;">No hay vehículos registrados</td></tr>
            <?php else: foreach($data['vehiculos'] as $v): ?>
                <tr>
                    <td style="color:var(--text-muted);"><?= $v['id'] ?></td>
                    <td><strong><?= htmlspecialchars($v['patente'] ?? '-') ?></strong></td>
                    <td style="color:var(--text-muted);"><?= htmlspecialchars($v['numero_chasis'] ?? '-') ?></td>
                    <td style="color:var(--text-muted);"><?= htmlspecialchars($v['numero_motor'] ?? '-') ?></td>
                    <td><?= htmlspecialchars($v['modelo_vehiculo'] ?? '-') ?></td>
                    <td><span class="badge-status disponible"><?= htmlspecialchars($v['tipo_vehiculo'] ?? '-') ?></span></td>
                    <td><?= $v['anio_vehiculo'] ?? '-' ?></td>
                    <td style="color:var(--text-muted);"><?= isset($v['fecha_ingreso']) ? substr($v['fecha_ingreso'],0,10) : '-' ?></td>
                    <td>
                        <div class="action-icons">
                            <a href="index.php?page=vehiculos&action=edit&id=<?= $v['id'] ?>" class="action-edit">✏️</a>
                            <a href="index.php?page=vehiculos&action=delete&id=<?= $v['id'] ?>" class="action-delete"
                               onclick="return confirmarAccion(this.href, '¿Eliminar este vehículo?')" //reemplazar confirm() nativo por modal propio en vehículos

                                
                        </div>
                    </td>
                </tr>
            <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php require_once 'views/pie_pagina.php'; ?>

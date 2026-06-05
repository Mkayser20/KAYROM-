<?php
// views/vehiculos.php
require_once 'views/layout_header.php';
?>

<div class="page-header">
    <h1>🚘 Vehículos</h1>
    <a href="index.php?page=vehiculos&action=create" class="btn btn-green">＋ Nuevo Vehículo</a>
</div>

<div class="panel">
    <div class="panel-body">
        <table>
            <thead>
                <tr>
                    <th>#</th><th>Marca</th><th>Modelo</th><th>Año</th><th>Estado</th><th>Fecha Alta</th><th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($data['vehiculos'] as $v): ?>
                <?php
                $cls = match($v['estado']) {
                    'Disponible'   => 'disponible',
                    'Vendido'      => 'vendido',
                    'En Reparación'=> 'reparacion',
                    default        => ''
                };
                ?>
                <tr>
                    <td style="color:var(--text-muted);"><?= $v['id'] ?></td>
                    <td><?= htmlspecialchars($v['marca']) ?></td>
                    <td><?= htmlspecialchars($v['modelo']) ?></td>
                    <td><?= $v['anio'] ?></td>
                    <td><span class="badge-status <?= $cls ?>"><?= $v['estado'] ?></span></td>
                    <td style="color:var(--text-muted);"><?= substr($v['created_at'],0,10) ?></td>
                    <td>
                        <div class="action-icons">
                            <a href="index.php?page=vehiculos&action=edit&id=<?= $v['id'] ?>" class="action-edit" title="Editar">✏️</a>
                            <a href="index.php?page=vehiculos&action=delete&id=<?= $v['id'] ?>" class="action-delete" title="Eliminar"
                               onclick="return confirm('¿Eliminar este vehículo?')">🗑️</a>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once 'views/layout_footer.php'; ?>

<?php
require_once 'views/encabezado.php';

$statsV = $data['estadVehiculos'];
$totalV = $data['totalVehiculos'] ?: 1;

$colors = ['#3b82f6','#22c55e','#f97316','#a855f7','#ef4444','#eab308'];

function pieSegment($cx,$cy,$r,$startAngle,$endAngle,$color){
    if($endAngle-$startAngle>=360) $endAngle=$startAngle+359.99;
    $s=polarXY($cx,$cy,$r,$startAngle);
    $e=polarXY($cx,$cy,$r,$endAngle);
    $li=($endAngle-$startAngle)>180?1:0;
    $inner=42;
    $is=polarXY($cx,$cy,$inner,$startAngle);
    $ie=polarXY($cx,$cy,$inner,$endAngle);
    $d="M{$s['x']},{$s['y']} A$r,$r 0 $li,1 {$e['x']},{$e['y']} L{$ie['x']},{$ie['y']} A$inner,$inner 0 $li,0 {$is['x']},{$is['y']} Z";
    return "<path d=\"$d\" fill=\"$color\" opacity=\"0.93\"/>";
}
function polarXY($cx,$cy,$r,$a){
    $a=($a-90)*M_PI/180;
    return['x'=>round($cx+$r*cos($a),2),'y'=>round($cy+$r*sin($a),2)];
}

$svgParts=[]; $cur=0;
foreach($statsV as $i=>$s){
    $pct=$s['total']/$totalV;
    $span=$pct*360;
    $svgParts[]=pieSegment(80,80,74,$cur,$cur+$span,$colors[$i%count($colors)]);
    $cur+=$span;
}
?>

<div class="stats-grid">
    <div class="stat-card blue">
        <div class="stat-icon">🚗</div>
        <div class="stat-info">
            <div class="stat-label">Total Vehículos</div>
            <div class="stat-value"><?= $data['totalVehiculos'] ?></div>
        </div>
    </div>
    <div class="stat-card green">
        <div class="stat-icon">📦</div>
        <div class="stat-info">
            <div class="stat-label">Unidades en Stock</div>
            <div class="stat-value"><?= $data['totalStock'] ?></div>
        </div>
    </div>
    <div class="stat-card orange">
        <div class="stat-icon">⚠️</div>
        <div class="stat-info">
            <div class="stat-label">Productos Stock Bajo</div>
            <div class="stat-value"><?= $data['stockBajo'] ?></div>
        </div>
    </div>
    <div class="stat-card red">
        <div class="stat-icon">📋</div>
        <div class="stat-info">
            <div class="stat-label">Total Pedidos</div>
            <div class="stat-value"><?= $data['totalPedidos'] ?></div>
        </div>
    </div>
</div>

<div class="dashboard-grid">

    <div class="panel">
        <div class="panel-header">
            <div class="panel-title">🚘 Últimos Vehículos Añadidos</div>
            <a href="index.php?page=vehiculos&action=create" class="btn btn-green" style="font-size:11px;padding:5px 12px;">+ Nuevo</a>
        </div>
        <div class="panel-body">
            <table>
                <thead><tr><th>Patente</th><th>Modelo</th><th>Tipo</th><th>Año</th><th></th></tr></thead>
                <tbody>
                <?php if(empty($data['recentVehiculos'])): ?>
                    <tr><td colspan="5" style="text-align:center;color:var(--text-muted);padding:20px;">Sin registros aún</td></tr>
                <?php else: foreach($data['recentVehiculos'] as $v): ?>
                    <tr>
                        <td><strong><?= htmlspecialchars($v['patente'] ?? '-') ?></strong></td>
                        <td><?= htmlspecialchars($v['modelo_vehiculo'] ?? '-') ?></td>
                        <td style="color:var(--text-muted);"><?= htmlspecialchars($v['tipo_vehiculo'] ?? '-') ?></td>
                        <td style="color:var(--text-muted);"><?= $v['anio_vehiculo'] ?? '-' ?></td>
                        <td><a href="index.php?page=vehiculos&action=edit&id=<?= $v['id'] ?>" class="action-edit" style="text-decoration:none;width:26px;height:26px;border-radius:6px;display:inline-flex;align-items:center;justify-content:center;background:rgba(59,130,246,0.15);color:var(--accent-blue);">✏️</a></td>
                    </tr>
                <?php endforeach; endif; ?>
                </tbody>
            </table>
            <div style="padding:12px 16px;">
                <a href="index.php?page=vehiculos" class="btn btn-green" style="font-size:12px;">Ver Todos &rsaquo;</a>
            </div>
        </div>
    </div>

    <div class="panel">
        <div class="panel-header">
            <div class="panel-title">⚠️ Alertas de Stock Bajo</div>
        </div>
        <div class="panel-body">
            <div class="alert-list">
            <?php if(empty($data['lowStockItems'])): ?>
                <div style="padding:20px;text-align:center;color:var(--accent-green);">✅ Todo el stock está OK</div>
            <?php else: foreach($data['lowStockItems'] as $item): ?>
                <div class="alert-item">
                    <div style="display:flex;align-items:center;">
                        <div class="alert-dot"></div>
                        <span><?= htmlspecialchars($item['nombre_producto']) ?></span>
                    </div>
                    <span class="alert-stock"><?= $item['cantidad_disponible'] ?> en stock</span>
                </div>
            <?php endforeach; endif; ?>
            </div>
            <div style="padding:12px 16px;">
                <a href="index.php?page=productos" class="btn btn-orange" style="font-size:12px;">Ver Detalles &rsaquo;</a>
            </div>
        </div>
    </div>

    <div class="panel">
        <div class="panel-header">
            <div class="panel-title">📊 Vehículos por Tipo</div>
        </div>
        <div class="panel-body">
            <div class="chart-container">
                <div class="pie-wrap">
                    <svg viewBox="0 0 160 160" xmlns="http://www.w3.org/2000/svg">
                        <?php if(empty($statsV)): ?>
                        <circle cx="80" cy="80" r="74" fill="var(--border)" opacity="0.4"/>
                        <?php else: echo implode('',$svgParts); endif; ?>
                        <circle cx="80" cy="80" r="38" fill="var(--bg-card)"/>
                    </svg>
                    <div class="pie-label-center">
                        <div class="total"><?= $data['totalVehiculos'] ?></div>
                        <div class="sub">total</div>
                    </div>
                </div>
                <div class="legend">
                <?php foreach($statsV as $i=>$s): ?>
                    <div class="legend-item">
                        <div class="legend-dot" style="background:<?= $colors[$i%count($colors)] ?>"></div>
                        <span><?= htmlspecialchars($s['label'] ?? 'Sin tipo') ?></span>
                        <strong style="margin-left:auto;padding-left:12px;"><?= $s['total'] ?></strong>
                    </div>
                <?php endforeach; ?>
                <?php if(empty($statsV)): ?>
                    <div style="color:var(--text-muted);font-size:13px;">Sin datos aún</div>
                <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="panel">
        <div class="panel-header">
            <div class="panel-title">📦 Pedidos Recientes</div>
            <a href="index.php?page=pedidos&action=create" class="btn btn-primary" style="font-size:11px;padding:5px 12px;">+ Nuevo</a>
        </div>
        <div class="panel-body">
            <table>
                <thead><tr><th>Fecha</th><th>N° Único</th><th>Estado</th><th>Cantidad</th></tr></thead>
                <tbody>
                <?php if(empty($data['recentPedidos'])): ?>
                    <tr><td colspan="4" style="text-align:center;color:var(--text-muted);padding:20px;">Sin pedidos aún</td></tr>
                <?php else: foreach($data['recentPedidos'] as $p): ?>
                    <?php
                    $ecls = match(strtolower($p['estado_pedido'] ?? '')) {
                        'entregado','completado' => 'disponible',
                        'cancelado'              => 'vendido',
                        default                  => 'reparacion'
                    };
                    ?>
                    <tr>
                        <td style="color:var(--text-muted);"><?= substr($p['fecha_pedidos'],0,10) ?></td>
                        <td><strong>#<?= $p['numero_unico'] ?></strong></td>
                        <td><span class="badge-status <?= $ecls ?>"><?= htmlspecialchars($p['estado_pedido']) ?></span></td>
                        <td><?= $p['cantidad'] ?></td>
                    </tr>
                <?php endforeach; endif; ?>
                </tbody>
            </table>
            <div style="padding:12px 16px;">
                <a href="index.php?page=pedidos" class="btn btn-primary" style="font-size:12px;">Ver Historial &rsaquo;</a>
            </div>
        </div>
    </div>

</div>
<?php require_once 'views/pie_pagina.php'; ?>

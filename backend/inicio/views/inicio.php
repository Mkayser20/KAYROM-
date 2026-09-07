<?php
require_once 'compartidoCREO/views/encabezado.php';

$statsV = $data['estadVehiculos'];
$totalV = $data['totalVehiculos'] ?: 1;

$colors = ['#3B82F6','#60A5FA','#6366F1','#38BDF8','#10B981','#EF4444'];

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
        <div class="stat-icon"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="1" y="3" width="15" height="13" rx="2"/><path d="M16 8h4l3 5v3h-7V8z"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/></svg></div>
        <div class="stat-info">
            <div class="stat-label">Total Vehículos</div>
            <div class="stat-value"><?= $data['totalVehiculos'] ?></div>
        </div>
    </div>
    <div class="stat-card green">
        <div class="stat-icon"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 8V21H3V8"/><path d="M1 3h22v5H1z"/><path d="M10 12h4"/></svg></div>
        <div class="stat-info">
            <div class="stat-label">Unidades en Stock</div>
            <div class="stat-value"><?= $data['totalStock'] ?></div>
        </div>
    </div>
    <div class="stat-card orange">
        <div class="stat-icon"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg></div>
        <div class="stat-info">
            <div class="stat-label">Productos Stock Bajo</div>
            <div class="stat-value"><?= $data['stockBajo'] ?></div>
        </div>
    </div>
    <div class="stat-card red">
        <div class="stat-icon"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/><rect x="8" y="2" width="8" height="4" rx="1"/><line x1="9" y1="12" x2="15" y2="12"/><line x1="9" y1="16" x2="15" y2="16"/></svg></div>
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
            <a href="index.php?page=vehiculos&action=create" class="btn btn-primary" style="font-size:11px;padding:5px 12px;">+ Nuevo</a>
        </div>
        <div class="panel-body">
            <table>
                <thead><tr><th>Patente</th><th>Modelo</th><th>Tipo</th><th>Año</th><th>Estado</th><th></th></tr></thead>
                <tbody>
                <?php if(empty($data['recentVehiculos'])): ?>
                    <tr><td colspan="6" style="text-align:center;color:var(--text-muted);padding:20px;">Sin registros aún</td></tr>
                <?php else: foreach($data['recentVehiculos'] as $v): ?>
                    <?php
                        $estadoBadge = ['En Diagnóstico'=>'reparacion','En Reparación'=>'reparacion','Esperando Repuestos'=>'vendido','Listo para Entrega'=>'disponible','Entregado'=>'disponible'];
                        $et = $v['estado_taller'] ?? 'En Diagnóstico';
                    ?>
                    <tr>
                        <td><strong><?= htmlspecialchars($v['patente'] ?? '-') ?></strong></td>
                        <td><?= htmlspecialchars($v['modelo_vehiculo'] ?? '-') ?></td>
                        <td style="color:var(--text-muted);"><?= htmlspecialchars($v['tipo_vehiculo'] ?? '-') ?></td>
                        <td style="color:var(--text-muted);"><?= $v['anio'] ?? '-' ?></td>
                        <td><span class="badge-status <?= $estadoBadge[$et] ?? 'reparacion' ?>" style="font-size:10px;"><?= htmlspecialchars($et) ?></span></td>
                        <td><a href="index.php?page=vehiculos&action=edit&id=<?= $v['id'] ?>" class="action-edit" style="text-decoration:none;width:26px;height:26px;border-radius:6px;display:inline-flex;align-items:center;justify-content:center;"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg></a></td>
                    </tr>
                <?php endforeach; endif; ?>
                </tbody>
            </table>
            <div style="padding:12px 16px;">
                <a href="index.php?page=vehiculos" class="btn btn-ghost" style="font-size:12px;">Ver Todos &rsaquo;</a>
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
                        <span><?= htmlspecialchars($item['nombre']) ?></span>
                    </div>
                    <span class="alert-stock"><?= $item['stock'] ?> en stock</span>
                </div>
            <?php endforeach; endif; ?>
            </div>
            <div style="padding:12px 16px;">
                <a href="index.php?page=repuestos" class="btn btn-ghost" style="font-size:12px;">Ver Detalles &rsaquo;</a>
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
<?php require_once 'compartidoCREO/views/pie_pagina.php'; ?>

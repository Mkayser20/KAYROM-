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

//armo el gráfico de torta de Vehículos por Tipo (el que ya teníamos)
$svgParts=[]; $cur=0;
foreach($statsV as $i=>$s){
    $pct=$s['total']/$totalV;
    $span=$pct*360;
    $svgParts[]=pieSegment(80,80,74,$cur,$cur+$span,$colors[$i%count($colors)]);
    $cur+=$span;
}

//armo el gráfico de torta nuevo: Pedidos por Estado (mismo mecanismo, otros datos)
$statsP = $data['estadPedidos'];
$totalP = array_sum(array_column($statsP, 'total')) ?: 1;
$svgPartsPedidos=[]; $curP=0;
foreach($statsP as $i=>$s){
    $pct=$s['total']/$totalP;
    $span=$pct*360;
    $svgPartsPedidos[]=pieSegment(80,80,74,$curP,$curP+$span,$colors[$i%count($colors)]);
    $curP+=$span;
}

//para los gráficos de barras, necesito el valor más alto de cada set, así calculo el ancho (%) de cada barra
$maxTaller    = !empty($data['estadEstadoTaller']) ? max(array_column($data['estadEstadoTaller'], 'total')) : 1;
$maxRepuestos = !empty($data['estadRepuestos']) ? max(array_column($data['estadRepuestos'], 'total')) : 1;
?>

<div class="ops-grid">

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

    <div class="ops-grid-stack">

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
                <div class="panel-title">📦 Pedidos Recientes</div>
                <a href="index.php?page=pedidos&action=create" class="btn btn-primary" style="font-size:11px;padding:5px 12px;">+ Nuevo</a>
            </div>
            <div class="panel-body">
                <table>
                    <thead><tr><th>Fecha</th><th>N° Único</th><th>Estado</th><th>Cant.</th></tr></thead>
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

</div>

<!-- Separador de sección: acá termina lo operativo y arranca la parte de Informes y Gráficos -->
<div class="section-divider">
    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>
    <span>Informes y Gráficos</span>
    <div class="line"></div>
</div>

<div class="dashboard-grid">

    <!-- Informe 1: Vehículos por Tipo (torta) -->
    <div class="panel">
        <div class="panel-header">
            <div class="panel-title">🚘 Vehículos por Tipo</div>
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

    <!-- Informe 2: Pedidos por Estado (torta) -->
    <div class="panel">
        <div class="panel-header">
            <div class="panel-title">📦 Pedidos por Estado</div>
        </div>
        <div class="panel-body">
            <div class="chart-container">
                <div class="pie-wrap">
                    <svg viewBox="0 0 160 160" xmlns="http://www.w3.org/2000/svg">
                        <?php if(empty($statsP)): ?>
                        <circle cx="80" cy="80" r="74" fill="var(--border)" opacity="0.4"/>
                        <?php else: echo implode('',$svgPartsPedidos); endif; ?>
                        <circle cx="80" cy="80" r="38" fill="var(--bg-card)"/>
                    </svg>
                    <div class="pie-label-center">
                        <div class="total"><?= $totalP ?></div>
                        <div class="sub">total</div>
                    </div>
                </div>
                <div class="legend">
                <?php foreach($statsP as $i=>$s): ?>
                    <div class="legend-item">
                        <div class="legend-dot" style="background:<?= $colors[$i%count($colors)] ?>"></div>
                        <span><?= htmlspecialchars($s['label'] ?? 'Sin estado') ?></span>
                        <strong style="margin-left:auto;padding-left:12px;"><?= $s['total'] ?></strong>
                    </div>
                <?php endforeach; ?>
                <?php if(empty($statsP)): ?>
                    <div style="color:var(--text-muted);font-size:13px;">Sin datos aún</div>
                <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Informe 3: Vehículos por Estado de Taller (barras) -->
    <div class="panel">
        <div class="panel-header">
            <div class="panel-title">🔧 Vehículos por Estado de Taller</div>
        </div>
        <div class="panel-body">
            <div class="bar-chart">
            <?php if(empty($data['estadEstadoTaller'])): ?>
                <div style="color:var(--text-muted);font-size:13px;">Sin datos aún</div>
            <?php else: foreach($data['estadEstadoTaller'] as $e): ?>
                <div class="bar-row">
                    <div class="bar-row-top">
                        <span><?= htmlspecialchars($e['label'] ?? 'Sin estado') ?></span>
                        <b><?= $e['total'] ?></b>
                    </div>
                    <div class="bar-track">
                        <div class="bar-fill" style="width:<?= round(($e['total']/$maxTaller)*100) ?>%;"></div>
                    </div>
                </div>
            <?php endforeach; endif; ?>
            </div>
        </div>
    </div>

    <!-- Informe 4: Repuestos por Categoría (barras) -->
    <div class="panel">
        <div class="panel-header">
            <div class="panel-title">🛠️ Repuestos por Categoría</div>
        </div>
        <div class="panel-body">
            <div class="bar-chart">
            <?php if(empty($data['estadRepuestos'])): ?>
                <div style="color:var(--text-muted);font-size:13px;">Sin datos aún</div>
            <?php else: foreach($data['estadRepuestos'] as $r): ?>
                <div class="bar-row">
                    <div class="bar-row-top">
                        <span><?= htmlspecialchars($r['label'] ?? 'Sin categoría') ?></span>
                        <b><?= $r['total'] ?></b>
                    </div>
                    <div class="bar-track">
                        <div class="bar-fill cyan" style="width:<?= round(($r['total']/$maxRepuestos)*100) ?>%;"></div>
                    </div>
                </div>
            <?php endforeach; endif; ?>
            </div>
        </div>
    </div>

</div>

<?php require_once 'compartidoCREO/views/pie_pagina.php'; ?>

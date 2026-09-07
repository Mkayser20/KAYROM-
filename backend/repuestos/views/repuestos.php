<?php require_once 'compartidoCREO/views/encabezado.php'; ?>

<div class="page-header">
    <h1>🔧 Catálogo de Repuestos e Insumos</h1>
    <button type="button" class="btn btn-primary" onclick="abrirRepuesto()">＋ Nuevo Repuesto</button>
</div>

<!-- Barra de filtros: todo corre en el navegador (JS), no pega contra la base de nuevo -->
<div class="filter-bar">
    <div class="filter-group filter-search">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
        <input type="text" id="filtro-buscar" placeholder="Buscar por nombre de repuesto..." autocomplete="off">
    </div>
    <div class="filter-group">
        <label>Categoría</label>
        <select id="filtro-categoria">
            <option value="">Todas</option>
            <?php foreach ($data['categorias'] as $cat): ?>
                <option value="<?= htmlspecialchars($cat) ?>"><?= htmlspecialchars($cat) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="filter-group">
        <label>Estado</label>
        <select id="filtro-estado">
            <option value="">Todos</option>
            <option value="ok">En Stock</option>
            <option value="bajo">Stock Bajo</option>
            <option value="sin">Sin Stock</option>
        </select>
    </div>
</div>

<div class="panel">
    <div class="panel-body">
        <table id="tabla-repuestos">
            <thead>
                <tr>
                    <th>#</th><th>SKU</th><th>Nombre</th><th>Categoría</th><th>Stock (Actual / Mín.)</th><th>Precio</th><th>Proveedor</th><th>Estado</th><th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($data['repuestos'])): ?>
                <tr><td colspan="9" style="text-align:center;color:var(--text-muted);padding:30px;">No hay repuestos cargados</td></tr>
                <?php else: foreach ($data['repuestos'] as $r): ?>
                <?php
                    $bajo = $r['stock'] <= $r['stock_minimo'];
                    $sinStock = $r['stock'] == 0;
                    $estadoFiltro = $sinStock ? 'sin' : ($bajo ? 'bajo' : 'ok');
                ?>
                <tr data-nombre="<?= htmlspecialchars(mb_strtolower($r['nombre'])) ?>"
                    data-categoria="<?= htmlspecialchars($r['categoria']) ?>"
                    data-estado="<?= $estadoFiltro ?>">
                    <td style="color:var(--text-muted);"><?= $r['id'] ?></td>
                    <td style="color:var(--text-muted);font-family:var(--font-mono);font-size:12px;"><?= htmlspecialchars($r['sku'] ?? '-') ?></td>
                    <td><?= htmlspecialchars($r['nombre']) ?></td>
                    <td style="color:var(--text-muted);"><?= htmlspecialchars($r['categoria']) ?></td>
                    <td>
                        <strong style="color:<?= $bajo ? 'var(--accent-orange)' : 'var(--accent-green)' ?>">
                            <?= $r['stock'] ?>
                        </strong>
                        <span style="color:var(--text-dim);"> / <?= $r['stock_minimo'] ?></span>
                    </td>
                    <td>$<?= number_format($r['precio'], 0, ',', '.') ?></td>
                    <td style="color:var(--text-muted);"><?= htmlspecialchars($r['nombre_proveedor'] ?? '—') ?></td>
                    <td>
                        <?php if ($sinStock): ?>
                            <span class="badge-status vendido">Sin Stock</span>
                        <?php elseif ($bajo): ?>
                            <span class="badge-status reparacion">Stock Bajo</span>
                        <?php else: ?>
                            <span class="badge-status disponible">OK</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <div class="action-icons">
                            <?php if ($r['stock'] > 0): ?>
                            <a href="index.php?page=carrito&action=agregar&id=<?= $r['id'] ?>" class="action-edit" title="Agregar al carrito">
                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg>
                            </a>
                            <?php endif; ?>
                            <a href="#" class="action-edit" title="Editar"
                               onclick="return abrirRepuesto({
                                    id: <?= $r['id'] ?>,
                                    nombre: <?= htmlspecialchars(json_encode($r['nombre']), ENT_QUOTES, 'UTF-8') ?>,
                                    sku: <?= htmlspecialchars(json_encode($r['sku']), ENT_QUOTES, 'UTF-8') ?>,
                                    proveedor_id: <?= (int)($r['proveedor_id'] ?? 0) ?>,
                                    categoria: <?= htmlspecialchars(json_encode($r['categoria']), ENT_QUOTES, 'UTF-8') ?>,
                                    precio: <?= (float)$r['precio'] ?>,
                                    stock: <?= (int)$r['stock'] ?>,
                                    stock_minimo: <?= (int)$r['stock_minimo'] ?>
                               })">
                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                            </a>
                            <a href="index.php?page=repuestos&action=delete&id=<?= $r['id'] ?>" class="action-delete" title="Eliminar"
                               onclick="return confirmarAccion(this.href, '¿Eliminar este repuesto?')">
                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                            </a>
                        </div>
                    </td>
                </tr>
                <?php endforeach; endif; ?>
            </tbody>
        </table>
        <p id="sin-resultados" style="display:none;text-align:center;color:var(--text-muted);padding:30px;">
            Ningún repuesto coincide con ese filtro.
        </p>
    </div>
</div>

<!-- Modal: alta y edición de repuesto (un solo modal para las dos cosas) -->
<div id="modal-nuevo-repuesto" class="quick-modal-overlay">
    <div class="quick-modal-box">
        <div class="quick-modal-header">
            <h3 id="rep-titulo">＋ Nuevo Repuesto</h3>
            <button type="button" class="quick-modal-close" onclick="cerrarModal('modal-nuevo-repuesto')">&times;</button>
        </div>
        <div class="quick-modal-body">
            <form method="POST" id="form-repuesto" action="index.php?page=repuestos&action=create" onsubmit="return enviarModalForm(this, event, 'toast-error-repuesto')">
                <input type="hidden" name="ajax" value="1">
                <div id="toast-error-repuesto" class="toast-error"></div>
                <div class="form-group">
                    <label>Nombre</label>
                    <input type="text" name="nombre" id="rep-nombre" placeholder="Frenos de Disco, Bujías...">
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>SKU (opcional)</label>
                        <input type="text" name="sku" id="rep-sku" placeholder="Código interno o del fabricante">
                    </div>
                    <div class="form-group">
                        <label>Proveedor habitual</label>
                        <select name="proveedor_id" id="rep-proveedor">
                            <option value="">— Sin especificar —</option>
                            <?php foreach ($data['proveedores'] as $prov): ?>
                            <option value="<?= $prov['id'] ?>"><?= htmlspecialchars($prov['nombre_proveedor']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Categoría</label>
                        <select name="categoria" id="rep-categoria">
                            <?php foreach (['Frenos','Filtros','Motor','Suspensión','Eléctrico','Lubricantes','Refrigeración','Carrocería','Transmisión'] as $cat): ?>
                            <option value="<?= $cat ?>"><?= $cat ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Precio ($)</label>
                        <input type="number" name="precio" id="rep-precio" value="0" min="0" step="0.01">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Stock Actual</label>
                        <input type="number" name="stock" id="rep-stock" value="0" min="0">
                    </div>
                    <div class="form-group">
                        <label>Stock Mínimo</label>
                        <input type="number" name="stock_minimo" id="rep-stock-min" value="5" min="0">
                    </div>
                </div>
                <button type="submit" class="btn btn-primary" id="rep-submit">✅ Guardar</button>
            </form>
        </div>
    </div>
</div>

<script>
// Un solo modal para alta y edición de repuesto: sin datos = alta; con datos = edición.
function abrirRepuesto(r) {
    const form   = document.getElementById('form-repuesto');
    const titulo = document.getElementById('rep-titulo');
    const submit = document.getElementById('rep-submit');

    if (r && r.id) {
        form.action = 'index.php?page=repuestos&action=edit&id=' + r.id;
        titulo.textContent = '✏️ Editar Repuesto';
        submit.textContent = '💾 Actualizar';
        document.getElementById('rep-nombre').value     = r.nombre || '';
        document.getElementById('rep-sku').value        = r.sku || '';
        document.getElementById('rep-proveedor').value  = r.proveedor_id || '';
        document.getElementById('rep-categoria').value  = r.categoria || '';
        document.getElementById('rep-precio').value     = r.precio || 0;
        document.getElementById('rep-stock').value      = r.stock || 0;
        document.getElementById('rep-stock-min').value  = r.stock_minimo || 5;
    } else {
        form.reset();
        form.action = 'index.php?page=repuestos&action=create';
        titulo.textContent = '＋ Nuevo Repuesto';
        submit.textContent = '✅ Guardar';
    }
    abrirModal('modal-nuevo-repuesto');
    return false;
}

// Filtro 100% en el navegador: no vuelve a pedirle nada a la base de datos.
(function () {
    const buscar    = document.getElementById('filtro-buscar');
    const categoria = document.getElementById('filtro-categoria');
    const estado    = document.getElementById('filtro-estado');
    const filas     = document.querySelectorAll('#tabla-repuestos tbody tr[data-nombre]');
    const sinResultados = document.getElementById('sin-resultados');

    function aplicarFiltros() {
        const texto = buscar.value.trim().toLowerCase();
        const cat   = categoria.value;
        const est   = estado.value;
        let visibles = 0;

        filas.forEach(fila => {
            const coincideTexto = !texto || fila.dataset.nombre.includes(texto);
            const coincideCat   = !cat || fila.dataset.categoria === cat;
            const coincideEst   = !est || fila.dataset.estado === est;
            const mostrar = coincideTexto && coincideCat && coincideEst;
            fila.style.display = mostrar ? '' : 'none';
            if (mostrar) visibles++;
        });

        sinResultados.style.display = (visibles === 0 && filas.length > 0) ? 'block' : 'none';
    }

    buscar.addEventListener('input', aplicarFiltros);
    categoria.addEventListener('change', aplicarFiltros);
    estado.addEventListener('change', aplicarFiltros);
})();
</script>

<?php require_once 'compartidoCREO/views/pie_pagina.php'; ?>

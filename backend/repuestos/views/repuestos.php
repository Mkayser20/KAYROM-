<?php require_once 'compartidoCREO/views/encabezado.php'; ?>
<!-- Estilos de las imágenes (miniaturas, tarjetas, visor y cuadro para subir la foto) -->
<link rel="stylesheet" href="public/css/imagenes.css">

<?php
// Atributos que lee el visor de imágenes (doble clic sobre la imagen): dirección, título, precio y los datos de abajo
if (!function_exists('datosVisor')) {
    function datosVisor($r) {
        $sinStock = $r['stock'] == 0;
        $bajo     = $r['stock'] <= $r['stock_minimo'];
        $chips = [];
        if (!empty($r['sku'])) $chips[] = ['t' => 'SKU ' . $r['sku'], 'm' => 1];
        $chips[] = ['t' => 'Categoría: ' . $r['categoria']];
        $chips[] = ['t' => 'Stock ' . $r['stock'] . ' / ' . $r['stock_minimo'],
                    'e' => $sinStock ? 'Sin stock' : ($bajo ? 'Stock bajo' : 'En stock'),
                    'c' => $sinStock ? 'sin' : ($bajo ? 'bajo' : 'ok')];
        return 'data-ver="1"'
             . ' data-src="'    . htmlspecialchars($r['imagen_url'], ENT_QUOTES, 'UTF-8') . '"'
             . ' data-titulo="' . htmlspecialchars($r['nombre'], ENT_QUOTES, 'UTF-8') . '"'
             . ' data-precio="$' . number_format($r['precio'], 0, ',', '.') . '"'
             . ' data-chips="'  . htmlspecialchars(json_encode($chips, JSON_UNESCAPED_UNICODE), ENT_QUOTES, 'UTF-8') . '"';
    }
}

// Íconos de acción de un repuesto (agregar al pedido, editar, eliminar): se usan en la tabla y en las tarjetas
if (!function_exists('accionesRepuesto')) {
    function accionesRepuesto($r) { ?>
                        <div class="action-icons">
                            <?php if ($r['stock'] > 0): ?>
                             <a href="index.php?page=carrito&action=agregar&id=<?= $r['id'] ?>" class="action-edit" title="Agregar al pedido a proveedores">
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
                                    stock_minimo: <?= (int)$r['stock_minimo'] ?>,
                                    imagen: <?= htmlspecialchars(json_encode($r['imagen_url'] ?? ''), ENT_QUOTES, 'UTF-8') ?>
                               })">
                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                            </a>
                            <a href="index.php?page=repuestos&action=delete&id=<?= $r['id'] ?>" class="action-delete" title="Eliminar"
                               onclick="return confirmarAccion(this.href, '¿Eliminar este repuesto?')">
                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                            </a>
                        </div>
    <?php }
}
?>

<div class="page-header">
    <h1><?= icono('llave') ?> Catálogo de Repuestos e Insumos</h1>
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
    <!-- Cambiar entre la tabla y las tarjetas con imagen grande -->
    <div class="seg" role="group" aria-label="Tipo de vista">
        <button type="button" id="btn-vista-lista" class="on"><svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="8" y1="6" x2="21" y2="6"/><line x1="8" y1="12" x2="21" y2="12"/><line x1="8" y1="18" x2="21" y2="18"/><circle cx="3.5" cy="6" r="1"/><circle cx="3.5" cy="12" r="1"/><circle cx="3.5" cy="18" r="1"/></svg> Lista</button>
        <button type="button" id="btn-vista-tarjetas"><svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/></svg> Tarjetas</button>
    </div>
</div>

<div class="panel" id="vista-lista">
    <div class="panel-body">
        <table id="tabla-repuestos" class="tabla-cards">
            <thead>
                <tr>
                    <th>#</th><th class="th-img" aria-label="Imagen"></th><th>SKU</th><th>Nombre</th><th>Categoría</th><th>Stock (Actual / Mín.)</th><th>Precio</th><th>Proveedor</th><th>Estado</th><th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($data['repuestos'])): ?>
                <tr><td colspan="10" style="text-align:center;color:var(--text-muted);padding:30px;">No hay repuestos cargados</td></tr>
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
                    <td class="td-img">
                        <?php if (!empty($r['imagen_url'])): ?>
                        <!-- Doble clic sobre la miniatura: se abre la imagen en grande -->
                        <button type="button" class="thumb" <?= datosVisor($r) ?> data-tip="Doble clic para ampliar" aria-label="Ver la imagen de <?= htmlspecialchars($r['nombre']) ?> (doble clic)">
                            <img src="<?= htmlspecialchars($r['imagen_url']) ?>" alt="" loading="lazy">
                            <span class="lupa"><svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="7"/><line x1="21" y1="21" x2="16.65" y2="16.65"/><line x1="11" y1="8" x2="11" y2="14"/><line x1="8" y1="11" x2="14" y2="11"/></svg></span>
                        </button>
                        <?php else: ?>
                        <div class="thumb vacio" title="Sin imagen"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="3"/><circle cx="9" cy="9" r="1.6"/><path d="m21 15-5-5L5 21"/></svg></div>
                        <?php endif; ?>
                    </td>
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
                        <?php accionesRepuesto($r); ?>
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

<!-- Vista de tarjetas: la misma información de la tabla, con la imagen grande -->
<div id="vista-tarjetas" class="grid-rep" hidden>
    <?php foreach (($data['repuestos'] ?? []) as $r): ?>
    <?php
        $bajo = $r['stock'] <= $r['stock_minimo'];
        $sinStock = $r['stock'] == 0;
        $estadoFiltro = $sinStock ? 'sin' : ($bajo ? 'bajo' : 'ok');
        $claseStock = $sinStock ? 'sin' : ($bajo ? 'bajo' : '');
    ?>
    <article class="card-rep"
             data-nombre="<?= htmlspecialchars(mb_strtolower($r['nombre'])) ?>"
             data-categoria="<?= htmlspecialchars($r['categoria']) ?>"
             data-estado="<?= $estadoFiltro ?>">
        <?php if (!empty($r['imagen_url'])): ?>
        <button type="button" class="foto" <?= datosVisor($r) ?> aria-label="Ver la imagen de <?= htmlspecialchars($r['nombre']) ?> (doble clic)">
            <span class="stock-tag">
                <?php if ($sinStock): ?><span class="badge-status vendido">Sin Stock</span>
                <?php elseif ($bajo): ?><span class="badge-status reparacion">Stock Bajo</span>
                <?php else: ?><span class="badge-status disponible">OK</span><?php endif; ?>
            </span>
            <span class="zoom"><svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="7"/><line x1="21" y1="21" x2="16.65" y2="16.65"/><line x1="11" y1="8" x2="11" y2="14"/><line x1="8" y1="11" x2="14" y2="11"/></svg></span>
            <img src="<?= htmlspecialchars($r['imagen_url']) ?>" alt="" loading="lazy">
        </button>
        <?php else: ?>
        <div class="foto vacio">
            <span class="stock-tag">
                <?php if ($sinStock): ?><span class="badge-status vendido">Sin Stock</span>
                <?php elseif ($bajo): ?><span class="badge-status reparacion">Stock Bajo</span>
                <?php else: ?><span class="badge-status disponible">OK</span><?php endif; ?>
            </span>
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="3"/><circle cx="9" cy="9" r="1.6"/><path d="m21 15-5-5L5 21"/></svg>
            <span>Sin imagen</span>
        </div>
        <?php endif; ?>
        <div class="cuerpo">
            <div class="cat"><?= htmlspecialchars($r['categoria']) ?></div>
            <div class="nom"><?= htmlspecialchars($r['nombre']) ?></div>
            <div class="sku"><?= !empty($r['sku']) ? 'SKU ' . htmlspecialchars($r['sku']) : '' ?></div>
            <div class="fila">
                <div class="precio">$<?= number_format($r['precio'], 0, ',', '.') ?></div>
                <div class="stk">Stock <b class="<?= $claseStock ?>"><?= $r['stock'] ?></b> / <?= $r['stock_minimo'] ?></div>
            </div>
            <div class="acc"><?php accionesRepuesto($r); ?></div>
        </div>
    </article>
    <?php endforeach; ?>
</div>

<!-- Modal: alta y edición de repuesto (un solo modal para las dos cosas) -->
<div id="modal-nuevo-repuesto" class="quick-modal-overlay">
    <div class="quick-modal-box mod-ancho">
        <div class="quick-modal-header">
            <h3 id="rep-titulo">＋ Nuevo Repuesto</h3>
            <button type="button" class="quick-modal-close" onclick="cerrarModal('modal-nuevo-repuesto')">&times;</button>
        </div>
        <div class="quick-modal-body">
            <form method="POST" id="form-repuesto" action="index.php?page=repuestos&action=create" enctype="multipart/form-data" onsubmit="return enviarModalForm(this, event, 'toast-error-repuesto')">
                <input type="hidden" name="ajax" value="1">
                <div id="toast-error-repuesto" class="toast-error"></div>
                <div class="mod-img">
                    <!-- Imagen del repuesto: cuadro para soltar o elegir la foto, con vista previa -->
                    <div class="col-imagen">
                        <label class="dropz" id="dropz" for="rep-imagen">
                            <span id="dz-contenido"></span>
                            <span class="dz-arrastre">Soltá la imagen acá</span>
                        </label>
                        <input type="file" name="imagen" id="rep-imagen" accept="image/jpeg,image/png,image/webp" hidden>
                        <input type="hidden" name="quitar_imagen" id="rep-quitar-imagen" value="0">
                        <div class="dz-botones">
                            <button type="button" class="btn-img" id="dz-elegir"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg> <span id="dz-texto">Elegir imagen</span></button>
                            <button type="button" class="btn-quitar" id="dz-quitar" title="Quitar imagen" aria-label="Quitar imagen" hidden><svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg></button>
                        </div>
                        <div class="dz-nota">Arrastrá una foto acá o hacé clic.<br>JPG, PNG o WebP · máx. 2 MB</div>
                        <div class="dz-error" id="dz-error" role="alert"></div>
                    </div>
                    <div>
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
                    </div>
                </div>
                <button type="submit" class="btn btn-primary" id="rep-submit">Guardar</button>
            </form>
        </div>
    </div>
</div>

<!-- Visor de imágenes: doble clic sobre una imagen la muestra en grande -->
<script src="public/js/visor-imagenes.js"></script>

<script>
// Un solo modal para alta y edición de repuesto: sin datos = alta; con datos = edición.
function abrirRepuesto(r) {
    const form   = document.getElementById('form-repuesto');
    const titulo = document.getElementById('rep-titulo');
    const submit = document.getElementById('rep-submit');

    if (r && r.id) {
        form.action = 'index.php?page=repuestos&action=edit&id=' + r.id;
        titulo.textContent = 'Editar Repuesto';
        submit.textContent = 'Actualizar';
        document.getElementById('rep-nombre').value     = r.nombre || '';
        document.getElementById('rep-sku').value        = r.sku || '';
        document.getElementById('rep-proveedor').value  = r.proveedor_id || '';
        document.getElementById('rep-categoria').value  = r.categoria || '';
        document.getElementById('rep-precio').value     = r.precio || 0;
        document.getElementById('rep-stock').value      = r.stock || 0;
        document.getElementById('rep-stock-min').value  = r.stock_minimo || 5;
        mostrarImagenRepuesto(r.imagen || '');
    } else {
        form.reset();
        form.action = 'index.php?page=repuestos&action=create';
        titulo.textContent = '＋ Nuevo Repuesto';
        submit.textContent = 'Guardar';
        mostrarImagenRepuesto('');
    }
    mostrarErrorImagen('');
    document.getElementById('rep-quitar-imagen').value = '0';
    document.getElementById('rep-imagen').value = '';
    abrirModal('modal-nuevo-repuesto');
    return false;
}

// ----- Imagen del repuesto: cuadro para subir la foto, con vista previa y chequeos -----
const TIPOS_IMAGEN = ['image/jpeg', 'image/png', 'image/webp'];
const MAX_IMAGEN   = 2 * 1024 * 1024;   // 2 MB

// Muestra la imagen en el cuadro (o el cuadro vacío si no hay) y ajusta los botones
function mostrarImagenRepuesto(url) {
    const contenido = document.getElementById('dz-contenido');
    contenido.textContent = '';
    if (url) {
        const img = document.createElement('img');
        img.src = url;
        img.alt = 'Vista previa de la imagen';
        contenido.appendChild(img);
    } else {
        contenido.innerHTML = '<span class="dz-vacio"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg><span>Arrastrá una imagen o hacé clic para elegir</span></span>';
    }
    document.getElementById('dz-texto').textContent = url ? 'Cambiar imagen' : 'Elegir imagen';
    document.getElementById('dz-quitar').hidden = !url;
}

// Muestra (o borra, con texto vacío) el mensaje de error de la imagen
function mostrarErrorImagen(mensaje) {
    document.getElementById('dz-error').textContent = mensaje;
    document.getElementById('dropz').classList.toggle('con-error', mensaje !== '');
}

// Revisa el archivo elegido antes de mandarlo (el servidor lo vuelve a revisar). Devuelve el motivo o '' si está bien.
function revisarImagen(archivo) {
    if (TIPOS_IMAGEN.indexOf(archivo.type) === -1) return 'Formato no permitido. Usá JPG, PNG o WebP.';
    if (archivo.size > MAX_IMAGEN) {
        return 'La imagen pesa ' + (archivo.size / 1048576).toFixed(1).replace('.', ',') + ' MB y el máximo es 2 MB.';
    }
    return '';
}

// Toma el archivo elegido o soltado: si sirve, muestra la vista previa; si no, avisa y no cambia nada
function elegirImagen(archivo) {
    if (!archivo) return;
    const input = document.getElementById('rep-imagen');
    const motivo = revisarImagen(archivo);
    if (motivo) {
        input.value = '';              // no se manda el archivo que no sirve
        mostrarErrorImagen(motivo);
        return;
    }
    // el archivo queda en el campo del formulario, así viaja junto con los demás datos
    const lista = new DataTransfer();
    lista.items.add(archivo);
    input.files = lista.files;
    document.getElementById('rep-quitar-imagen').value = '0';
    mostrarErrorImagen('');
    mostrarImagenRepuesto(URL.createObjectURL(archivo));
}

(function () {
    const input = document.getElementById('rep-imagen');
    const zona  = document.getElementById('dropz');

    input.addEventListener('change', () => { const f = input.files[0]; if (f) elegirImagen(f); });
    document.getElementById('dz-elegir').addEventListener('click', () => input.click());
    document.getElementById('dz-quitar').addEventListener('click', () => {
        input.value = '';
        document.getElementById('rep-quitar-imagen').value = '1';   // el servidor borra la imagen que tenía
        mostrarErrorImagen('');
        mostrarImagenRepuesto('');
    });
    // arrastrar y soltar una foto sobre el cuadro
    ['dragenter', 'dragover'].forEach(n => zona.addEventListener(n, e => { e.preventDefault(); zona.classList.add('arrastrando'); }));
    ['dragleave', 'dragend'].forEach(n => zona.addEventListener(n, () => zona.classList.remove('arrastrando')));
    zona.addEventListener('drop', e => {
        e.preventDefault();
        zona.classList.remove('arrastrando');
        elegirImagen(e.dataTransfer.files[0]);
    });
})();

// Lista / Tarjetas: se recuerda la elección en este navegador
(function () {
    const lista    = document.getElementById('vista-lista');
    const tarjetas = document.getElementById('vista-tarjetas');
    const btnLista    = document.getElementById('btn-vista-lista');
    const btnTarjetas = document.getElementById('btn-vista-tarjetas');

    function elegirVista(vista) {
        lista.hidden    = vista !== 'lista';
        tarjetas.hidden = vista !== 'tarjetas';
        btnLista.classList.toggle('on', vista === 'lista');
        btnTarjetas.classList.toggle('on', vista === 'tarjetas');
        try { localStorage.setItem('kr_vista_repuestos', vista); } catch (e) {}
    }
    btnLista.addEventListener('click', () => elegirVista('lista'));
    btnTarjetas.addEventListener('click', () => elegirVista('tarjetas'));

    let guardada = 'lista';
    try { guardada = localStorage.getItem('kr_vista_repuestos') || 'lista'; } catch (e) {}
    elegirVista(guardada === 'tarjetas' ? 'tarjetas' : 'lista');
})();

// Filtro 100% en el navegador: no vuelve a pedirle nada a la base de datos.
(function () {
    const buscar    = document.getElementById('filtro-buscar');
    const categoria = document.getElementById('filtro-categoria');
    const estado    = document.getElementById('filtro-estado');
    const filas     = document.querySelectorAll('#tabla-repuestos tbody tr[data-nombre], #vista-tarjetas .card-rep[data-nombre]'); // filas de la tabla y tarjetas
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

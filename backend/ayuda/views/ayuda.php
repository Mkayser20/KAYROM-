<?php require_once 'compartidoCREO/views/encabezado.php'; ?>

<style>
/* Estilos propios del módulo de Ayuda (usan las mismas variables de style.css) */
.ayuda-buscador { position: relative; margin-bottom: 20px; }
.ayuda-buscador input {
    width: 100%; padding: 12px 14px 12px 40px; background: var(--bg-card); color: var(--text-primary);
    border: 1px solid var(--border); border-radius: var(--radius); font-family: var(--font-body); font-size: 14px;
}
.ayuda-buscador input:focus { outline: none; border-color: var(--accent-blue); }
.ayuda-buscador svg { position: absolute; left: 13px; top: 50%; transform: translateY(-50%); color: var(--text-muted); }

.ayuda-indice { display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 10px; margin-bottom: 24px; }
.ayuda-indice a {
    display: flex; align-items: center; gap: 10px; padding: 12px 14px; border: 1px solid var(--border);
    border-radius: var(--radius); color: var(--text-primary); text-decoration: none; font-size: 13px; font-weight: 500;
    transition: var(--transition);
}
.ayuda-indice a:hover { background: var(--bg-hover); border-color: var(--accent-blue); }
.ayuda-indice a .icono { color: var(--accent-blue-bright); flex-shrink: 0; }

.ayuda-seccion { border: 1px solid var(--border); border-radius: var(--radius); margin-bottom: 10px; background: var(--bg-card); }
.ayuda-seccion summary {
    list-style: none; cursor: pointer; display: flex; align-items: center; gap: 12px; padding: 14px 16px;
}
.ayuda-seccion summary::-webkit-details-marker { display: none; }
.ayuda-seccion summary:hover { background: var(--bg-hover); }
.ayuda-seccion summary .icono { color: var(--accent-blue-bright); flex-shrink: 0; }
.ayuda-seccion summary .ayuda-tit { font-family: var(--font-display); font-weight: 700; font-size: 15px; color: var(--text-primary); }
.ayuda-seccion summary .ayuda-res { font-size: 12.5px; color: var(--text-muted); margin-top: 2px; }
.ayuda-seccion summary .ayuda-flecha { margin-left: auto; color: var(--text-muted); transition: transform var(--transition); }
.ayuda-seccion[open] summary .ayuda-flecha { transform: rotate(90deg); }
.ayuda-seccion[open] summary { border-bottom: 1px solid var(--border); }

.ayuda-cuerpo { padding: 16px 20px 18px; font-size: 13.5px; color: var(--text-primary); line-height: 1.6; }
.ayuda-cuerpo h4 { font-size: 12px; text-transform: uppercase; letter-spacing: .05em; color: var(--text-muted); margin: 0 0 8px; font-family: var(--font-mono); }
.ayuda-cuerpo ol { margin: 0 0 14px; padding-left: 20px; }
.ayuda-cuerpo li { margin-bottom: 6px; }
.ayuda-tips { border-left: 3px solid var(--accent-orange); padding: 10px 14px; background: var(--bg-hover); border-radius: 0 var(--radius) var(--radius) 0; margin-bottom: 14px; }
.ayuda-tips ul { margin: 0; padding-left: 18px; }
.ayuda-tips li { margin-bottom: 4px; }
.ayuda-acceso { display: inline-block; font-size: 10.5px; font-family: var(--font-mono); text-transform: uppercase; letter-spacing: .04em;
    color: var(--accent-orange); border: 1px solid var(--accent-orange); border-radius: 4px; padding: 1px 6px; margin-left: 8px; vertical-align: middle; }

.ayuda-faq .ayuda-cuerpo { padding-top: 12px; }
.ayuda-vacio { display: none; text-align: center; color: var(--text-muted); padding: 30px; }
mark.ayuda-marca { background: rgba(59,130,246,.35); color: inherit; border-radius: 2px; }
</style>

<div class="page-header">
    <h1>
        <svg class="icono" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
        Centro de Ayuda
    </h1>
</div>

<!-- Buscador: filtra secciones y preguntas mientras escribís -->
<div class="ayuda-buscador">
    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
    <input type="text" id="ayuda-buscar" placeholder="¿Qué necesitás hacer? Ej: recibir pedido, stock bajo, contraseña..." autocomplete="off">
</div>

<!-- Índice de módulos -->
<div class="ayuda-indice" id="ayuda-indice">
    <?php foreach ($data['secciones'] as $clave => $s): ?>
        <a href="#ayuda-<?= $clave ?>" onclick="abrirSeccionAyuda('<?= $clave ?>')">
            <?= icono($s['icono'], 18) ?> <?= htmlspecialchars($s['titulo']) ?>
        </a>
    <?php endforeach; ?>
    <a href="#ayuda-faq" onclick="abrirSeccionAyuda('faq')">
        <?= icono('alerta', 18) ?> Preguntas frecuentes
    </a>
</div>

<!-- Guía por módulo -->
<div class="panel" style="margin-bottom:20px;">
    <div class="panel-header">
        <div class="panel-title">Guía de uso por módulo</div>
    </div>
    <div class="panel-body">
        <?php foreach ($data['secciones'] as $clave => $s): ?>
        <details class="ayuda-seccion ayuda-item" id="ayuda-<?= $clave ?>" <?= $data['moduloAbierto'] === $clave ? 'open' : '' ?>>
            <summary>
                <?= icono($s['icono'], 20) ?>
                <div>
                    <div class="ayuda-tit">
                        <?= htmlspecialchars($s['titulo']) ?>
                        <?php if ($s['acceso'] === 'admin'): ?><span class="ayuda-acceso">Solo admin</span><?php endif; ?>
                    </div>
                    <div class="ayuda-res"><?= htmlspecialchars($s['resumen']) ?></div>
                </div>
                <svg class="ayuda-flecha" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>
            </summary>
            <div class="ayuda-cuerpo">
                <h4>Paso a paso</h4>
                <ol>
                    <?php foreach ($s['pasos'] as $paso): ?>
                        <li><?= htmlspecialchars($paso) ?></li>
                    <?php endforeach; ?>
                </ol>

                <?php if (!empty($s['tips'])): ?>
                <div class="ayuda-tips">
                    <h4>Tené en cuenta</h4>
                    <ul>
                        <?php foreach ($s['tips'] as $tip): ?>
                            <li><?= htmlspecialchars($tip) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <?php endif; ?>

                <?php if (!empty($s['url'])): ?>
                    <a href="<?= htmlspecialchars($s['url']) ?>" class="btn btn-primary">Ir a <?= htmlspecialchars($s['titulo']) ?> &rsaquo;</a>
                <?php endif; ?>
            </div>
        </details>
        <?php endforeach; ?>
    </div>
</div>

<!-- Preguntas frecuentes -->
<div class="panel ayuda-faq" id="ayuda-faq">
    <div class="panel-header">
        <div class="panel-title">Preguntas frecuentes</div>
    </div>
    <div class="panel-body">
        <?php foreach ($data['preguntas'] as [$pregunta, $respuesta]): ?>
        <details class="ayuda-seccion ayuda-item">
            <summary>
                <div class="ayuda-tit" style="font-size:14px;"><?= htmlspecialchars($pregunta) ?></div>
                <svg class="ayuda-flecha" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>
            </summary>
            <div class="ayuda-cuerpo"><?= htmlspecialchars($respuesta) ?></div>
        </details>
        <?php endforeach; ?>
    </div>
</div>

<div class="ayuda-vacio" id="ayuda-vacio">
    No encontramos nada con esa búsqueda. Probá con otra palabra o consultá al administrador del sistema.
</div>

<script>
// Abre una sección del índice (y la deja abierta si ya lo estaba)
function abrirSeccionAyuda(clave) {
    const el = document.getElementById('ayuda-' + clave);
    if (el && el.tagName === 'DETAILS') el.open = true;
}

// Si se entró con ?modulo=xxx, baja hasta esa sección
document.addEventListener('DOMContentLoaded', function () {
    const abierta = document.querySelector('.ayuda-seccion[open]');
    if (abierta) abierta.scrollIntoView({ behavior: 'smooth', block: 'start' });
});

// Buscador: muestra solo las secciones/preguntas que contienen el texto y las abre.
// Ignora mayúsculas y tildes (así "vehiculo" encuentra "Vehículo").
(function () {
    const input = document.getElementById('ayuda-buscar');
    const items = Array.from(document.querySelectorAll('.ayuda-item'));
    const vacio = document.getElementById('ayuda-vacio');
    const indice = document.getElementById('ayuda-indice');
    const normalizar = t => t.toLowerCase().normalize('NFD').replace(/[̀-ͯ]/g, '');

    // guardo el texto de cada item una sola vez
    items.forEach(it => { it._texto = normalizar(it.textContent); it._abiertoAntes = it.open; });

    input.addEventListener('input', function () {
        const q = normalizar(input.value.trim());
        let visibles = 0;

        items.forEach(it => {
            const coincide = q === '' || q.split(/\s+/).every(p => it._texto.includes(p));
            it.style.display = coincide ? '' : 'none';
            it.open = q === '' ? it._abiertoAntes : coincide;
            if (coincide) visibles++;
        });

        // oculto los paneles que quedaron sin resultados
        document.querySelectorAll('.panel').forEach(p => {
            const hay = p.querySelector('.ayuda-item:not([style*="display: none"])');
            p.style.display = hay ? '' : 'none';
        });

        indice.style.display = q === '' ? '' : 'none';
        vacio.style.display  = visibles === 0 ? 'block' : 'none';
    });
})();
</script>

<?php require_once 'compartidoCREO/views/pie_pagina.php'; ?>

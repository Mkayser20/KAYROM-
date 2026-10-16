// Visor de imágenes
// Doble clic sobre cualquier elemento con el atributo data-ver abre su imagen en grande (en celular alcanza con un toque).
// Cada elemento tiene que traer estos datos:
//   data-ver      (sin valor: solo marca que se puede ampliar)
//   data-src      dirección de la imagen
//   data-titulo   nombre que se muestra abajo
//   data-precio   texto del precio (opcional)
//   data-chips    lista JSON con los datos de abajo: [{"t":"texto","m":1,"e":"texto resaltado","c":"ok|bajo|sin"}]
// Con el visor abierto: flechas para pasar de imagen, Esc para cerrar, rueda o clic para acercar y arrastrar para mover.
(function () {
    let visor = null;                 // el visor se arma la primera vez que se usa
    let items = [], indice = 0;       // imágenes que se pueden recorrer y cuál se está viendo
    let escala = 1, mx = 0, my = 0;   // zoom actual y desplazamiento de la imagen
    let focoPrevio = null;            // para devolver el foco al cerrar
    let toque = null;                 // datos del arrastre en curso

    const $ = id => document.getElementById(id);
    const esTactil = () => window.matchMedia('(pointer: coarse)').matches;

    // Arma el visor (una sola vez) y lo deja listo para usar
    function armarVisor() {
        visor = document.createElement('div');
        visor.className = 'visor';
        visor.setAttribute('role', 'dialog');
        visor.setAttribute('aria-modal', 'true');
        visor.setAttribute('aria-label', 'Imagen ampliada');
        visor.innerHTML =
            '<div class="caja">' +
                '<div class="escena" id="vi-escena">' +
                    '<span class="contador" id="vi-contador"></span>' +
                    '<button type="button" class="cerrar" id="vi-cerrar" aria-label="Cerrar"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg></button>' +
                    '<button type="button" class="flecha izq" id="vi-anterior" aria-label="Anterior"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"/></svg></button>' +
                    '<div class="lienzo" id="vi-lienzo"></div>' +
                    '<button type="button" class="flecha der" id="vi-siguiente" aria-label="Siguiente"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg></button>' +
                    '<div class="zoomctl"><button type="button" id="vi-menos" aria-label="Alejar">−</button><span id="vi-zoom">100%</span><button type="button" id="vi-mas" aria-label="Acercar">＋</button></div>' +
                '</div>' +
                '<div class="pie"><div><div class="t" id="vi-titulo"></div><div class="s" id="vi-chips"></div></div><div class="p" id="vi-precio"></div></div>' +
            '</div>';
        document.body.appendChild(visor);

        $('vi-cerrar').addEventListener('click', cerrar);
        $('vi-anterior').addEventListener('click', () => pasar(-1));
        $('vi-siguiente').addEventListener('click', () => pasar(1));
        $('vi-mas').addEventListener('click', () => cambiarZoom(escala * 1.4, 0, 0, true));
        $('vi-menos').addEventListener('click', () => cambiarZoom(escala / 1.4, 0, 0, true));
        visor.addEventListener('click', e => { if (e.target === visor) cerrar(); });   // clic en el fondo oscuro

        const escena = $('vi-escena');
        // rueda del mouse: acerca o aleja en el punto donde está el cursor
        escena.addEventListener('wheel', e => {
            e.preventDefault();
            const [cx, cy] = desdeElCentro(e);
            cambiarZoom(escala * (e.deltaY < 0 ? 1.2 : 1 / 1.2), cx, cy, false);
        }, { passive: false });
        // clic: acerca o vuelve al tamaño normal; arrastrar: mueve la imagen ampliada o pasa de imagen (celular)
        escena.addEventListener('pointerdown', e => {
            if (e.target.closest('button')) return;
            toque = { x: e.clientX, y: e.clientY, mx: mx, my: my, movio: false };
            escena.setPointerCapture(e.pointerId);
        });
        escena.addEventListener('pointermove', e => {
            if (!toque) return;
            const dx = e.clientX - toque.x, dy = e.clientY - toque.y;
            if (Math.abs(dx) + Math.abs(dy) > 5) toque.movio = true;
            if (escala > 1 && toque.movio) {
                mx = toque.mx + dx; my = toque.my + dy;
                limitar();
                $('vi-lienzo').classList.add('arrastrando');
                aplicarZoom();
            }
        });
        escena.addEventListener('pointerup', e => {
            if (!toque) return;
            const dx = e.clientX - toque.x;
            $('vi-lienzo').classList.remove('arrastrando');
            if (!toque.movio) {
                const [cx, cy] = desdeElCentro(e);
                if (escala > 1) cambiarZoom(1, 0, 0, true); else cambiarZoom(2.4, cx, cy, true);
            } else if (escala === 1 && Math.abs(dx) > 60) {
                pasar(dx < 0 ? 1 : -1);        // deslizar hacia un costado pasa de imagen
            }
            toque = null;
        });
        document.addEventListener('keydown', teclado);
    }

    // Teclas (solo funcionan con el visor abierto)
    function teclado(e) {
        if (!visor || !visor.classList.contains('abierto')) return;
        if (e.key === 'Escape') cerrar();
        else if (e.key === 'ArrowRight') pasar(1);
        else if (e.key === 'ArrowLeft') pasar(-1);
        else if (e.key === '+' || e.key === '=') cambiarZoom(escala * 1.4, 0, 0, true);
        else if (e.key === '-') cambiarZoom(escala / 1.4, 0, 0, true);
        else if (e.key === 'Tab') {            // el foco no se escapa del visor
            const botones = Array.from(visor.querySelectorAll('button:not(:disabled)'));
            const pos = botones.indexOf(document.activeElement);
            e.preventDefault();
            botones[(pos + (e.shiftKey ? -1 : 1) + botones.length) % botones.length].focus();
        }
    }

    // Abre el visor en la imagen del elemento tocado. Se recorren las imágenes que están a la vista
    // (si hay filtros aplicados, solo esas; si la tabla o las tarjetas están ocultas, no cuentan).
    function abrir(elemento) {
        if (!visor) armarVisor();
        items = Array.from(document.querySelectorAll('[data-ver]')).filter(e => e.offsetParent !== null);
        indice = Math.max(0, items.indexOf(elemento));
        focoPrevio = document.activeElement;
        visor.classList.add('abierto');
        document.body.style.overflow = 'hidden';
        mostrar();
        $('vi-cerrar').focus();
    }

    function cerrar() {
        visor.classList.remove('abierto');
        document.body.style.overflow = '';
        if (focoPrevio && focoPrevio.focus) focoPrevio.focus();
    }

    // Pasa a la imagen anterior o siguiente (al llegar al final da la vuelta)
    function pasar(paso) {
        if (items.length < 2) return;
        indice = (indice + paso + items.length) % items.length;
        mostrar();
    }

    // Muestra la imagen actual con sus datos
    function mostrar() {
        const d = items[indice].dataset;
        const lienzo = $('vi-lienzo');
        lienzo.textContent = '';
        const img = document.createElement('img');
        img.alt = d.titulo || '';
        img.src = d.src;
        img.addEventListener('error', () => { lienzo.textContent = 'No se pudo cargar la imagen.'; });
        lienzo.appendChild(img);
        reiniciarZoom();

        $('vi-contador').textContent = (indice + 1) + ' / ' + items.length;
        $('vi-titulo').textContent = d.titulo || '';
        $('vi-precio').textContent = d.precio || '';
        $('vi-anterior').disabled = $('vi-siguiente').disabled = items.length < 2;

        // datos de abajo (SKU, categoría, stock...)
        const chips = $('vi-chips');
        chips.textContent = '';
        let lista = [];
        try { lista = JSON.parse(d.chips || '[]'); } catch (e) {}
        lista.forEach(c => {
            const chip = document.createElement('span');
            chip.className = 'chip' + (c.m ? ' mono' : '');
            chip.appendChild(document.createTextNode(c.t));
            if (c.e) {
                chip.appendChild(document.createTextNode(' · '));
                const b = document.createElement('b');
                b.className = c.c || '';
                b.textContent = c.e;
                chip.appendChild(b);
            }
            chips.appendChild(chip);
        });
    }

    // ---- Zoom: escala = acercamiento, (mx, my) = desplazamiento. El punto (cx, cy) bajo el cursor queda fijo ----
    function aplicarZoom() {
        const lienzo = $('vi-lienzo');
        lienzo.style.transform = 'translate(' + mx + 'px,' + my + 'px) scale(' + escala + ')';
        lienzo.classList.toggle('zoom', escala > 1);
        $('vi-zoom').textContent = Math.round(escala * 100) + '%';
    }
    function reiniciarZoom() { escala = 1; mx = 0; my = 0; aplicarZoom(); }
    // la imagen ampliada no se puede arrastrar más allá de sus bordes
    function limitar() {
        const r = $('vi-escena').getBoundingClientRect();
        const maxX = (escala - 1) * r.width / 2, maxY = (escala - 1) * r.height / 2;
        mx = Math.max(-maxX, Math.min(maxX, mx));
        my = Math.max(-maxY, Math.min(maxY, my));
    }
    function cambiarZoom(nueva, cx, cy, conAnimacion) {
        nueva = Math.max(1, Math.min(4, nueva));
        const k = nueva / escala;
        mx = cx - (cx - mx) * k;
        my = cy - (cy - my) * k;
        escala = nueva;
        if (escala === 1) { mx = 0; my = 0; }
        limitar();
        $('vi-lienzo').style.transition = conAnimacion ? 'transform .2s ease' : 'none';
        aplicarZoom();
    }
    // posición del cursor respecto del centro de la imagen
    function desdeElCentro(e) {
        const r = $('vi-escena').getBoundingClientRect();
        return [e.clientX - (r.left + r.width / 2), e.clientY - (r.top + r.height / 2)];
    }

    // ---- Cómo se abre: doble clic (mouse), un toque (celular) o Enter (teclado) ----
    document.addEventListener('dblclick', e => {
        const el = e.target.closest('[data-ver]');
        if (el) abrir(el);
    });
    document.addEventListener('click', e => {
        const el = e.target.closest('[data-ver]');
        if (el && (esTactil() || e.detail === 0)) abrir(el);
    });
})();

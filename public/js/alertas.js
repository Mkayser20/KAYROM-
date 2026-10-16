// =====================================================================
// Alertas del sistema, hechas con SweetAlert2 (public/vendor/sweetalert2/)
// Este archivo concentra TODAS las alertas: avisos (toasts) y confirmaciones.
// El aspecto (colores, bordes, botones) está en public/css/alertas.css
//
// Si por algún motivo la librería no carga, todo sigue funcionando con
// los avisos y confirmaciones nativas del navegador (no se rompe nada).
// =====================================================================
(function () {

    // ¿Se cargó la librería? (si no, usamos las alertas nativas como respaldo)
    const hayLibreria = typeof Swal !== 'undefined';
    if (hayLibreria) {
        // esta clase le avisa al CSS que oculte los avisos viejos (.toast / .toast-error)
        document.documentElement.classList.add('swal-activo');
    }

    // Escapa caracteres especiales para que un texto nunca se interprete como HTML
    // (los títulos de SweetAlert2 aceptan HTML, así que siempre escapamos lo que viene de afuera)
    function escapar(texto) {
        return String(texto)
            .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;').replace(/'/g, '&#39;');
    }

    // Saca los emojis o símbolos del principio de un texto ("✅ Guardado" pasa a ser "Guardado"),
    // porque ahora el ícono lo pone la librería
    function limpiarTexto(texto) {
        return (texto || '').replace(/^[^\p{L}\p{N}¿¡]+/u, '').trim();
    }

    // ---------- AVISOS (toasts) ----------
    // Píldora que baja desde arriba al centro del contenido, se cierra sola y se pausa si pasás el mouse
    const Toast = hayLibreria ? Swal.mixin({
        toast: true,
        position: 'top',
        showConfirmButton: false,
        timer: 4000,
        showClass: { popup: 'kr-drop-in' },   // animación de entrada (definida en alertas.css)
        hideClass: { popup: 'kr-drop-out' },  // animación de salida
        didOpen: function (toast) {
            toast.addEventListener('mouseenter', Swal.stopTimer);
            toast.addEventListener('mouseleave', Swal.resumeTimer);
        }
    }) : null;

    // Uso: notificar('success' | 'error' | 'warning' | 'info', 'Mensaje')
    window.notificar = function (tipo, mensaje) {
        if (!Toast) { alert(mensaje); return; }
        // si la pantalla tiene barra lateral, la píldora se centra sobre el contenido (no sobre toda la ventana)
        const conBarra = document.querySelector('.sidebar') ? ' kr-con-sidebar' : '';
        Toast.fire({
            icon: tipo,
            title: escapar(mensaje),
            customClass: {
                container: 'kr-ctn-pill' + conBarra,
                popup: 'kr-pill kr-pill-' + tipo
            }
        });
    };

    // ---------- CONFIRMACIONES ----------
    // Devuelve una promesa: true si el usuario confirmó, false si canceló.
    // opciones: { peligroso: true/false, confirmar: 'texto del botón', cancelar: 'texto del botón' }
    window.preguntar = function (titulo, detalle, opciones) {
        opciones = opciones || {};
        const peligroso = !!opciones.peligroso;

        if (!hayLibreria) {
            return Promise.resolve(window.confirm(titulo + (detalle ? '\n' + detalle : '')));
        }
        return Swal.fire({
            icon: peligroso ? 'warning' : 'question',
            title: escapar(titulo),
            text: detalle || '',
            showCancelButton: true,
            confirmButtonText: opciones.confirmar || 'Confirmar',
            cancelButtonText: opciones.cancelar || 'Cancelar',
            reverseButtons: true,        // Cancelar a la izquierda, Confirmar a la derecha
            focusCancel: peligroso,      // en acciones peligrosas, el foco arranca en "Cancelar"
            buttonsStyling: false,       // los botones se estilan con nuestras clases (alertas.css)
            heightAuto: false,           // no tocar el alto de la página al abrir
            customClass: {
                popup: 'kr-popup',
                title: 'kr-title',
                htmlContainer: 'kr-text',
                actions: 'kr-actions',
                confirmButton: 'kr-btn ' + (peligroso ? 'kr-btn-danger' : 'kr-btn-primary'),
                cancelButton: 'kr-btn kr-btn-ghost'
            }
        }).then(function (resultado) { return resultado.isConfirmed; });
    };

    // Reemplaza al modal viejo. Se usa igual que antes en los links:
    //   onclick="return confirmarAccion(this.href, '¿Eliminar este vehículo?')"
    // Mira el texto del mensaje para decidir cómo mostrarse (rojo si es una baja, etc.)
    // opciones (todas opcionales): { titulo: '...', detalle: '...', confirmar: 'texto del botón' }
    window.confirmarAccion = function (url, mensaje, opciones) {
        opciones = opciones || {};
        const esBaja   = /eliminar|borrar|quitar/i.test(mensaje);
        const sinVuelta = /eliminar|borrar/i.test(mensaje);
        const esSesion = /sesi[oó]n/i.test(mensaje);
        const largo    = mensaje.length > 60; // los mensajes largos van como detalle, no como título

        // en los mensajes largos el "¿Confirmás?" del final sobra (el título ya lo pregunta), lo saco
        const mensajeSinPregunta = mensaje.replace(/\s*¿Confirm[aá]s\?\s*$/i, '');
        const titulo  = opciones.titulo || (largo ? '¿Confirmás la acción?' : mensaje);
        // si el mensaje no quedó como título (porque es largo o porque se pasó un título propio), va como detalle
        const mensajeEsDetalle = largo || !!opciones.titulo;
        const detalle = opciones.detalle || (mensajeEsDetalle ? mensajeSinPregunta : (sinVuelta ? 'Esta acción no se puede deshacer.' : ''));
        const textoBoton = opciones.confirmar ||
            (esBaja ? (sinVuelta ? 'Sí, eliminar' : 'Sí, quitar') : (esSesion ? 'Cerrar sesión' : 'Confirmar'));

        preguntar(titulo, detalle, { peligroso: esBaja, confirmar: textoBoton }).then(function (ok) {
            if (ok) { window.location.href = url; }
        });
        return false; // evita que el link navegue solo; navegamos recién si se confirmó
    };

    // ---------- PUENTE CON LOS AVISOS VIEJOS ----------
    // El sistema ya tenía avisos escritos en divs (.toast para éxitos y .toast-error para errores).
    // En vez de reescribir cada vista, acá los detectamos y los mostramos con la librería.
    if (hayLibreria) {
        function mostrarYLimpiar(el, tipo) {
            const texto = limpiarTexto(el.textContent);
            if (texto) { notificar(tipo, texto); }
            el.textContent = ''; // se vacía para no repetirlo
        }

        document.addEventListener('DOMContentLoaded', function () {
            // 1) avisos que ya vienen escritos desde el servidor (ej: "Registro actualizado", errores por URL)
            document.querySelectorAll('.toast').forEach(function (el) { mostrarYLimpiar(el, 'success'); });
            document.querySelectorAll('.toast-error').forEach(function (el) { mostrarYLimpiar(el, 'error'); });

            // 2) avisos que el JS de cada formulario completa después (mostrarError, errores de AJAX)
            document.querySelectorAll('.toast-error').forEach(function (el) {
                new MutationObserver(function () {
                    if (el.textContent.trim() !== '') { mostrarYLimpiar(el, 'error'); }
                }).observe(el, { childList: true, characterData: true, subtree: true });
            });
        });
    }

})();

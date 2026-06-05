<?php
require_once 'views/encabezado.php';
?>

<div class="page-header">
    <h1>＋ Nuevo Movimiento</h1>
    <a href="index.php?page=movimientos" class="btn btn-ghost">← Volver</a>
</div>

<div class="form-card">
    <form method="POST" action="index.php?page=movimientos&action=create">
        <div id="toast-error" class="toast-error"></div>
        <div class="form-row">
            <div class="form-group">
                <label>Tipo de Movimiento</label>
                <select name="tipo">
                    <option value="Entrada">📥 Entrada</option>
                    <option value="Salida">📤 Salida</option>
                    <option value="Ajuste">🔁 Ajuste</option>
                </select>
            </div>
            <div class="form-group">
                <label>Cantidad</label>
                <input type="number" name="cantidad" value="0" min="0">
            </div>
        </div>
        <div class="form-group">
            <label>Descripción</label>
            <input type="text" name="descripcion" placeholder="Ej: Entrada 10 Filtros de Aceite...">
        </div>
        <button type="submit" class="btn btn-primary">✅ Registrar Movimiento</button>

    <script>
        function mostrarError(mensaje) {
            const toast = document.getElementById('toast-error');
            toast.textContent = mensaje;
            setTimeout(() => { toast.textContent = ''; }, 3500);
        }

        document.querySelector('form').addEventListener('submit', function(e) {
            const inputs = document.querySelectorAll('form input[name], form select[name]');
            for (const input of inputs) {
                if (!input.value.trim()) {
                    e.preventDefault();
                    const label = input.closest('.form-group')?.querySelector('label')?.textContent.replace('*', '').trim() || 'Este campo';
                    mostrarError('Completá: ' + label);
                    return;
                }
            }
        });
    </script>
    </form>
</div>

<?php require_once 'views/pie_pagina.php'; ?>

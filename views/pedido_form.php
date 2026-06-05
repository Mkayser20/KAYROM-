<?php
require_once 'views/encabezado.php';
?>
<div class="page-header">
    <h1>＋ Nuevo Pedido</h1>
    <a href="index.php?page=pedidos" class="btn btn-ghost">← Volver</a>
</div>
<div class="form-card">
    <form method="POST" action="index.php?page=pedidos&action=create">
        <div id="toast-error" class="toast-error"></div>
        <div class="form-row">
            <div class="form-group">
                <label>Estado del Pedido</label>
                <select name="estado_pedido">
                    <option value="Pendiente">📋 Pendiente</option>
                    <option value="En proceso">🔄 En proceso</option>
                    <option value="Entregado">✅ Entregado</option>
                    <option value="Cancelado">❌ Cancelado</option>
                </select>
            </div>
            <div class="form-group">
                <label>Cantidad</label>
                <input type="number" name="cantidad" value="1" min="1">
            </div>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label>Responsable</label>
                <input type="text" name="responsable_pedido" placeholder="Nombre del responsable">
            </div>
            <div class="form-group">
                <label>Número Único</label>
                <input type="number" name="numero_unico" value="<?= rand(1000,9999) ?>">
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
            <label>Detalle del Pedido</label>
            <input type="text" name="detalle_pedido" placeholder="Descripción del pedido">
        </div>
        
        <button type="submit" class="btn btn-primary">✅ Registrar Pedido</button>

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

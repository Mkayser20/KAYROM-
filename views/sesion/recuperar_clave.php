<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Olvidé mi contraseña — Kayrom</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="public/css/auth.css">
</head>
<body>

<div class="auth-card">

    <div class="auth-logo">
        <img src="public/img/dpv.png" alt="Logo" onerror="this.style.display='none'">
    </div>

    <hr class="auth-divider">

    <h2 class="auth-title">Olvidé mi contraseña</h2>
    <p class="auth-subtitle">Ingresá tu email y te enviamos un enlace para restablecerla.</p>

    <?php if (!empty($data['error'])): ?>
        <div class="auth-alert error"><?= htmlspecialchars($data['error']) ?></div>
    <?php endif; ?>

    <?php if (!empty($data['success'])): ?>
        <div class="auth-alert success"><?= $data['success'] /* contiene HTML seguro generado internamente */ ?></div>
    <?php endif; ?>

    <?php if (empty($data['success'])): ?>
    <form method="POST" action="index.php?page=recuperar_clave" class="auth-form">
        <div id="toast-error" class="toast-error"></div>

        <div class="field-group">
            <div class="field-wrap">
                <span class="field-icon">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/>
                        <polyline points="22,6 12,13 2,6"/>
                    </svg>
                </span>
                <input
<<<<<<< HEAD
                    type="text"
=======
                    type="email"
>>>>>>> fea3808941fb476d8d3bbd3da4140c9ba8ce84cc
                    name="email"
                    placeholder="Correo electrónico"
                    value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                    autocomplete="email"
                    
                >
            </div>
        </div>

        <button type="submit" class="auth-btn">Enviar enlace de recuperación</button>

        <script>
        function mostrarError(mensaje) {
            const toast = document.getElementById('toast-error');
            toast.textContent = mensaje;
            setTimeout(() => { toast.textContent = ''; }, 3500);
        }

        document.querySelector('form').addEventListener('submit', function(e) {
            const email = document.querySelector('input[name="email"]').value.trim();
<<<<<<< HEAD
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!email) {
                e.preventDefault();
                mostrarError('Ingresá tu correo electrónico');
                return;
            }
            if (!emailRegex.test(email)) {
                e.preventDefault();
                mostrarError('Ingresá un correo electrónico válido');
=======
            if (!email) {
                e.preventDefault();
                mostrarError('Ingresá tu correo electrónico');
>>>>>>> fea3808941fb476d8d3bbd3da4140c9ba8ce84cc
            }
        });
        </script>

    </form>
    <?php endif; ?>

    <p class="auth-switch">
        <a href="index.php?page=iniciar_sesion">← Volver al inicio de sesión</a>
    </p>

</div>

</body>
</html>

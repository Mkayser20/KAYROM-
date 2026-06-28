<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Inicio de Sesión — Kayrom</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="public/css/auth.css">
</head>
<body>

<div class="auth-card">

    <div class="auth-logo">
        <img src="public/img/dpv.png" alt="Logo"
             onerror="this.style.display='none'">
    </div>

    <hr class="auth-divider">

    <h2 class="auth-title">Inicio de Sesión</h2>

    <?php if (!empty($data['error'])): ?>
    <div class="auth-alert error"><?= htmlspecialchars($data['error']) ?></div>
    <?php endif; ?>

    <?php if (($_GET['msg'] ?? '') === 'password_changed'): ?>
    <div class="auth-alert success">✓ Contraseña cambiada exitosamente. Ya podés iniciar sesión.</div>
    <?php endif; ?>

    <form method="POST" action="index.php?page=login&action=login" class="auth-form">

        <!-- Email -->
        <div class="field-group">
            <div class="field-wrap">
                <span class="field-icon">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
                </span>
                <input
                    type="email"
                    name="email"
                    placeholder="Correo electrónico"
                    value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                    autocomplete="email"
                    required
                >
            </div>
        </div>

        <!-- Contraseña -->
        <div class="field-group">
            <div class="field-wrap pass-wrap">
                <span class="field-icon">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                </span>
                <input
                    type="password"
                    id="pass_login"
                    name="contrasena_usuario"
                    placeholder="Contraseña"
                    autocomplete="current-password"
                    required
                >
                <button type="button" class="toggle-pass" onclick="togglePass('pass_login', this)">
                    <svg id="eye_login" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                </button>
            </div>
        </div>

        <a href="index.php?page=forgot_password" class="forgot-link">Olvidé mi contraseña</a>

        <button type="submit" class="auth-btn">Iniciar Sesión</button>

    </form>

    <p class="auth-switch">
        ¿No tienes una cuenta?
        <a href="index.php?page=register&action=register">Crear cuenta</a>
    </p>

</div>

<script>
function togglePass(id, btn) {
    const input = document.getElementById(id);
    const isPass = input.type === 'password';
    input.type = isPass ? 'text' : 'password';
    btn.innerHTML = isPass
        ? '<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94"/><path d="M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19"/><line x1="1" y1="1" x2="23" y2="23"/></svg>'
        : '<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>';
}
</script>
</body>
</html>

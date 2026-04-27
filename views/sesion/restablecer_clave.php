<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Restablecer contraseña — Kayrom</title>
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

    <h2 class="auth-title">Nueva contraseña</h2>

    <?php if (!empty($data['error'])): ?>
        <div class="auth-alert error"><?= htmlspecialchars($data['error']) ?></div>
        <p class="auth-switch" style="margin-top:20px;">
            <a href="index.php?page=recuperar_clave">Solicitar un nuevo enlace</a>
            &nbsp;·&nbsp;
            <a href="index.php?page=iniciar_sesion">Volver al login</a>
        </p>
    <?php elseif ($data['usuario']): ?>

        <p class="auth-subtitle">
            Hola <strong style="color:#e2e8f0;"><?= htmlspecialchars($data['usuario']['nombre']) ?></strong>,
            elegí tu nueva contraseña.
        </p>

        <form method="POST"
              action="index.php?page=restablecer_clave&token=<?= urlencode($data['token']) ?>"
              class="auth-form"
              id="resetForm">

            <!-- Nueva contraseña -->
            <div class="field-group">
                <div class="field-wrap pass-wrap">
                    <span class="field-icon">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                            <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                        </svg>
                    </span>
                    <input
                        type="password"
                        id="nueva_password"
                        name="nueva_password"
                        placeholder="Nueva contraseña"
                        minlength="6"
                        required
                    >
                    <button type="button" class="toggle-pass" onclick="togglePass('nueva_password', this)">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                            <circle cx="12" cy="12" r="3"/>
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Confirmar contraseña -->
            <div class="field-group">
                <div class="field-wrap pass-wrap">
                    <span class="field-icon">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                            <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                        </svg>
                    </span>
                    <input
                        type="password"
                        id="confirmar_password"
                        name="confirmar_password"
                        placeholder="Confirmar contraseña"
                        minlength="6"
                        required
                    >
                    <button type="button" class="toggle-pass" onclick="togglePass('confirmar_password', this)">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                            <circle cx="12" cy="12" r="3"/>
                        </svg>
                    </button>
                </div>
            </div>

            <button type="submit" class="auth-btn">Cambiar contraseña</button>

        </form>

        <p class="auth-switch">
            <a href="index.php?page=iniciar_sesion">← Volver al inicio de sesión</a>
        </p>

    <?php endif; ?>

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

// Validación client-side antes del submit
document.getElementById('resetForm')?.addEventListener('submit', function(e) {
    const p1 = document.getElementById('nueva_password').value;
    const p2 = document.getElementById('confirmar_password').value;
    if (p1 !== p2) {
        e.preventDefault();
        alert('Las contraseñas no coinciden.');
    }
});
</script>

</body>
</html>

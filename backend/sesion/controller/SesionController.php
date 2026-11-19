<?php

class SesionController {
    private $model;

    public function __construct() {
        $this->model = new UsuarioModel();
    }

    //valida mail y contraseña, inicia sesión y guarda datos en $_SESSION
    public function iniciarSesion() {
        $error = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = trim($_POST['email'] ?? '');
            $pass  = $_POST['contrasena_usuario'] ?? '';

            if ($email === '' || $pass === '') {
                $error = 'Completá todos los campos.';
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $error = 'Ingresá un correo electrónico válido.';
            } elseif ($this->model->estaBloqueado($email, $_SERVER['REMOTE_ADDR'] ?? '')) {
                // demasiados intentos fallidos seguidos: se frena un rato
                $error = 'Demasiados intentos fallidos. Esperá ' . UsuarioModel::MINUTOS_BLOQUEO . ' minutos y probá de nuevo.';
            } else {
                $user = $this->model->findByEmail($email);
                if ($user && password_verify($pass, $user['contrasena_usuario'])) {
                    $this->model->limpiarIntentos($email);
                    session_regenerate_id(true);
                    $_SESSION['usuario_id'] = $user['id'];
                    $_SESSION['nombre_usuario'] = $user['nombre_usuario'];
                    $_SESSION['nombre']     = $user['nombre'] ?? $user['nombre_usuario'];
                    $_SESSION['email']      = $user['email'];
                    $_SESSION['rol']        = $user['rol_nombre'] ?? 'empleado';
                    header('Location: index.php?page=inicio');
                    exit;
                } else {
                    $this->model->registrarIntentoFallido($email, $_SERVER['REMOTE_ADDR'] ?? '');
                    $error = 'Correo o contraseña incorrectos.';
                }
            }
        }

        $data = ['error' => $error];
        require_once 'backend/sesion/views/iniciar_sesion.php';
    }

    //valida datos, chequea duplicados, registra el usuario y su persona, el rol siempre se asigna como empleado
    public function registrarUsuario() {
        $error   = '';
        $success = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
           $nombre  = trim($_POST['nombre']          ?? '');
            $apellido = trim($_POST['apellido']       ?? '');
            $domicilio = trim($_POST['domicilio']     ?? '');
            $dni      = trim($_POST['dni']              ?? '');
            $telefono = trim($_POST['telefono_persona'] ?? '');
            $usuario = trim($_POST['nombre_usuario']  ?? '');
            $email   = trim($_POST['email']           ?? '');
            $rol     = UsuarioModel::normalizarRol($_POST['rol'] ?? 'empleado'); // "Admin" o "ADMIN" quedan como "admin"
            $activo  = (int)($_POST['activo'] ?? 1);
            $modulos = $_POST['modulos'] ?? [];
            $pass    = $_POST['contrasena_usuario']   ?? '';
            $pass2   = $_POST['contrasene_confirm']   ?? '';

            if (empty($nombre) || empty($email) || empty($pass) || empty($usuario)) {
                $error = 'Completá todos los campos obligatorios.';
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $error = 'Ingresá un correo electrónico válido.';
            } elseif ($pass !== $pass2) {
                $error = 'Las contraseñas no coinciden.';
            } elseif (!preg_match('/^(?=.*[A-Z])(?=.*[^a-zA-Z0-9]).{8,}$/', $pass)) {
                $error = 'La contraseña debe tener al menos 8 caracteres, una mayúscula y un carácter especial.';
            } elseif ($this->model->existeEmail($email)) {
                $error = "El correo '$email' ya está registrado.";
            } elseif ($this->model->existeUsuario($usuario)) {
                $error = "El usuario '$usuario' ya está en uso.";
            } elseif ($dni !== '' && $this->model->existeDni($dni)) {
                $error = "El DNI '$dni' ya está registrado.";
            } elseif ($telefono !== '' && $this->model->existeTelefono($telefono)) {
                $error = "El teléfono '$telefono' ya está registrado.";
            } else {
                    $datos = [
                        'nombre'             => $nombre,
                        'apellido'           => $apellido,
                        'domicilio'          => $domicilio,
                        'dni'                => $dni,
                        'telefono_persona'   => $telefono,
                        'nombre_usuario'     => $usuario,
                        'email'              => $email,
                        'contrasena_usuario' => $pass,
                        'rol'                => $rol,
                        'activo'             => $activo,
                    ];
                // create() ahora devuelve el id del usuario nuevo (o false si falló)
                $nuevoId = $this->model->create($datos);
                if ($nuevoId) {
                    // El admin tiene todos los módulos, los demas se asignan por el mismo
                    if ($rol !== 'admin') {
                        require_once 'compartidoCREO/models/PermisoModel.php';
                        $permisoModel = new PermisoModel();
                        $permisoModel->setForUsuario($nuevoId, $modulos);
                    }
                    $success = '¡Usuario creado con éxito!';
                    $_POST = [];
                } else {
                    $error = 'Error al registrar. Revisá los datos e intentá de nuevo.';
                  }
             }
        }

        $data = [
            'error'   => $error,
            'success' => $success,
        ];
        require_once 'backend/sesion/views/registrar_usuario.php';
    }

     public function cerrarSesion() {
        // se vacían los datos y se borra la cookie de la sesión, no solo se destruye
        $_SESSION = [];
        $p = session_get_cookie_params();
        setcookie(session_name(), '', time() - 3600, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
        session_destroy();
        header('Location: index.php?page=iniciar_sesion');
        exit;
    }

    //recuperar cntraseña
    public function recuperarClave() {
        $error   = '';
        $success = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = trim($_POST['email'] ?? '');

            if ($email === '') {
                $error = 'Ingresá tu correo electrónico.';
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $error = 'El correo no tiene un formato válido.';
            } else {
                $resultado = $this->model->setTokenRecuperacion($email);

                if ($resultado) {
                    $enviado = $this->enviarEmailRecuperacion($email, $resultado['nombre'], $resultado['token']);
                    if (!$enviado && APP_ENV !== 'production') {
                        // en desarrollo se muestra el problema para poder arreglar la configuración del mail
                        $error = 'No se pudo enviar el email. Revisá MAIL_USER y MAIL_PASS en el archivo .env (el detalle quedó en el log de PHP).';
                    }
                }
                // Siempre el mismo mensaje, exista o no el correo: así nadie puede averiguar qué emails están registrados
                if ($error === '') {
                    $success = 'Si ese correo está registrado, recibirás un enlace para restablecer tu contraseña. Revisá tu bandeja (y spam).';
                }
            }
        }

        $data = ['error' => $error, 'success' => $success];
        require_once 'backend/sesion/views/recuperar_clave.php';
    }

    public function restablecerClave() {
        $token  = $_GET['token'] ?? '';
        if (!is_string($token)) $token = ''; // si mandan un array en vez de texto, se toma como vacío
        $error  = '';
        $usuario = null;

        if ($token === '') {
            $error = 'Token inválido.';
        } else {
            $usuario = $this->model->findByToken($token);
            if (!$usuario) {
                $error = 'El enlace ya no es válido o ha expirado. Solicitá uno nuevo.';
            }
        }
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && $usuario) {
            $nueva    = $_POST['nueva_password']     ?? '';
            $confirma = $_POST['confirmar_password'] ?? '';

            if ($nueva !== $confirma) {
                $error = 'Las contraseñas no coinciden.';
            } elseif (!preg_match('/^(?=.*[A-Z])(?=.*[^a-zA-Z0-9]).{8,}$/', $nueva)) {
                $error = 'La contraseña debe tener al menos 8 caracteres, una letra mayúscula y un carácter especial.';
            } else {
                $this->model->resetPassword((int)$usuario['id'], $nueva);
                header('Location: index.php?page=iniciar_sesion&msg=password_changed');
                exit;
            }
        }

        $data = ['error' => $error, 'usuario' => $usuario, 'token' => $token];
        require_once 'backend/sesion/views/restablecer_clave.php';
    }


    private function enviarEmailRecuperacion(string $email, string $nombre, string $token): bool {
        require_once 'vendor/autoload.php';

        // Sin usuario o contraseña de mail no hay nada que probar: se avisa en el log con un mensaje claro
        if (MAIL_USER === '' || MAIL_PASS === '') {
            error_log('[KAYROM Mail] Faltan MAIL_USER o MAIL_PASS en el archivo .env');
            return false;
        }

        $mail = new \PHPMailer\PHPMailer\PHPMailer(true);
        try {
            $mail->isSMTP();
            // Los datos del mail salen del archivo .env (config.php los carga en estas constantes).
            // Antes estaban vacíos acá ('') y por eso el email de recuperación nunca se enviaba.
            $mail->Host       = MAIL_HOST ?: 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = MAIL_USER;
            $mail->Password   = MAIL_PASS;
            // tls = STARTTLS (587), ssl = SMTPS (465), none = sin cifrado (solo pruebas locales)
            if (MAIL_SECURE === 'ssl') {
                $mail->SMTPSecure = \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_SMTPS;
            } elseif (MAIL_SECURE === 'none') {
                $mail->SMTPSecure  = '';
                $mail->SMTPAutoTLS = false;
            } else {
                $mail->SMTPSecure = \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
            }
            $mail->Port       = (int)MAIL_PORT;
            $mail->Timeout    = 15;            // si Gmail no responde, no se cuelga la página
            $mail->CharSet    = 'UTF-8';
            // En desarrollo la conversación completa con el servidor SMTP queda en el log de PHP
            if (APP_ENV !== 'production') {
                $mail->SMTPDebug   = \PHPMailer\PHPMailer\SMTP::DEBUG_SERVER;
                $mail->Debugoutput = 'error_log';
            }

            $mail->setFrom(MAIL_USER, 'Kayrom — Sistema');
            $mail->addAddress($email, $nombre);

            // El link usa APP_URL del .env si está configurado. Si no, se arma con el servidor actual.
            // (Usar siempre HTTP_HOST permitiría que alguien manipule el dominio del link.)
            $base = APP_URL !== ''
                ? rtrim(APP_URL, '/')
                : (isset($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . rtrim(dirname($_SERVER['PHP_SELF']), '/\\');
            $link = $base . '/index.php?page=restablecer_clave&token=' . urlencode($token);
            $nombre = htmlspecialchars($nombre, ENT_QUOTES, 'UTF-8'); // el nombre va dentro del HTML del mail

            $mail->isHTML(true);
            $mail->Subject = 'Restablecer contraseña — Kayrom';
            $mail->Body    = "
                <div style='font-family:Inter,sans-serif;background:#0d0d1a;padding:40px;border-radius:12px;max-width:500px;margin:auto;'>
                    <h2 style='color:#f1f5f9;margin-bottom:12px;'>Restablecer contraseña</h2>
                    <p style='color:#94a3b8;margin-bottom:24px;'>Hola <strong style='color:#e2e8f0;'>{$nombre}</strong>, recibimos una solicitud para restablecer tu contraseña.</p>
                    <a href='{$link}' style='display:inline-block;background:#2563eb;color:#fff;padding:12px 28px;border-radius:8px;text-decoration:none;font-weight:700;'>Restablecer contraseña</a>
                    <p style='color:#4b5563;margin-top:24px;font-size:13px;'>O copiá este enlace en tu navegador:<br><span style='color:#6366f1;'>{$link}</span></p>
                    <p style='color:#ef4444;margin-top:16px;font-size:13px;'><strong>Este enlace expira en 1 hora.</strong></p>
                    <p style='color:#4b5563;font-size:12px;margin-top:8px;'>Si no solicitaste este cambio, ignorá este email.</p>
                </div>
            ";
            $mail->AltBody = "Restablecer contraseña SISTEMA-KAYROM: {$link} (expira en 1 hora)";

            $mail->send();
            return true;
        } catch (\Throwable $e) {
            // ErrorInfo trae el motivo que dio el servidor (usuario/clave rechazados, no conecta, etc.)
            error_log('[KAYROM Mail] No se pudo enviar el email de recuperación: ' . $mail->ErrorInfo . ' | ' . $e->getMessage());
            return false;
        }
    }
}
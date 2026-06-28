<?php
//Controlador de autenticación (versión alternativa simplificada de SesionController)
//Maneja login, registro y recuperación de contraseña
class AuthController {
    private $model; //modelo de usuario

    public function __construct() {
        $this->model = new UsuarioModel();
    }

    //iniciar sesión con email y contraseña
    public function login() {
        $error = '';

        //si es formulario POST, validar credenciales
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = trim($_POST['email'] ?? '');
            $pass  = $_POST['contrasena_usuario'] ?? '';

            //validación 1: campos no vacíos
            if ($email === '' || $pass === '') {
                $error = 'Completá todos los campos.';
            } 
            //validación 2: email válido
            elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $error = 'Ingresá un correo electrónico válido.';
            } else {
                //buscar usuario y verificar contraseña
                $user = $this->model->findByEmail($email);
                if ($user && password_verify($pass, $user['contrasena_usuario'])) {
                    //credenciales correctas: guardar datos en sesión
                    $_SESSION['usuario_id'] = $user['id'];
                    $_SESSION['nombre_usuario'] = $user['nombre_usuario'];
                    $_SESSION['nombre']     = $user['nombre'] ?? $user['nombre_usuario'];
                    $_SESSION['email']      = $user['email'];
                    $_SESSION['rol']        = $user['rol_nombre'] ?? 'Usuario';

                    //redirigir al dashboard
                    header('Location: index.php?page=dashboard');
                    exit;
                } else {
                    $error = 'Correo o contraseña incorrectos.';
                }
            }
        }

        //cargar vista de login
        $data = ['error' => $error];
        require_once 'backend/sesion/views/auth/login.php';
    }

    //registrar nuevo usuario
    public function register() {
        $error   = '';
        $success = '';

        //si es formulario POST, procesar registro
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $nombre  = trim($_POST['nombre']          ?? '');
            $usuario = trim($_POST['nombre_usuario']  ?? '');
            $email   = trim($_POST['email']           ?? '');
            $pass    = $_POST['contrasena_usuario']   ?? '';
            $pass2   = $_POST['contrasene_confirm']   ?? '';

            //validar campos obligatorios
            if (empty($nombre) || empty($email) || empty($pass) || empty($usuario)) {
                $error = 'Completá todos los campos obligatorios.';
            } 
            //validar formato de email
            elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $error = 'Ingresá un correo electrónico válido.';
            } 
            //validar que las contraseñas coincidan
            elseif ($pass !== $pass2) {
                $error = 'Las contraseñas no coinciden.';
            } 
            //validar longitud mínima de contraseña
            elseif (strlen($pass) < 6) {
                $error = 'La contraseña debe tener al menos 6 caracteres.';
            } 
            //validar que el email no esté registrado
            elseif ($this->model->existeEmail($email)) {
                $error = "El correo '$email' ya está registrado.";
            } 
            //validar que el usuario no esté en uso
            elseif ($this->model->existeUsuario($usuario)) {
                $error = "El usuario '$usuario' ya está en uso.";
            } else {
                //todos los datos válidos: crear cuenta
                if ($this->model->create($_POST)) {
                    $success = '¡Cuenta creada con éxito! Ya podés iniciar sesión.';
                    $_POST = [];
                } else {
                    $error = 'Error al registrar. Revisá los datos e intentá de nuevo.';
                }
            }
        }

        //cargar vista de registro con opciones de roles
        $data = [
            'error'   => $error,
            'success' => $success,
            'roles'   => $this->model->getRoles(),
        ];
        require_once 'backend/sesion/views/auth/register.php';
    }

    //cerrar sesión del usuario
    public function logout() {
        //destruir sesión
        session_destroy();
        //redirigir a login
        header('Location: index.php?page=login');
        exit;
    }

    //recuperación de contraseña (pendiente de implementación completa)
    public function forgotPassword() {
        $error   = '';
        $success = '';

        //si es POST, procesar solicitud de recuperación
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = trim($_POST['email'] ?? '');

            //validar email vacío
            if ($email === '') {
                $error = 'Ingresá tu correo electrónico.';
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $error = 'El correo no tiene un formato válido.';
            } else {
                $resultado = $this->model->setTokenRecuperacion($email);

                if ($resultado) {
                    $enviado = $this->enviarEmailRecuperacion($email, $resultado['nombre'], $resultado['token']);
                    if ($enviado) {
                        $success = 'Te enviamos un enlace a <strong>' . htmlspecialchars($email) . '</strong>. Revisá tu bandeja (y spam).';
                    } else {
                        $error = 'No se pudo enviar el email. Intentá más tarde.';
                    }
                } else {
                    // Mensaje genérico: no revelar si el email existe o no
                    $success = 'Si ese correo está registrado, recibirás un enlace para restablecer tu contraseña.';
                }
            }
        }

        $data = ['error' => $error, 'success' => $success];
        require_once 'backend/sesion/views/auth/forgot_password.php';
    }

    public function resetPassword() {
        $token  = $_GET['token'] ?? '';
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
            } elseif (strlen($nueva) < 6) {
                $error = 'La contraseña debe tener al menos 6 caracteres.';
            } else {
                $this->model->resetPassword((int)$usuario['id'], $nueva);
                header('Location: index.php?page=login&msg=password_changed');
                exit;
            }
        }

        $data = ['error' => $error, 'usuario' => $usuario, 'token' => $token];
        require_once 'backend/sesion/views/auth/reset_password.php';
    }

    // ── Helper privado: envío de email ─────────────────────────

    private function enviarEmailRecuperacion(string $email, string $nombre, string $token): bool {
        require_once 'vendor/autoload.php';

        $mail = new \PHPMailer\PHPMailer\PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'maquitosk05@gmail.com';   // ← tu cuenta Gmail
            $mail->Password   = 'qgeo bdds cmui rlyn';     // ← app password de Gmail
            $mail->SMTPSecure = \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;
            $mail->CharSet    = 'UTF-8';

            $mail->setFrom('maquitosk05@gmail.com', 'Kayrom — Sistema');
            $mail->addAddress($email, $nombre);

            $link = (isset($_SERVER['HTTPS']) ? 'https' : 'http')
                  . '://' . $_SERVER['HTTP_HOST']
                  . dirname($_SERVER['PHP_SELF'])
                  . '/index.php?page=reset_password&token=' . $token;

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
            $mail->AltBody = "Restablecer contraseña Kayrom: {$link} (expira en 1 hora)";

            $mail->send();
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
}

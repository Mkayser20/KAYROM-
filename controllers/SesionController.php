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
            } else {
                $user = $this->model->findByEmail($email);
                if ($user && password_verify($pass, $user['contrasena_usuario'])) {
                    $_SESSION['usuario_id'] = $user['id'];
                    $_SESSION['nombre_usuario'] = $user['nombre_usuario'];
                    $_SESSION['nombre']     = $user['nombre'] ?? $user['nombre_usuario'];
                    $_SESSION['email']      = $user['email'];
                    $_SESSION['rol']        = $user['rol_nombre'] ?? 'Usuario';
                    header('Location: index.php?page=inicio');
                    exit;
                } else {
                    $error = 'Correo o contraseña incorrectos.';
                }
            }
        }

        $data = ['error' => $error];
        require_once 'views/sesion/iniciar_sesion.php';
    }

    //valida datos, chequea duplicados, registra el usuario y su persona, el rol siempre se asigna como empleadp
    public function registrarUsuario() {
        $error   = '';
        $success = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $nombre  = trim($_POST['nombre']          ?? '');
            $apellido = trim($_POST['apellido']       ?? '');
            $domicilio = trim($_POST['domicilio']     ?? '');
            $usuario = trim($_POST['nombre_usuario']  ?? '');
            $email   = trim($_POST['email']           ?? '');
            $pass    = $_POST['contrasena_usuario']   ?? '';
            $pass2   = $_POST['contrasene_confirm']   ?? '';

            if (empty($nombre) || empty($email) || empty($pass) || empty($usuario)) {
                $error = 'Completá todos los campos obligatorios.';
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $error = 'Ingresá un correo electrónico válido.';
            } elseif ($pass !== $pass2) {
                $error = 'Las contraseñas no coinciden.';
            } elseif (strlen($pass) < 6) {
                $error = 'La contraseña debe tener al menos 6 caracteres.';
            } elseif ($this->model->existeEmail($email)) {
                $error = "El correo '$email' ya está registrado.";
            } elseif ($this->model->existeUsuario($usuario)) {
                $error = "El usuario '$usuario' ya está en uso.";
            } else {
                    $datos = [
                        'nombre'             => $nombre,
                        'apellido'           => $apellido,
                        'domicilio'          => $domicilio,
                        'nombre_usuario'     => $usuario,
                        'email'              => $email,
                        'contrasena_usuario' => $pass,
                        'rol'                => 'empleado',
                    ];
                if ($this->model->create($datos)) {
                    $success = '¡Cuenta creada con éxito! Ya podés iniciar sesión.';
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
        require_once 'views/sesion/registrar_usuario.php';
    }

    public function cerrarSesion() {
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
                    if ($enviado) {
                        $success = 'Te enviamos un enlace a <strong>' . htmlspecialchars($email) . '</strong>. Revisá tu bandeja (y spam).';
                    } else {
                        $error = 'No se pudo enviar el email. Intentá más tarde.';
                    }
                } else {
                    //no revelar si el email existe o no
                    $success = 'Si ese correo está registrado, recibirás un enlace para restablecer tu contraseña.';
                }
            }
        }

        $data = ['error' => $error, 'success' => $success];
        require_once 'views/sesion/recuperar_clave.php';
    }

    public function restablecerClave() {
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
                header('Location: index.php?page=iniciar_sesion&msg=password_changed');
                exit;
            }
        }

        $data = ['error' => $error, 'usuario' => $usuario, 'token' => $token];
        require_once 'views/sesion/restablecer_clave.php';
    }


    private function enviarEmailRecuperacion(string $email, string $nombre, string $token): bool {
        require_once 'vendor/autoload.php';

        $mail = new \PHPMailer\PHPMailer\PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'maquitosk05@gmail.com';
            $mail->Password   = 'qgeo bdds cmui rlyn';     
            $mail->SMTPSecure = \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;
            $mail->CharSet    = 'UTF-8';

            $mail->setFrom('maquitosk05@gmail.com', 'Kayrom — Sistema');
            $mail->addAddress($email, $nombre);

            $link = (isset($_SERVER['HTTPS']) ? 'https' : 'http')
                  . '://' . $_SERVER['HTTP_HOST']
                  . dirname($_SERVER['PHP_SELF'])
                  . '/index.php?page=restablecer_clave&token=' . $token;

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

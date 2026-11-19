<?php
// OJO: este archivo tiene que estar guardado como UTF-8 SIN BOM.
// Antes tenía el BOM (3 bytes invisibles al principio) y esos bytes se mandaban al navegador
// antes que los header('Location: ...'), lo que puede romper las redirecciones.

function cargarEnv($rutaArchivo)
{
    if (!is_file($rutaArchivo)) {
        return;
    }

    $lineas = file($rutaArchivo, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    if ($lineas === false) {
        return;
    }

    foreach ($lineas as $linea) {
        $linea = trim($linea);

        if ($linea === '' || str_starts_with($linea, '#')) {
            continue;
        }

        if (!str_contains($linea, '=')) {
            continue;
        }

        [$clave, $valor] = array_map('trim', explode('=', $linea, 2));
        $valor = trim($valor, " \t\n\r\0\x0B\"");

        $_ENV[$clave] = $valor;
        $_SERVER[$clave] = $valor;
        putenv("{$clave}={$valor}");
    }
}

cargarEnv(__DIR__ . '/.env');

define('APP_ENV', $_ENV['APP_ENV'] ?? 'production');
define('DB_HOST', $_ENV['DB_HOST'] ?? 'localhost');
define('DB_NAME', $_ENV['DB_NAME'] ?? 'soft_kayrom');
define('DB_USER', $_ENV['DB_USER'] ?? 'root');
define('DB_PASS', $_ENV['DB_PASS'] ?? '');
define('DB_CHARSET', $_ENV['DB_CHARSET'] ?? 'utf8mb4'); // utf8mb4 = el mismo que usan las tablas (acepta emojis y todos los acentos)

define('MAIL_HOST', $_ENV['MAIL_HOST'] ?? '');
define('MAIL_PORT', $_ENV['MAIL_PORT'] ?? 587);
define('MAIL_USER', trim($_ENV['MAIL_USER'] ?? ''));
// Las contraseñas de aplicación de Google se muestran en grupos de 4 separados por espacios
// ("abcd efgh ijkl mnop"). Para el SMTP van SIN espacios, así que se los sacamos acá.
define('MAIL_PASS', preg_replace('/\s+/', '', $_ENV['MAIL_PASS'] ?? ''));
// Cifrado del SMTP: tls (puerto 587, el de Gmail), ssl (puerto 465) o none (solo para pruebas locales)
define('MAIL_SECURE', strtolower(trim($_ENV['MAIL_SECURE'] ?? 'tls')));

// Dirección del sistema, para armar el link del mail de recuperación (ej: http://localhost/SISTEMA-KAYROM)
define('APP_URL', $_ENV['APP_URL'] ?? '');
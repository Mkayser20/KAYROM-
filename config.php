<?php

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

define('APP_ENV', getenv('APP_ENV') ?: ($_ENV['APP_ENV'] ?? 'production'));
define('DB_HOST', getenv('DB_HOST') ?: ($_ENV['DB_HOST'] ?? 'localhost'));
define('DB_NAME', getenv('DB_NAME') ?: ($_ENV['DB_NAME'] ?? 'soft_kayrom'));
define('DB_USER', getenv('DB_USER') ?: ($_ENV['DB_USER'] ?? 'root'));
define('DB_PASS', getenv('DB_PASS') ?: ($_ENV['DB_PASS'] ?? ''));
define('DB_CHARSET', getenv('DB_CHARSET') ?: ($_ENV['DB_CHARSET'] ?? 'utf8'));

define('MAIL_HOST', getenv('MAIL_HOST') ?: ($_ENV['MAIL_HOST'] ?? ''));
define('MAIL_PORT', getenv('MAIL_PORT') ?: ($_ENV['MAIL_PORT'] ?? 587));
define('MAIL_USER', getenv('MAIL_USER') ?: ($_ENV['MAIL_USER'] ?? ''));
define('MAIL_PASS', getenv('MAIL_PASS') ?: ($_ENV['MAIL_PASS'] ?? ''));

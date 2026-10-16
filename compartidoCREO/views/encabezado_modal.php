<?php
$msg = $_GET['msg'] ?? '';
$msgTexts = [
    'created' => 'Registro creado exitosamente.',
    'updated' => 'Registro actualizado.',
];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Kayrom</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="stylesheet" href="public/css/style.css">
    <!-- Alertas modernas (SweetAlert2 + tema del sistema). La lógica está en public/js/alertas.js -->
    <link rel="stylesheet" href="public/vendor/sweetalert2/sweetalert2.min.css">
    <link rel="stylesheet" href="public/css/alertas.css">
    <script src="public/vendor/sweetalert2/sweetalert2.min.js"></script>
    <script src="public/js/alertas.js"></script>
    <style>
        body { background: transparent; padding: 4px 2px 20px; min-height: auto; display: block; }
    </style>
</head>
<body>
<?php if ($msg && isset($msgTexts[$msg])): ?>
<div class="toast"><?= $msgTexts[$msg] ?></div>
<?php endif; ?>
<main class="main-content" style="margin:0; padding:0; min-height:auto;">

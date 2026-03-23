<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/functions.php';

$token = trim((string) ($_GET['token'] ?? ''));

if ($token === '') {
    set_flash('error', 'Enlace de verificación no válido.');
    redirect('index.php');
}

$result = verify_email_token($token);

if (!$result) {
    set_flash('error', 'El enlace ha expirado o ya fue utilizado. Solicita uno nuevo.');
    redirect('index.php');
}

set_flash('success', '¡Correo verificado correctamente! Ya puedes iniciar sesión.');
redirect('index.php');

<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/functions.php';

$isAjax = ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'XMLHttpRequest';

function json_out(int $code, string $type, string $msg): void
{
    http_response_code($code);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['type' => $type, 'message' => $msg], JSON_UNESCAPED_UNICODE);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('index.php');
}

if (!verify_csrf($_POST['csrf_token'] ?? null)) {
    if ($isAjax) json_out(419, 'error', 'La sesión del formulario expiró. Recarga la página.');
    redirect('index.php');
}

if (!is_logged_in()) {
    if ($isAjax) json_out(401, 'error', 'Debes iniciar sesión para reenviar la verificación.');
    redirect('index.php');
}

$userId = current_user_id();
$pdo = getPDO();
$stmt = $pdo->prepare('SELECT email, username, email_verified FROM users WHERE id = ? LIMIT 1');
$stmt->execute([$userId]);
$user = $stmt->fetch();

if (!$user) {
    if ($isAjax) json_out(404, 'error', 'Usuario no encontrado.');
    redirect('index.php');
}

if ((int) $user['email_verified'] === 1) {
    if ($isAjax) json_out(200, 'info', 'Tu correo ya está verificado.');
    redirect('dashboard.php');
}

// Rate-limit: check if there's a recent token (less than 2 minutes old)
$stmt = $pdo->prepare(
    'SELECT created_at FROM email_verifications
     WHERE user_id = ? AND created_at > DATE_SUB(NOW(), INTERVAL 2 MINUTE)
     LIMIT 1'
);
$stmt->execute([$userId]);
if ($stmt->fetch()) {
    if ($isAjax) json_out(429, 'error', 'Ya enviamos un correo hace poco. Espera 2 minutos.');
    set_flash('error', 'Ya enviamos un correo hace poco. Espera 2 minutos.');
    redirect('dashboard.php');
}

$token = create_email_verification($userId);
send_verification_email($user['email'], $user['username'], $token);

if ($isAjax) json_out(200, 'success', 'Correo de verificación reenviado. Revisa tu bandeja de entrada.');
set_flash('success', 'Correo de verificación reenviado.');
redirect('dashboard.php');

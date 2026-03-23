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
    set_flash('error', 'La sesión del formulario expiró. Vuelve a intentarlo.');
    redirect('index.php');
}

$username = trim((string) ($_POST['username'] ?? ''));
$email = trim((string) ($_POST['email'] ?? ''));
$password = (string) ($_POST['password'] ?? '');

if (mb_strlen($username) < 3 || !filter_var($email, FILTER_VALIDATE_EMAIL) || mb_strlen($password) < 6) {
    if ($isAjax) json_out(422, 'error', 'Completa el registro con un usuario válido, correo correcto y contraseña de al menos 6 caracteres.');
    set_flash('error', 'Completa el registro con un usuario válido, correo correcto y una contraseña de al menos 6 caracteres.');
    redirect('index.php');
}

$pdo = getPDO();
$exists = $pdo->prepare('SELECT id FROM users WHERE username = ? OR email = ? LIMIT 1');
$exists->execute([$username, $email]);

if ($exists->fetch()) {
    if ($isAjax) json_out(409, 'error', 'Ese usuario o correo ya está registrado.');
    set_flash('error', 'Ese usuario o correo ya está registrado.');
    redirect('index.php');
}

$inviteCode = strtoupper(trim((string) ($_POST['invite_code'] ?? '')));
$isVip = 0;

if ($inviteCode !== '') {
    if ($inviteCode !== 'CTRLZ') {
        if ($isAjax) json_out(422, 'error', 'El código especial que ingresaste no existe.');
        set_flash('error', 'El código especial que ingresaste no existe.');
        redirect('index.php');
    }
    $isVip = 1;
}

$stmt = $pdo->prepare('INSERT INTO users (username, email, password_hash, is_vip) VALUES (?, ?, ?, ?)');
$stmt->execute([$username, $email, password_hash($password, PASSWORD_DEFAULT), $isVip]);

$userId = (int) $pdo->lastInsertId();
initialize_progress($userId);

$_SESSION['user_id'] = $userId;
$_SESSION['username'] = $username;

$msg = $isVip ? '¡Código VIP activado! Tienes vidas infinitas. A jugar.' : 'Tu cuenta está lista. Comienza con el nivel 1.';
if ($isAjax) json_out(200, 'success', $msg);
set_flash('success', $msg);
redirect('dashboard.php');
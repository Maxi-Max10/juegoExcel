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

$login = trim((string) ($_POST['login'] ?? ''));
$password = (string) ($_POST['password'] ?? '');
$user = fetch_user_by_login($login);

if (!$user || !password_verify($password, (string) $user['password_hash'])) {
    if ($isAjax) json_out(401, 'error', 'Credenciales incorrectas.');
    set_flash('error', 'Credenciales incorrectas.');
    redirect('index.php');
}

$_SESSION['user_id'] = (int) $user['id'];
$_SESSION['username'] = (string) $user['username'];

if ($isAjax) json_out(200, 'success', 'Sesión iniciada correctamente.');
set_flash('success', 'Sesión iniciada correctamente.');
redirect('dashboard.php');
<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('index.php');
}

if (!verify_csrf($_POST['csrf_token'] ?? null)) {
    set_flash('error', 'La sesión del formulario expiró. Vuelve a intentarlo.');
    redirect('index.php');
}

$login = trim((string) ($_POST['login'] ?? ''));
$password = (string) ($_POST['password'] ?? '');
$user = fetch_user_by_login($login);

if (!$user || !password_verify($password, (string) $user['password_hash'])) {
    set_flash('error', 'Credenciales incorrectas.');
    redirect('index.php');
}

$_SESSION['user_id'] = (int) $user['id'];
$_SESSION['username'] = (string) $user['username'];

set_flash('success', 'Sesión iniciada correctamente.');
redirect('dashboard.php');
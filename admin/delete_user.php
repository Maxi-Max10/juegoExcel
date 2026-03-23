<?php
declare(strict_types=1);

require_once __DIR__ . '/functions.php';
require_admin_login();

$id = (int) ($_GET['id'] ?? 0);
$csrfToken = (string) ($_GET['csrf_token'] ?? '');

if (!verify_csrf($csrfToken)) {
    set_flash('error', 'Token de seguridad inválido.');
    redirect('index.php');
}

if ($id <= 0) {
    set_flash('error', 'ID de usuario inválido.');
    redirect('index.php');
}

$user = admin_get_user($id);

if (!$user) {
    set_flash('error', 'Usuario no encontrado.');
    redirect('index.php');
}

$pdo = getPDO();

// Eliminar datos relacionados (progress y user_level_status se eliminan por CASCADE)
$stmt = $pdo->prepare('DELETE FROM users WHERE id = ?');
$stmt->execute([$id]);

set_flash('success', "Usuario '" . $user['username'] . "' eliminado correctamente.");
redirect('index.php');

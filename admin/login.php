<?php
declare(strict_types=1);

require_once __DIR__ . '/functions.php';

// Asegurar que la tabla admins exista
ensure_admin_table();

// Si ya está logueado, ir al panel
if (is_admin_logged_in()) {
    redirect('index.php');
}

$flash = get_flash();
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf($_POST['csrf_token'] ?? null)) {
        $error = 'La sesión del formulario expiró. Vuelve a intentarlo.';
    } else {
        $username = trim((string) ($_POST['username'] ?? ''));
        $password = (string) ($_POST['password'] ?? '');
        $admin = fetch_admin_by_username($username);

        if (!$admin || !password_verify($password, (string) $admin['password_hash'])) {
            $error = 'Credenciales incorrectas.';
        } else {
            $_SESSION['admin_id'] = (int) $admin['id'];
            $_SESSION['admin_username'] = (string) $admin['username'];
            set_flash('success', 'Bienvenido al panel de administración.');
            redirect('index.php');
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login | <?= e(APP_NAME) ?></title>
    <link rel="icon" type="image/png" href="../assets/img/logo.png">
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;700;800&family=Syne:wght@500;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" crossorigin="anonymous">
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body class="admin-login-page">
    <div class="admin-login-wrapper">
        <div class="admin-login-card">
            <div class="admin-login-header">
                <img src="../assets/img/logo.png" alt="Logo" width="56" height="56" class="admin-logo">
                <h1>Panel Admin</h1>
                <p>Ingresa tus credenciales de administrador</p>
            </div>

            <?php if ($flash): ?>
                <div class="alert alert-<?= $flash['type'] === 'error' ? 'danger' : 'success' ?>">
                    <?= e($flash['message']) ?>
                </div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="alert alert-danger"><?= e($error) ?></div>
            <?php endif; ?>

            <form method="POST" autocomplete="off">
                <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                <div class="form-group">
                    <label for="username"><i class="fa-solid fa-user"></i> Usuario</label>
                    <input type="text" id="username" name="username" class="form-control" required autofocus
                           value="<?= e($username ?? '') ?>">
                </div>
                <div class="form-group">
                    <label for="password"><i class="fa-solid fa-lock"></i> Contraseña</label>
                    <input type="password" id="password" name="password" class="form-control" required>
                </div>
                <button type="submit" class="btn btn-admin-primary w-100">
                    <i class="fa-solid fa-right-to-bracket"></i> Iniciar sesión
                </button>
            </form>

            <div class="admin-login-footer">
                <a href="../index.php"><i class="fa-solid fa-arrow-left"></i> Volver al sitio</a>
            </div>
        </div>
    </div>
</body>
</html>

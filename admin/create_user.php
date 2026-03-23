<?php
declare(strict_types=1);

require_once __DIR__ . '/functions.php';
require_admin_login();

$flash = get_flash();
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf($_POST['csrf_token'] ?? null)) {
        $error = 'La sesión del formulario expiró.';
    } else {
        $username = trim((string) ($_POST['username'] ?? ''));
        $email = trim((string) ($_POST['email'] ?? ''));
        $password = (string) ($_POST['password'] ?? '');
        $isVip = isset($_POST['is_vip']) ? 1 : 0;

        if (mb_strlen($username) < 3) {
            $error = 'El nombre de usuario debe tener al menos 3 caracteres.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'El correo electrónico no es válido.';
        } elseif (mb_strlen($password) < 6) {
            $error = 'La contraseña debe tener al menos 6 caracteres.';
        } else {
            $pdo = getPDO();
            $exists = $pdo->prepare('SELECT id FROM users WHERE username = ? OR email = ? LIMIT 1');
            $exists->execute([$username, $email]);

            if ($exists->fetch()) {
                $error = 'Ese usuario o correo ya está registrado.';
            } else {
                $stmt = $pdo->prepare('INSERT INTO users (username, email, password_hash, is_vip) VALUES (?, ?, ?, ?)');
                $stmt->execute([$username, $email, password_hash($password, PASSWORD_DEFAULT), $isVip]);
                $userId = (int) $pdo->lastInsertId();
                initialize_progress($userId);

                set_flash('success', "Usuario '{$username}' creado correctamente.");
                redirect('index.php');
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear Usuario | Admin <?= e(APP_NAME) ?></title>
    <link rel="icon" type="image/png" href="../assets/img/logo.png">
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;700;800&family=Syne:wght@500;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" crossorigin="anonymous">
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body class="admin-page">
    <aside class="admin-sidebar">
        <div class="admin-sidebar__brand">
            <img src="../assets/img/logo.png" alt="Logo" width="36" height="36">
            <span>Admin Panel</span>
        </div>
        <nav class="admin-sidebar__nav">
            <a href="index.php"><i class="fa-solid fa-users"></i> Usuarios</a>
            <a href="create_user.php" class="active"><i class="fa-solid fa-user-plus"></i> Nuevo usuario</a>
        </nav>
        <div class="admin-sidebar__footer">
            <span class="admin-sidebar__user"><i class="fa-solid fa-shield-halved"></i> <?= e($_SESSION['admin_username'] ?? 'Admin') ?></span>
            <a href="logout.php" class="admin-sidebar__logout"><i class="fa-solid fa-right-from-bracket"></i> Salir</a>
        </div>
    </aside>

    <main class="admin-main">
        <header class="admin-topbar">
            <div>
                <h1><i class="fa-solid fa-user-plus"></i> Crear Usuario</h1>
                <p>Agrega un nuevo usuario al sistema</p>
            </div>
            <a href="index.php" class="btn btn-admin-ghost">
                <i class="fa-solid fa-arrow-left"></i> Volver
            </a>
        </header>

        <?php if ($error): ?>
            <div class="alert alert-danger admin-alert"><?= e($error) ?></div>
        <?php endif; ?>

        <div class="admin-form-card">
            <form method="POST" autocomplete="off">
                <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">

                <div class="admin-form-row">
                    <div class="form-group">
                        <label for="username"><i class="fa-solid fa-user"></i> Nombre de usuario</label>
                        <input type="text" id="username" name="username" class="form-control" required minlength="3"
                               value="<?= e($username ?? '') ?>">
                    </div>
                    <div class="form-group">
                        <label for="email"><i class="fa-solid fa-envelope"></i> Correo electrónico</label>
                        <input type="email" id="email" name="email" class="form-control" required
                               value="<?= e($email ?? '') ?>">
                    </div>
                </div>

                <div class="admin-form-row">
                    <div class="form-group">
                        <label for="password"><i class="fa-solid fa-lock"></i> Contraseña</label>
                        <input type="password" id="password" name="password" class="form-control" required minlength="6">
                    </div>
                    <div class="form-group form-group--checkbox">
                        <label class="admin-checkbox">
                            <input type="checkbox" name="is_vip" value="1" <?= !empty($isVip) ? 'checked' : '' ?>>
                            <span class="admin-checkbox__mark"></span>
                            <i class="fa-solid fa-crown"></i> Usuario VIP (vidas infinitas)
                        </label>
                    </div>
                </div>

                <div class="admin-form-actions">
                    <button type="submit" class="btn btn-admin-primary">
                        <i class="fa-solid fa-check"></i> Crear usuario
                    </button>
                    <a href="index.php" class="btn btn-admin-ghost">Cancelar</a>
                </div>
            </form>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
</body>
</html>

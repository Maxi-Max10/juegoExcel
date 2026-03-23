<?php
declare(strict_types=1);

require_once __DIR__ . '/functions.php';
require_admin_login();

$id = (int) ($_GET['id'] ?? $_POST['id'] ?? 0);
$user = admin_get_user($id);

if (!$user) {
    set_flash('error', 'Usuario no encontrado.');
    redirect('index.php');
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf($_POST['csrf_token'] ?? null)) {
        $error = 'La sesión del formulario expiró.';
    } else {
        $username = trim((string) ($_POST['username'] ?? ''));
        $email = trim((string) ($_POST['email'] ?? ''));
        $password = (string) ($_POST['password'] ?? '');
        $isVip = isset($_POST['is_vip']) ? 1 : 0;
        $nivelActual = max(1, min(TOTAL_LEVELS, (int) ($_POST['nivel_actual'] ?? 1)));
        $puntos = max(0, (int) ($_POST['puntos'] ?? 0));
        $vidas = max(0, min(5, (int) ($_POST['vidas'] ?? 5)));
        $nivelesCompletados = max(0, min(TOTAL_LEVELS, (int) ($_POST['niveles_completados'] ?? 0)));

        if (mb_strlen($username) < 3) {
            $error = 'El nombre de usuario debe tener al menos 3 caracteres.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'El correo electrónico no es válido.';
        } elseif ($password !== '' && mb_strlen($password) < 6) {
            $error = 'La contraseña debe tener al menos 6 caracteres.';
        } else {
            $pdo = getPDO();

            // Verificar duplicados excluyendo al usuario actual
            $exists = $pdo->prepare('SELECT id FROM users WHERE (username = ? OR email = ?) AND id != ? LIMIT 1');
            $exists->execute([$username, $email, $id]);

            if ($exists->fetch()) {
                $error = 'Ese usuario o correo ya está en uso por otro usuario.';
            } else {
                // Actualizar datos del usuario
                if ($password !== '') {
                    $stmt = $pdo->prepare('UPDATE users SET username = ?, email = ?, password_hash = ?, is_vip = ? WHERE id = ?');
                    $stmt->execute([$username, $email, password_hash($password, PASSWORD_DEFAULT), $isVip, $id]);
                } else {
                    $stmt = $pdo->prepare('UPDATE users SET username = ?, email = ?, is_vip = ? WHERE id = ?');
                    $stmt->execute([$username, $email, $isVip, $id]);
                }

                // Actualizar progreso
                $pdo->prepare(
                    'INSERT INTO progress (user_id, nivel_actual, puntos, vidas, niveles_completados)
                     VALUES (?, ?, ?, ?, ?)
                     ON DUPLICATE KEY UPDATE nivel_actual = VALUES(nivel_actual), puntos = VALUES(puntos),
                                             vidas = VALUES(vidas), niveles_completados = VALUES(niveles_completados)'
                )->execute([$id, $nivelActual, $puntos, $vidas, $nivelesCompletados]);

                set_flash('success', "Usuario '{$username}' actualizado correctamente.");
                redirect('index.php');
            }
        }

        // Refrescar datos si hubo error
        $user = array_merge($user, [
            'username' => $username,
            'email' => $email,
            'is_vip' => $isVip,
            'nivel_actual' => $nivelActual,
            'puntos' => $puntos,
            'vidas' => $vidas,
            'niveles_completados' => $nivelesCompletados,
        ]);
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Usuario | Admin <?= e(APP_NAME) ?></title>
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
            <a href="create_user.php"><i class="fa-solid fa-user-plus"></i> Nuevo usuario</a>
        </nav>
        <div class="admin-sidebar__footer">
            <span class="admin-sidebar__user"><i class="fa-solid fa-shield-halved"></i> <?= e($_SESSION['admin_username'] ?? 'Admin') ?></span>
            <a href="logout.php" class="admin-sidebar__logout"><i class="fa-solid fa-right-from-bracket"></i> Salir</a>
        </div>
    </aside>

    <main class="admin-main">
        <header class="admin-topbar">
            <div>
                <h1><i class="fa-solid fa-pen-to-square"></i> Editar Usuario #<?= (int) $user['id'] ?></h1>
                <p>Modificar datos de <strong><?= e($user['username']) ?></strong></p>
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
                <input type="hidden" name="id" value="<?= (int) $user['id'] ?>">

                <h3 class="admin-form-section">Datos de cuenta</h3>
                <div class="admin-form-row">
                    <div class="form-group">
                        <label for="username"><i class="fa-solid fa-user"></i> Nombre de usuario</label>
                        <input type="text" id="username" name="username" class="form-control" required minlength="3"
                               value="<?= e($user['username']) ?>">
                    </div>
                    <div class="form-group">
                        <label for="email"><i class="fa-solid fa-envelope"></i> Correo electrónico</label>
                        <input type="email" id="email" name="email" class="form-control" required
                               value="<?= e($user['email']) ?>">
                    </div>
                </div>

                <div class="admin-form-row">
                    <div class="form-group">
                        <label for="password"><i class="fa-solid fa-lock"></i> Nueva contraseña <small>(dejar vacío para no cambiar)</small></label>
                        <input type="password" id="password" name="password" class="form-control" minlength="6">
                    </div>
                    <div class="form-group form-group--checkbox">
                        <label class="admin-checkbox">
                            <input type="checkbox" name="is_vip" value="1" <?= (int) $user['is_vip'] ? 'checked' : '' ?>>
                            <span class="admin-checkbox__mark"></span>
                            <i class="fa-solid fa-crown"></i> Usuario VIP (vidas infinitas)
                        </label>
                    </div>
                </div>

                <h3 class="admin-form-section">Progreso del juego</h3>
                <div class="admin-form-row admin-form-row--4">
                    <div class="form-group">
                        <label for="nivel_actual"><i class="fa-solid fa-layer-group"></i> Nivel actual</label>
                        <input type="number" id="nivel_actual" name="nivel_actual" class="form-control"
                               min="1" max="<?= TOTAL_LEVELS ?>" value="<?= (int) $user['nivel_actual'] ?>">
                    </div>
                    <div class="form-group">
                        <label for="puntos"><i class="fa-solid fa-bolt"></i> Puntos</label>
                        <input type="number" id="puntos" name="puntos" class="form-control"
                               min="0" value="<?= (int) $user['puntos'] ?>">
                    </div>
                    <div class="form-group">
                        <label for="vidas"><i class="fa-solid fa-heart"></i> Vidas</label>
                        <input type="number" id="vidas" name="vidas" class="form-control"
                               min="0" max="5" value="<?= (int) $user['vidas'] ?>">
                    </div>
                    <div class="form-group">
                        <label for="niveles_completados"><i class="fa-solid fa-check-double"></i> Niveles completados</label>
                        <input type="number" id="niveles_completados" name="niveles_completados" class="form-control"
                               min="0" max="<?= TOTAL_LEVELS ?>" value="<?= (int) $user['niveles_completados'] ?>">
                    </div>
                </div>

                <div class="admin-form-actions">
                    <button type="submit" class="btn btn-admin-primary">
                        <i class="fa-solid fa-save"></i> Guardar cambios
                    </button>
                    <a href="index.php" class="btn btn-admin-ghost">Cancelar</a>
                </div>
            </form>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
</body>
</html>

<?php
declare(strict_types=1);

require_once __DIR__ . '/functions.php';
require_admin_login();

$flash = get_flash();
$users = admin_get_all_users();
$totalUsers = count($users);
$totalVip = count(array_filter($users, fn($u) => (int) $u['is_vip'] === 1));
$totalPoints = array_sum(array_column($users, 'puntos'));
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel | <?= e(APP_NAME) ?></title>
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
            <a href="index.php" class="active"><i class="fa-solid fa-users"></i> Usuarios</a>
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
                <h1><i class="fa-solid fa-users"></i> Gestión de Usuarios</h1>
                <p><?= $totalUsers ?> usuario<?= $totalUsers !== 1 ? 's' : '' ?> registrado<?= $totalUsers !== 1 ? 's' : '' ?></p>
            </div>
            <a href="create_user.php" class="btn btn-admin-primary">
                <i class="fa-solid fa-plus"></i> Nuevo usuario
            </a>
        </header>

        <?php if ($flash): ?>
            <div class="alert alert-<?= $flash['type'] === 'error' ? 'danger' : ($flash['type'] === 'warning' ? 'warning' : 'success') ?> admin-alert">
                <?= e($flash['message']) ?>
            </div>
        <?php endif; ?>

        <div class="admin-stats">
            <div class="admin-stat-card">
                <div class="admin-stat-card__icon admin-stat-card__icon--users">
                    <i class="fa-solid fa-users"></i>
                </div>
                <div>
                    <span class="admin-stat-card__label">Total usuarios</span>
                    <strong class="admin-stat-card__value"><?= $totalUsers ?></strong>
                </div>
            </div>
            <div class="admin-stat-card">
                <div class="admin-stat-card__icon admin-stat-card__icon--vip">
                    <i class="fa-solid fa-crown"></i>
                </div>
                <div>
                    <span class="admin-stat-card__label">VIP</span>
                    <strong class="admin-stat-card__value"><?= $totalVip ?></strong>
                </div>
            </div>
            <div class="admin-stat-card">
                <div class="admin-stat-card__icon admin-stat-card__icon--points">
                    <i class="fa-solid fa-bolt"></i>
                </div>
                <div>
                    <span class="admin-stat-card__label">Puntos totales</span>
                    <strong class="admin-stat-card__value"><?= number_format($totalPoints) ?></strong>
                </div>
            </div>
        </div>

        <div class="admin-table-wrapper">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Usuario</th>
                        <th>Email</th>
                        <th>VIP</th>
                        <th>Nivel</th>
                        <th>Puntos</th>
                        <th>Niveles completados</th>
                        <th>Registro</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($users)): ?>
                        <tr><td colspan="9" class="text-center py-4">No hay usuarios registrados.</td></tr>
                    <?php else: ?>
                        <?php foreach ($users as $u): ?>
                            <tr>
                                <td><?= (int) $u['id'] ?></td>
                                <td><strong><?= e($u['username']) ?></strong></td>
                                <td><?= e($u['email']) ?></td>
                                <td>
                                    <?php if ((int) $u['is_vip']): ?>
                                        <span class="badge badge--vip"><i class="fa-solid fa-crown"></i> VIP</span>
                                    <?php else: ?>
                                        <span class="badge badge--normal">Normal</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= (int) $u['nivel_actual'] ?></td>
                                <td><?= number_format((int) $u['puntos']) ?></td>
                                <td><?= (int) $u['niveles_completados'] ?>/<?= TOTAL_LEVELS ?></td>
                                <td><?= e(date('d/m/Y', strtotime($u['created_at']))) ?></td>
                                <td class="admin-actions">
                                    <a href="edit_user.php?id=<?= (int) $u['id'] ?>" class="btn-icon btn-icon--edit" title="Editar">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                    </a>
                                    <a href="delete_user.php?id=<?= (int) $u['id'] ?>&csrf_token=<?= e(csrf_token()) ?>"
                                       class="btn-icon btn-icon--delete" title="Eliminar"
                                       onclick="return confirm('¿Estás seguro de eliminar al usuario \'<?= e($u['username']) ?>\'? Esta acción no se puede deshacer.')">
                                        <i class="fa-solid fa-trash"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
</body>
</html>

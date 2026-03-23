<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/functions.php';

/**
 * Verifica si hay una sesión de admin activa.
 */
function is_admin_logged_in(): bool
{
    return isset($_SESSION['admin_id']);
}

/**
 * Redirige al login de admin si no hay sesión.
 */
function require_admin_login(): void
{
    if (!is_admin_logged_in()) {
        set_flash('error', 'Debes iniciar sesión como administrador.');
        redirect('login.php');
    }
}

/**
 * Busca un admin por username.
 */
function fetch_admin_by_username(string $username): ?array
{
    $stmt = getPDO()->prepare('SELECT * FROM admins WHERE username = ? LIMIT 1');
    $stmt->execute([$username]);
    $admin = $stmt->fetch();
    return $admin ?: null;
}

/**
 * Crea la tabla de admins si no existe y siembra el admin por defecto.
 */
function ensure_admin_table(): void
{
    $pdo = getPDO();

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS admins (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(40) NOT NULL UNIQUE,
            password_hash VARCHAR(255) NOT NULL,
            created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
        )
    ");

    $stmt = $pdo->query('SELECT COUNT(*) FROM admins');
    if ((int) $stmt->fetchColumn() === 0) {
        $hash = password_hash('admin123', PASSWORD_DEFAULT);
        $ins = $pdo->prepare('INSERT INTO admins (username, password_hash) VALUES (?, ?)');
        $ins->execute(['admin', $hash]);
    } else {
        // Verificar que el admin por defecto tenga un hash válido
        $check = $pdo->prepare('SELECT id, password_hash FROM admins WHERE username = ? LIMIT 1');
        $check->execute(['admin']);
        $row = $check->fetch();
        if ($row && !password_verify('admin123', (string) $row['password_hash'])) {
            $hash = password_hash('admin123', PASSWORD_DEFAULT);
            $upd = $pdo->prepare('UPDATE admins SET password_hash = ? WHERE id = ?');
            $upd->execute([$hash, $row['id']]);
        }
    }
}

/**
 * Obtiene todos los usuarios con su progreso.
 */
function admin_get_all_users(): array
{
    $sql = "
        SELECT u.id, u.username, u.email, u.is_vip, u.created_at,
               COALESCE(p.nivel_actual, 1) AS nivel_actual,
               COALESCE(p.puntos, 0) AS puntos,
               COALESCE(p.vidas, 5) AS vidas,
               COALESCE(p.niveles_completados, 0) AS niveles_completados
        FROM users u
        LEFT JOIN progress p ON p.user_id = u.id
        ORDER BY u.id DESC
    ";
    return getPDO()->query($sql)->fetchAll();
}

/**
 * Obtiene un usuario por ID con su progreso.
 */
function admin_get_user(int $id): ?array
{
    $stmt = getPDO()->prepare("
        SELECT u.id, u.username, u.email, u.password_hash, u.is_vip, u.created_at,
               COALESCE(p.nivel_actual, 1) AS nivel_actual,
               COALESCE(p.puntos, 0) AS puntos,
               COALESCE(p.vidas, 5) AS vidas,
               COALESCE(p.racha_actual, 0) AS racha_actual,
               COALESCE(p.niveles_completados, 0) AS niveles_completados
        FROM users u
        LEFT JOIN progress p ON p.user_id = u.id
        WHERE u.id = ?
        LIMIT 1
    ");
    $stmt->execute([$id]);
    $user = $stmt->fetch();
    return $user ?: null;
}

<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/functions.php';

require_login();

$userId = current_user_id();
$user = fetch_user_by_id((int) $userId);
$progress = get_user_progress((int) $userId);
$levels = get_all_levels();
$statusMap = get_user_level_status_map((int) $userId);
$flash = get_flash();
$leaderboard = fetch_leaderboard(8);
$currentLevel = max(1, min(TOTAL_LEVELS, (int) $progress['nivel_actual']));
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e(APP_NAME) ?> | Panel</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;700&family=Sora:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/styles.css">
</head>
<body class="app-page">
    <div class="page-shell">
        <header class="topbar">
            <div>
                <span class="eyebrow">Hola, <?= e($user['username'] ?? $_SESSION['username'] ?? 'Jugador') ?></span>
                <h1>Mapa de progreso</h1>
            </div>
            <nav class="topbar__actions">
                <a class="button button--ghost" href="leaderboard.php">Ranking</a>
                <a class="button button--ghost" href="logout.php">Salir</a>
            </nav>
        </header>

        <?php if ($flash): ?>
            <div class="flash flash--<?= e($flash['type']) ?>"><?= e($flash['message']) ?></div>
        <?php endif; ?>

        <section class="overview-grid">
            <article class="stat-card stat-card--highlight">
                <span class="stat-card__label">Siguiente reto</span>
                <strong>Nivel <?= e((string) $currentLevel) ?></strong>
                <p><?= e(level_band_title($currentLevel)) ?> · Sigue donde te quedaste.</p>
                <a class="button button--primary" href="level.php?nivel=<?= e((string) $currentLevel) ?>">Continuar</a>
            </article>
            <article class="stat-card">
                <span class="stat-card__label">Puntos</span>
                <strong><?= e((string) $progress['puntos']) ?></strong>
                <p>Se acumulan con cada nivel nuevo completado.</p>
            </article>
            <article class="stat-card">
                <span class="stat-card__label">Niveles completados</span>
                <strong><?= e((string) $progress['niveles_completados']) ?>/<?= TOTAL_LEVELS ?></strong>
                <p>Tu dominio actual de Excel en el juego.</p>
            </article>
            <article class="stat-card">
                <span class="stat-card__label">Vidas</span>
                <strong><?= e((string) $progress['vidas']) ?>/5</strong>
                <p>Se recuperan poco a poco cuando respondes bien.</p>
            </article>
        </section>

        <section class="progress-section">
            <div>
                <div class="section-heading">
                    <h2>Progreso general</h2>
                    <span><?= number_format(progress_percentage($progress), 0) ?>%</span>
                </div>
                <div class="progress-bar progress-bar--large">
                    <div class="progress-bar__fill" style="width: <?= number_format(progress_percentage($progress), 2, '.', '') ?>%"></div>
                </div>
            </div>
            <div>
                <div class="section-heading">
                    <h2>Ruta desbloqueada</h2>
                    <span><?= number_format(current_level_percentage($progress), 0) ?>%</span>
                </div>
                <div class="progress-bar progress-bar--large progress-bar--secondary">
                    <div class="progress-bar__fill" style="width: <?= number_format(current_level_percentage($progress), 2, '.', '') ?>%"></div>
                </div>
            </div>
        </section>

        <main class="dashboard-grid">
            <section class="levels-panel">
                <div class="section-heading">
                    <h2>Ruta de 100 niveles</h2>
                    <span>Desbloqueo progresivo</span>
                </div>
                <div class="levels-grid">
                    <?php foreach ($levels as $level): ?>
                        <?php
                        $number = (int) $level['numero'];
                        $status = $statusMap[$number] ?? null;
                        $completed = !empty($status['completed_at']);
                        $unlocked = level_is_unlocked($progress, $number);
                        $cardClass = $completed ? 'is-completed' : ($unlocked ? 'is-unlocked' : 'is-locked');
                        ?>
                        <article class="level-card <?= e($cardClass) ?>">
                            <div class="level-card__header">
                                <span class="level-card__number">Nivel <?= e((string) $number) ?></span>
                                <span class="pill <?= e(difficulty_class((string) $level['dificultad'])) ?>"><?= e($level['dificultad']) ?></span>
                            </div>
                            <h3><?= e($level['titulo']) ?></h3>
                            <p><?= e($level['categoria']) ?></p>
                            <div class="level-card__footer">
                                <span><?= $completed ? 'Completado' : ($unlocked ? 'Disponible' : 'Bloqueado') ?></span>
                                <?php if ($unlocked): ?>
                                    <a href="level.php?nivel=<?= e((string) $number) ?>">Abrir</a>
                                <?php else: ?>
                                    <span>Supera el nivel anterior</span>
                                <?php endif; ?>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            </section>

            <aside class="side-panel">
                <section class="leaderboard-card">
                    <div class="section-heading">
                        <h2>Top jugadores</h2>
                        <a href="leaderboard.php">Ver más</a>
                    </div>
                    <ol class="leaderboard-list">
                        <?php foreach ($leaderboard as $entry): ?>
                            <li>
                                <div>
                                    <strong><?= e($entry['username']) ?></strong>
                                    <span><?= e((string) $entry['niveles_completados']) ?> niveles</span>
                                </div>
                                <span><?= e((string) $entry['puntos']) ?> pts</span>
                            </li>
                        <?php endforeach; ?>
                    </ol>
                </section>

                <section class="hint-card">
                    <h2>Cómo avanzar más rápido</h2>
                    <ul>
                        <li>Escribe la fórmula con o sin espacios: el validador normaliza el formato.</li>
                        <li>Puedes usar coma o punto y coma como separador de argumentos.</li>
                        <li>Revisa la celda objetivo antes de enviar tu respuesta.</li>
                    </ul>
                </section>
            </aside>
        </main>
    </div>
    <script src="assets/js/app.js"></script>
</body>
</html>
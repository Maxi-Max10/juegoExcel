<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/layout.php';

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
    <?php render_head(APP_NAME . ' | Panel'); ?>
</head>
<body class="app-page">
    <div class="page-shell">
        <header class="site-header" data-reveal>
            <a class="brand" href="dashboard.php">
                <span class="brand__mark"><i class="fa-solid fa-table-cells-large"></i></span>
                <span>
                    <strong>Excel Quest</strong>
                    <small>Panel de progreso</small>
                </span>
            </a>
            <nav class="site-nav site-nav--actions">
                <a href="leaderboard.php">Ranking</a>
                <a href="logout.php">Salir</a>
            </nav>
        </header>

        <header class="topbar topbar--hero" data-reveal>
            <div>
                <span class="eyebrow">Hola, <?= e($user['username'] ?? $_SESSION['username'] ?? 'Jugador') ?></span>
                <h1>Mapa de progreso</h1>
                <p class="topbar__lead">Tu tablero resume dónde estás, qué te falta y cuál es el siguiente reto que más impacto tiene en tu avance.</p>
            </div>
            <nav class="topbar__actions">
                <a class="button button--primary" href="level.php?nivel=<?= e((string) $currentLevel) ?>">Jugar ahora</a>
                <a class="button button--ghost" href="leaderboard.php">Ver ranking</a>
            </nav>
        </header>

        <?php if ($flash): ?>
            <div class="flash flash--<?= e($flash['type']) ?>"><?= e($flash['message']) ?></div>
        <?php endif; ?>

        <section class="dashboard-hero-grid">
            <article class="focus-card" data-reveal>
                <div class="focus-card__copy">
                    <span class="eyebrow">Siguiente misión</span>
                    <h2>Nivel <?= e((string) $currentLevel) ?> listo para jugar</h2>
                    <p><?= e(level_band_title($currentLevel)) ?> · Mantén el ritmo, suma puntos y desbloquea la siguiente zona.</p>
                    <div class="focus-card__actions">
                        <a class="button button--primary" href="level.php?nivel=<?= e((string) $currentLevel) ?>">Continuar partida</a>
                        <a class="button button--ghost" href="leaderboard.php">Comparar ranking</a>
                    </div>
                </div>
                <div class="focus-card__rings">
                    <div class="focus-ring">
                        <span><?= number_format(progress_percentage($progress), 0) ?>%</span>
                        <small>Completado</small>
                    </div>
                    <ul class="focus-list">
                        <li><i class="fa-solid fa-star"></i> <?= e((string) $progress['puntos']) ?> puntos</li>
                        <li><i class="fa-solid fa-heart"></i> <?= e((string) $progress['vidas']) ?>/5 vidas</li>
                        <li><i class="fa-solid fa-layer-group"></i> <?= e((string) $progress['niveles_completados']) ?> niveles resueltos</li>
                    </ul>
                </div>
            </article>
        </section>

        <section class="overview-grid" data-stagger-group>
            <article class="stat-card stat-card--highlight">
                <div class="stat-card__top">
                    <span class="stat-card__label">Siguiente reto</span>
                </div>
                <strong class="stat-card__value">Nivel <?= e((string) $currentLevel) ?></strong>
                <p><?= e(level_band_title($currentLevel)) ?> · Sigue donde te quedaste.</p>
                <a class="button button--primary stat-card__cta" href="level.php?nivel=<?= e((string) $currentLevel) ?>">Continuar</a>
            </article>
            <article class="stat-card" data-reveal-item>
                <div class="stat-card__top">
                    <span class="stat-card__label">Puntos</span>
                </div>
                <strong class="stat-card__value"><?= e((string) $progress['puntos']) ?></strong>
                <p>Se acumulan con cada nivel nuevo completado.</p>
            </article>
            <article class="stat-card" data-reveal-item>
                <div class="stat-card__top">
                    <span class="stat-card__label">Niveles completados</span>
                </div>
                <strong class="stat-card__value"><?= e((string) $progress['niveles_completados']) ?>/<?= TOTAL_LEVELS ?></strong>
                <p>Tu dominio actual de Excel en el juego.</p>
            </article>
            <article class="stat-card" data-reveal-item>
                <div class="stat-card__top">
                    <span class="stat-card__label">Vidas</span>
                </div>
                <strong class="stat-card__value"><?= e((string) $progress['vidas']) ?>/5</strong>
                <p>Se recuperan poco a poco cuando respondes bien.</p>
            </article>
        </section>

        <section class="progress-section" data-reveal>
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
            <section class="levels-panel" data-reveal>
                <div class="section-heading levels-panel__heading">
                    <div>
                        <h2>Ruta de niveles</h2>
                        <p class="levels-panel__summary">Vista resumida del mapa completo. Expándelo solo cuando quieras revisar toda la ruta.</p>
                    </div>
                    <button class="button button--ghost levels-panel__toggle" type="button" data-route-toggle data-label-expand="Ver ruta completa" data-label-collapse="Ver menos">
                        Ver ruta completa
                    </button>
                </div>
                <div class="levels-panel__viewport is-collapsed" data-route-viewport>
                    <div class="levels-grid">
                    <?php foreach ($levels as $level): ?>
                        <?php
                        $number = (int) $level['numero'];
                        $status = $statusMap[$number] ?? null;
                        $completed = !empty($status['completed_at']);
                        $unlocked = level_is_unlocked($progress, $number);
                        $cardClass = $completed ? 'is-completed' : ($unlocked ? 'is-unlocked' : 'is-locked');
                        ?>
                        <article class="level-card <?= e($cardClass) ?>" data-level-card>
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
                </div>
            </section>

            <aside class="side-panel">
                <section class="leaderboard-card" data-reveal>
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

                <section class="hint-card" data-reveal>
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
    <?php render_app_scripts(); ?>
</body>
</html>
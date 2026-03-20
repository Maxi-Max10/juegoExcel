<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/layout.php';

require_login();

$userId = (int) current_user_id();
$requestedLevel = isset($_GET['nivel']) ? max(1, min(TOTAL_LEVELS, (int) $_GET['nivel'])) : 1;
$progress = get_user_progress($userId);

if (!level_is_unlocked($progress, $requestedLevel)) {
    set_flash('error', 'Ese nivel aún está bloqueado.');
    redirect('dashboard.php');
}

$level = get_level_by_number($requestedLevel);

if (!$level) {
    set_flash('error', 'El nivel solicitado no existe.');
    redirect('dashboard.php');
}

$status = get_single_level_status($userId, (int) $level['id']);
$tables = build_level_tables($level);
$guide = level_learning_guide($level);
$flash = get_flash();
$nextLevel = min(TOTAL_LEVELS, $requestedLevel + 1);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <?php render_head(APP_NAME . ' | Nivel ' . (string) $requestedLevel); ?>
</head>
<body class="app-page level-page">
    <div class="page-shell">
        <header class="site-header" data-reveal>
            <a class="brand" href="dashboard.php">
                <span class="brand__mark"><i class="fa-solid fa-table-cells-large"></i></span>
                <span>
                    <strong>Excel Quest</strong>
                    <small>Modo práctica</small>
                </span>
            </a>
            <nav class="site-nav site-nav--actions" id="main-nav">
                <a href="dashboard.php">Mapa</a>
                <a href="leaderboard.php">Ranking</a>
            </nav>
            <button class="nav-toggle" type="button" aria-label="Menú" aria-expanded="false" data-nav-toggle>
                <span class="nav-toggle__bar"></span>
                <span class="nav-toggle__bar"></span>
                <span class="nav-toggle__bar"></span>
            </button>
        </header>

        <nav class="bottom-nav" aria-label="Navegación principal">
            <a href="dashboard.php" class="bottom-nav__item">
                <i class="fa-solid fa-map"></i>
                <span>Mapa</span>
            </a>
            <a href="level.php?nivel=<?= e((string) $requestedLevel) ?>" class="bottom-nav__item bottom-nav__item--active">
                <i class="fa-solid fa-gamepad"></i>
                <span>Jugar</span>
            </a>
            <a href="leaderboard.php" class="bottom-nav__item">
                <i class="fa-solid fa-trophy"></i>
                <span>Ranking</span>
            </a>
            <a href="logout.php" class="bottom-nav__item">
                <i class="fa-solid fa-right-from-bracket"></i>
                <span>Salir</span>
            </a>
        </nav>

        <header class="topbar topbar--hero" data-reveal>
            <div>
                <a class="eyebrow link-inline" href="dashboard.php">Volver al mapa</a>
                <h1>Nivel <?= e((string) $requestedLevel) ?> · <?= e($level['titulo']) ?></h1>
                <p class="topbar__lead">El objetivo es escribir una fórmula válida en la celda marcada. La hoja se adapta a móvil con desplazamiento seguro y feedback inmediato.</p>
            </div>
            <div class="topbar__actions">
                <span class="pill <?= e(difficulty_class((string) $level['dificultad'])) ?>"><?= e($level['dificultad']) ?></span>
                <span class="pill pill--neutral">+<?= e((string) $level['points_reward']) ?> pts</span>
            </div>
        </header>

        <?php if ($flash): ?>
            <div class="flash flash--<?= e($flash['type']) ?>"><?= e($flash['message']) ?></div>
        <?php endif; ?>

        <section class="lesson-spotlight" data-reveal>
            <article class="lesson-spotlight__card">
                <div>
                    <span class="eyebrow">Objetivo actual</span>
                    <h2><?= e($level['consigna']) ?></h2>
                </div>
                <div class="lesson-spotlight__meta">
                    <span><i class="fa-solid fa-location-crosshairs"></i> Celda <?= e($level['formula_target']) ?></span>
                    <span><i class="fa-solid fa-star"></i> <?= e((string) $level['points_reward']) ?> pts</span>
                </div>
            </article>
        </section>

        <main class="play-layout">
            <section class="play-panel d-flex flex-column gap-4">
                <div class="prompt-card mb-0" data-reveal>
                    <span class="eyebrow"><?= e($level['categoria']) ?></span>
                    <h2><?= e($level['consigna']) ?></h2>
                    <p>Escribe la fórmula exacta para colocarla en la celda <?= e($level['formula_target']) ?>.</p>
                </div>

                <?= render_excel_tables($tables, (string) $level['formula_target']) ?>
            </section>

            <aside class="play-sidebar d-flex flex-column gap-4">
                <section class="answer-card mb-0" data-reveal>
                    <h2>Tu respuesta</h2>
                    <form id="level-form" class="stacked-form" data-next-level="<?= e((string) $nextLevel) ?>" data-dashboard-url="dashboard.php" novalidate>
                        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                        <input type="hidden" name="level_id" value="<?= e((string) $level['id']) ?>">
                        <label>
                            <span>Fórmula</span>
                            <input type="text" name="formula" autocomplete="off" required>
                        </label>
                        <button class="button button--primary button--wide" type="submit">Validar fórmula</button>
                    </form>
                    <div id="level-feedback" class="feedback-box" aria-live="polite"></div>
                    <div id="next-actions" class="next-actions<?= !empty($status['completed_at']) ? ' is-visible' : '' ?>">
                        <?php if ($requestedLevel < TOTAL_LEVELS): ?>
                            <a class="button button--secondary button--wide" href="level.php?nivel=<?= e((string) $nextLevel) ?>">Ir al siguiente nivel</a>
                        <?php else: ?>
                            <a class="button button--secondary button--wide" href="leaderboard.php">Ver ranking final</a>
                        <?php endif; ?>
                    </div>
                </section>

                <section class="mini-stat-card mb-0" data-reveal>
                    <h3>Tu progreso</h3>
                    <p>Nivel actual desbloqueado: <?= e((string) $progress['nivel_actual']) ?></p>
                    <p>Puntos acumulados: <strong id="player-points"><?= e((string) $progress['puntos']) ?></strong></p>
                    <p>Vidas: <strong id="player-lives"><?= e((string) $progress['vidas']) ?></strong>/5</p>
                    <div class="progress-bar">
                        <div id="level-progress-fill" class="progress-bar__fill" style="width: <?= number_format(progress_percentage($progress), 2, '.', '') ?>%"></div>
                    </div>
                </section>

                <section class="hint-card mb-0" data-reveal>
                    <h3><?= e($guide['title']) ?></h3>
                    <p class="hint-card__explanation"><?= e($guide['explanation']) ?></p>
                    <div class="formula-example">
                        <span class="formula-example__label">Ejemplo</span>
                        <strong class="formula-example__value"><?= e($guide['example']) ?></strong>
                    </div>
                </section>

                <section class="hint-card mb-0" data-reveal>
                    <h3>Tip rápido</h3>
                    <ul>
                        <li>Usa paréntesis cuando combines operaciones.</li>
                        <li>Para búsquedas exactas, BUSCARV usa FALSO.</li>
                        <li>Si te equivocas, revisa si el rango y el criterio coinciden.</li>
                    </ul>
                </section>

            </aside>
        </main>

    </div>
    <?php render_app_scripts(); ?>
</body>
</html>
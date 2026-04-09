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

if ((int) $progress['vidas'] <= 0) {
    set_flash('error', 'No tienes vidas. Espera a que se regeneren (1 cada 2 min).');
    redirect('dashboard.php');
}

$status = get_single_level_status($userId, (int) $level['id']);
$flash = get_flash();
$nextLevel = min(TOTAL_LEVELS, $requestedLevel + 1);
$distractors = generate_distractors($level);
$correct = (string) $level['respuesta_correcta'];

$answers = [];
$answers[] = ['text' => $correct, 'correct' => true];
foreach ($distractors as $d) {
    $answers[] = ['text' => $d, 'correct' => false];
}
shuffle($answers);

$snakeData = [
    'levelId'    => (int) $level['id'],
    'numero'     => (int) $level['numero'],
    'titulo'     => $level['titulo'],
    'consigna'   => $level['consigna'],
    'categoria'  => $level['categoria'],
    'dificultad' => $level['dificultad'],
    'target'     => $level['formula_target'],
    'reward'     => (int) $level['points_reward'],
    'answers'    => $answers,
    'csrfToken'  => csrf_token(),
    'nextLevel'  => $nextLevel,
    'lives'      => (int) $progress['vidas'],
    'points'     => (int) $progress['puntos'],
];

$guide = level_learning_guide($level);

$speedMap = [
    'Básico'       => 160,
    'Intermedio 1'  => 140,
    'Intermedio 2'  => 120,
    'Avanzado 1'    => 105,
    'Avanzado 2'    => 90,
    'Experto 1'     => 80,
    'Experto 2'     => 72,
    'Experto 3'     => 65,
    'Experto 4'     => 58,
    'Maestro'       => 52,
];
$snakeData['speed'] = $speedMap[$level['dificultad']] ?? 140;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <?php render_head(APP_NAME . ' | Snake · Nivel ' . (string) $requestedLevel); ?>
    <link rel="stylesheet" href="assets/css/snake.css">
</head>
<body class="app-page snake-page">
    <!-- Ambient particles -->
    <div class="sg-particles" id="sg-particles"></div>

    <div class="page-shell">
        <header class="site-header" data-reveal>
            <a class="brand" href="dashboard.php">
                <span class="brand__mark"><img src="assets/img/logo.png" alt="Excel Snake" width="46" height="46"></span>
                <span>
                    <strong>Excel Snake</strong>
                    <small>Modo Snake</small>
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
            <a href="snake.php?nivel=<?= e((string) $requestedLevel) ?>" class="bottom-nav__item bottom-nav__item--active">
                <i class="fa-solid fa-gamepad"></i>
                <span>Snake</span>
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

        <?php if ($flash): ?>
            <div class="flash flash--<?= e($flash['type']) ?>"><?= e($flash['message']) ?></div>
        <?php endif; ?>

        <!-- Game HUD bar -->
        <div class="sg-hud" data-reveal>
            <div class="sg-hud__left">
                <span class="sg-hud__level">
                    <i class="fa-solid fa-gamepad"></i>
                    Nivel <strong><?= e((string) $requestedLevel) ?></strong>
                </span>
                <span class="sg-hud__diff pill <?= e(difficulty_class((string) $level['dificultad'])) ?>"><?= e($level['dificultad']) ?></span>
            </div>
            <div class="sg-hud__center">
                <span class="sg-hud__title"><?= e($level['titulo']) ?></span>
            </div>
            <div class="sg-hud__right">
                <button class="sg-hud__btn" type="button" id="guide-popup-btn" title="Ayuda"><i class="fa-solid fa-circle-question"></i></button>
                <span class="sg-hud__stat sg-hud__stat--reward"><i class="fa-solid fa-bolt"></i> +<?= e((string) $level['points_reward']) ?></span>
                <span class="sg-hud__stat sg-hud__stat--lives" id="snake-lives"><i class="fa-solid fa-heart"></i> <?= e((string) $progress['vidas']) ?></span>
                <span class="sg-hud__stat sg-hud__stat--points" id="snake-points"><i class="fa-solid fa-star"></i> <?= e((string) $progress['puntos']) ?></span>
            </div>
        </div>

        <!-- Consigna / question bar -->
        <div class="sg-question" data-reveal>
            <i class="fa-solid fa-clipboard-question sg-question__icon"></i>
            <p><?= e($level['consigna']) ?></p>
        </div>

        <main class="snake-game-container">
            <div class="snake-layout">
            <section class="snake-board-wrapper">
                <div class="snake-board" id="snake-board">
                    <canvas id="snake-canvas"></canvas>
                    <!-- Score popup layer -->
                    <div class="sg-score-popup" id="sg-score-popup"></div>
                    <div class="snake-overlay" id="snake-overlay">
                        <div class="snake-overlay__content" id="snake-overlay-content">
                            <div class="sg-start-icon">🐍</div>
                            <h2>Modo Snake</h2>
                            <p>Mueve la serpiente hasta la respuesta correcta<br><small><i class="fa-solid fa-keyboard"></i> Flechas / WASD &nbsp;·&nbsp; <i class="fa-solid fa-hand-pointer"></i> Desliza en móvil</small></p>
                            <button class="sg-play-btn" id="snake-start-btn" type="button">
                                <i class="fa-solid fa-play"></i> Jugar
                            </button>
                        </div>
                    </div>
                </div>

                <div class="snake-controls" id="snake-touch-controls">
                    <button class="snake-btn snake-btn--up" data-dir="up" type="button" aria-label="Arriba"><i class="fa-solid fa-chevron-up"></i></button>
                    <div class="snake-controls__row">
                        <button class="snake-btn snake-btn--left" data-dir="left" type="button" aria-label="Izquierda"><i class="fa-solid fa-chevron-left"></i></button>
                        <button class="snake-btn snake-btn--down" data-dir="down" type="button" aria-label="Abajo"><i class="fa-solid fa-chevron-down"></i></button>
                        <button class="snake-btn snake-btn--right" data-dir="right" type="button" aria-label="Derecha"><i class="fa-solid fa-chevron-right"></i></button>
                    </div>
                </div>
            </section>

            <aside class="snake-sidebar">
                <section class="snake-legend" id="snake-legend">
                    <div class="sg-legend-header">
                        <i class="fa-solid fa-crosshairs"></i>
                        <div>
                            <span class="sg-legend-label">Celda objetivo</span>
                            <strong class="sg-legend-target"><?= e($level['formula_target']) ?></strong>
                        </div>
                    </div>
                    <p class="snake-legend__hint"><i class="fa-solid fa-utensils"></i> Come la respuesta correcta:</p>
                    <ol class="snake-options" id="snake-options">
                        <?php foreach ($answers as $i => $ans): ?>
                            <li class="snake-option" data-index="<?= $i ?>">
                                <span class="snake-option__num" style="background: var(--snake-color-<?= $i ?>)"><?= $i + 1 ?></span>
                                <code><?= e($ans['text']) ?></code>
                            </li>
                        <?php endforeach; ?>
                    </ol>
                </section>

                <section class="snake-feedback" id="snake-feedback" aria-live="polite"></section>

                <div class="snake-next" id="snake-next-actions" style="display:none;">
                    <?php if ($requestedLevel < TOTAL_LEVELS): ?>
                        <a class="sg-next-btn" href="snake.php?nivel=<?= e((string) $nextLevel) ?>">
                            <i class="fa-solid fa-forward-step"></i> Siguiente nivel
                        </a>
                    <?php else: ?>
                        <a class="sg-next-btn" href="leaderboard.php">
                            <i class="fa-solid fa-trophy"></i> Ver ranking final
                        </a>
                    <?php endif; ?>
                    <a class="sg-map-btn" href="dashboard.php">
                        <i class="fa-solid fa-map"></i> Volver al mapa
                    </a>
                </div>
            </aside>
            </div>
        </main>

        <!-- Modal de explicación -->
        <div class="guide-modal-backdrop" id="guide-modal" style="display:none;">
            <div class="guide-modal">
                <button class="guide-modal__close" id="guide-modal-close" type="button" aria-label="Cerrar">&times;</button>
                <h2><i class="fa-solid fa-lightbulb"></i> <?= e($guide['title']) ?></h2>
                <p class="guide-modal__explanation"><?= e($guide['explanation']) ?></p>
                <div class="guide-modal__example">
                    <span class="guide-modal__example-label">Ejemplo</span>
                    <code class="guide-modal__example-value"><?= e($guide['example']) ?></code>
                </div>
            </div>
        </div>
    </div>

    <script id="snake-level-data" type="application/json"><?= json_encode($snakeData, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) ?></script>
    <?php render_app_scripts(); ?>
    <script src="assets/js/snake.js"></script>
</body>
</html>

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

$isVip = is_user_vip($userId);

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
    'lives'      => $isVip ? -1 : (int) $progress['vidas'],
    'points'     => (int) $progress['puntos'],
    'vip'        => $isVip,
];

$speedMap = [
    'Básico'       => 160,
    'Intermedio 1'  => 140,
    'Intermedio 2'  => 120,
    'Avanzado 1'    => 105,
    'Avanzado 2'    => 90,
];
$snakeData['speed'] = $speedMap[$level['dificultad']] ?? 140;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <?php render_head(APP_NAME . ' | Víbora · Nivel ' . (string) $requestedLevel); ?>
    <link rel="stylesheet" href="assets/css/snake.css">
</head>
<body class="app-page snake-page">
    <div class="page-shell">
        <header class="site-header" data-reveal>
            <a class="brand" href="dashboard.php">
                <span class="brand__mark"><img src="assets/img/logo.png" alt="Excel Quest" width="46" height="46"></span>
                <span>
                    <strong>Excel Quest</strong>
                    <small>Modo Víbora</small>
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
                <span>Víbora</span>
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

        <header class="snake-header" data-reveal>
            <div class="snake-header__info">
                <span class="eyebrow"><?= e($level['categoria']) ?> · <?= e($level['dificultad']) ?></span>
                <h1>Nivel <?= e((string) $requestedLevel) ?> · <?= e($level['titulo']) ?></h1>
                <p class="snake-header__consigna"><?= e($level['consigna']) ?></p>
            </div>
            <div class="snake-header__stats">
                <span class="pill <?= e(difficulty_class((string) $level['dificultad'])) ?>"><?= e($level['dificultad']) ?></span>
                <span class="pill pill--neutral">+<?= e((string) $level['points_reward']) ?> pts</span>
                <span class="pill pill--neutral" id="snake-lives"><i class="fa-solid fa-heart"></i> <?= $isVip ? '∞' : e((string) $progress['vidas']) ?></span>
                <span class="pill pill--neutral" id="snake-points"><i class="fa-solid fa-star"></i> <?= e((string) $progress['puntos']) ?></span>
            </div>
        </header>

        <main class="snake-layout">
            <section class="snake-board-wrapper">
                <div class="snake-board" id="snake-board">
                    <canvas id="snake-canvas"></canvas>
                    <div class="snake-overlay" id="snake-overlay">
                        <div class="snake-overlay__content" id="snake-overlay-content">
                            <h2>🐍 Modo Víbora</h2>
                            <p>Mueve la víbora hasta la respuesta correcta.<br>Usa las flechas ← ↑ ↓ → o desliza en móvil.</p>
                            <button class="button button--primary" id="snake-start-btn" type="button">Comenzar</button>
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
                    <h3>Celda objetivo: <strong><?= e($level['formula_target']) ?></strong></h3>
                    <p class="snake-legend__hint">Come la respuesta correcta con la víbora:</p>
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
                        <a class="button button--primary button--wide" href="snake.php?nivel=<?= e((string) $nextLevel) ?>">Siguiente nivel</a>
                    <?php else: ?>
                        <a class="button button--primary button--wide" href="leaderboard.php">Ver ranking final</a>
                    <?php endif; ?>
                    <a class="button button--ghost button--wide" href="dashboard.php">Volver al mapa</a>
                </div>
            </aside>
        </main>
    </div>

    <script id="snake-level-data" type="application/json"><?= json_encode($snakeData, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) ?></script>
    <?php render_app_scripts(); ?>
    <script src="assets/js/snake.js"></script>
</body>
</html>

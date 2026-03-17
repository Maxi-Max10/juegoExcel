<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/functions.php';

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
$flash = get_flash();
$nextLevel = min(TOTAL_LEVELS, $requestedLevel + 1);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e(APP_NAME) ?> | Nivel <?= e((string) $requestedLevel) ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;700&family=Sora:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/styles.css">
</head>
<body class="app-page">
    <div class="page-shell">
        <header class="topbar">
            <div>
                <a class="eyebrow link-inline" href="dashboard.php">Volver al mapa</a>
                <h1>Nivel <?= e((string) $requestedLevel) ?> · <?= e($level['titulo']) ?></h1>
            </div>
            <div class="topbar__actions">
                <span class="pill <?= e(difficulty_class((string) $level['dificultad'])) ?>"><?= e($level['dificultad']) ?></span>
                <span class="pill pill--neutral">+<?= e((string) $level['points_reward']) ?> pts</span>
            </div>
        </header>

        <?php if ($flash): ?>
            <div class="flash flash--<?= e($flash['type']) ?>"><?= e($flash['message']) ?></div>
        <?php endif; ?>

        <main class="play-layout">
            <section class="play-panel">
                <div class="prompt-card">
                    <span class="eyebrow"><?= e($level['categoria']) ?></span>
                    <h2><?= e($level['consigna']) ?></h2>
                    <p>Escribe la fórmula exacta para colocarla en la celda <?= e($level['formula_target']) ?>.</p>
                </div>

                <?= render_excel_tables($tables, (string) $level['formula_target']) ?>
            </section>

            <aside class="play-sidebar">
                <section class="answer-card">
                    <h2>Tu respuesta</h2>
                    <form id="level-form" class="stacked-form" data-next-level="<?= e((string) $nextLevel) ?>" data-dashboard-url="dashboard.php" novalidate>
                        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                        <input type="hidden" name="level_id" value="<?= e((string) $level['id']) ?>">
                        <label>
                            <span>Fórmula</span>
                            <input type="text" name="formula" placeholder="=SUMA(B2:B6)" autocomplete="off" required>
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

                <section class="mini-stat-card">
                    <h3>Tu progreso</h3>
                    <p>Nivel actual desbloqueado: <?= e((string) $progress['nivel_actual']) ?></p>
                    <p>Puntos acumulados: <strong id="player-points"><?= e((string) $progress['puntos']) ?></strong></p>
                    <p>Vidas: <strong id="player-lives"><?= e((string) $progress['vidas']) ?></strong>/5</p>
                    <div class="progress-bar">
                        <div id="level-progress-fill" class="progress-bar__fill" style="width: <?= number_format(progress_percentage($progress), 2, '.', '') ?>%"></div>
                    </div>
                </section>

                <section class="hint-card">
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
    <script src="assets/js/app.js"></script>
</body>
</html>
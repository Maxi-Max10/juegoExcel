<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/layout.php';

require_login();

$userId = current_user_id();
$user = fetch_user_by_id((int) $userId);
$progress = get_user_progress((int) $userId);
$isVip = is_user_vip((int) $userId);
$levels = get_all_levels();
$statusMap = get_user_level_status_map((int) $userId);
$flash = get_flash();
$leaderboard = fetch_leaderboard(8);
$currentLevel = max(1, min(TOTAL_LEVELS, (int) $progress['nivel_actual']));
$progressPercent = number_format(progress_percentage($progress), 2, '.', '');
$previewSize = 12;
$previewStart = max(1, $currentLevel - 2);
$previewEnd = min(TOTAL_LEVELS, $previewStart + $previewSize - 1);
$previewStart = max(1, $previewEnd - $previewSize + 1);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <?php render_head(APP_NAME . ' | Panel'); ?>
    <style>
        .quick-stats{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:14px;margin-bottom:28px}
        .quick-stat{display:flex;align-items:center;gap:14px;padding:18px 20px;border-radius:20px;background:linear-gradient(135deg,rgba(30,41,59,.95),rgba(15,23,42,.95));border:1px solid rgba(148,163,184,.12);box-shadow:0 4px 16px rgba(0,0,0,.2);position:relative;overflow:hidden}
        .quick-stat__icon{display:grid;place-items:center;width:44px;height:44px;border-radius:14px;font-size:1.15rem;flex-shrink:0}
        .quick-stat--xp{border-left:3px solid #FBBF24}.quick-stat--xp .quick-stat__icon{background:rgba(250,204,21,.15);color:#FBBF24}
        .quick-stat--levels{border-left:3px solid #60A5FA}.quick-stat--levels .quick-stat__icon{background:rgba(59,130,246,.15);color:#60A5FA}
        .quick-stat--lives{border-left:3px solid #F87171}.quick-stat--lives .quick-stat__icon{background:rgba(239,68,68,.15);color:#F87171}
        .quick-stat--next{border-left:3px solid #34D399;text-decoration:none;color:inherit;background:linear-gradient(135deg,rgba(33,115,70,.25),rgba(15,23,42,.95));cursor:pointer}
        .quick-stat--next .quick-stat__icon{background:rgba(51,196,129,.2);color:#34D399}
        .quick-stat__body{display:flex;flex-direction:column;gap:2px}
        .quick-stat__label{font-size:.72rem;letter-spacing:.14em;text-transform:uppercase;color:rgba(148,163,184,.7)}
        .quick-stat__value{font-size:1.5rem;line-height:1;font-weight:800}
        .quick-stat__arrow{margin-left:auto;color:rgba(148,163,184,.5);font-size:.9rem}
        .xp-track{margin-bottom:32px;padding:16px 22px;border-radius:18px;background:rgba(255,255,255,.03);border:1px solid rgba(148,163,184,.08)}
        .xp-track__row{display:flex;justify-content:space-between;align-items:center;margin-bottom:10px}
        .xp-track__label{font-size:.82rem;font-weight:700;letter-spacing:.06em;text-transform:uppercase;color:rgba(148,163,184,.7)}
        .xp-track__pct{font-size:1.1rem;font-weight:800}
        @media(max-width:1240px){.quick-stats{grid-template-columns:repeat(2,minmax(0,1fr))}}
        @media(max-width:640px){.quick-stats{grid-template-columns:repeat(2,minmax(0,1fr))}.quick-stat{padding:14px 16px}.quick-stat__icon{width:38px;height:38px}}
    </style>
</head>
<body class="app-page">
    <div class="page-shell">
        <header class="site-header" data-reveal>
            <a class="brand" href="dashboard.php">
                <span class="brand__mark"><img src="assets/img/logo.png" alt="Excel Snake" width="46" height="46"></span>
                <span>
                    <strong>Excel Snake</strong>
                    <small>Panel de progreso</small>
                </span>
            </a>
            <nav class="site-nav site-nav--actions" id="main-nav">
                <a href="leaderboard.php">Ranking</a>
                <a href="logout.php">Salir</a>
            </nav>
            <button class="nav-toggle" type="button" aria-label="Menú" aria-expanded="false" data-nav-toggle>
                <span class="nav-toggle__bar"></span>
                <span class="nav-toggle__bar"></span>
                <span class="nav-toggle__bar"></span>
            </button>
        </header>

        <nav class="bottom-nav" aria-label="Navegación principal">
            <a href="dashboard.php" class="bottom-nav__item bottom-nav__item--active">
                <i class="fa-solid fa-map"></i>
                <span>Mapa</span>
            </a>
            <a href="snake.php?nivel=<?= e((string) $currentLevel) ?>" class="bottom-nav__item">
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

        <header class="topbar topbar--hero" data-reveal>
            <div>
                <span class="eyebrow"><i class="fa-solid fa-bolt"></i> Hola, <?= e($user['username'] ?? $_SESSION['username'] ?? 'Jugador') ?></span>
                <h1>Tu misión</h1>
            </div>
            <nav class="topbar__actions">
                <a class="button button--primary button--glow" href="snake.php?nivel=<?= e((string) $currentLevel) ?>"><i class="fa-solid fa-play"></i> Jugar ahora</a>
                <a class="button button--ghost" href="leaderboard.php"><i class="fa-solid fa-trophy"></i> Ranking</a>
            </nav>
        </header>

        <?php if ($flash): ?>
            <div class="flash flash--<?= e($flash['type']) ?>"><?= e($flash['message']) ?></div>
        <?php endif; ?>

        <section class="dashboard-hero-grid">
            <article class="focus-card" data-reveal>
                <div class="focus-card__copy">
                    <span class="eyebrow">🎯 Siguiente misión</span>
                    <h2>Nivel <?= e((string) $currentLevel) ?></h2>
                    <p><?= e(level_band_title($currentLevel)) ?> · Mueve la snake hasta la respuesta correcta.</p>
                    <div class="focus-card__actions">
                        <a class="button button--primary button--glow button--lg" href="snake.php?nivel=<?= e((string) $currentLevel) ?>">🐍 Jugar nivel <?= e((string) $currentLevel) ?></a>
                    </div>
                </div>
                <div class="focus-card__rings">
                    <div class="focus-ring" style="--progress: <?= e($progressPercent) ?>%;" aria-label="<?= e((string) number_format((float) $progressPercent, 0)) ?> por ciento completado">
                        <div class="focus-ring__inner">
                            <strong class="focus-ring__value"><?= e((string) number_format((float) $progressPercent, 0)) ?>%</strong>
                            <small>Completado</small>
                            <span class="focus-ring__meta"><?= e((string) $progress['niveles_completados']) ?> de <?= TOTAL_LEVELS ?> niveles</span>
                        </div>
                    </div>
                    <ul class="focus-list">
                        <li><i class="fa-solid fa-star"></i> <?= e((string) $progress['puntos']) ?> puntos</li>
                        <li><i class="fa-solid fa-heart"></i> <?= $isVip ? '∞ vidas <small>(VIP)</small>' : e((string) $progress['vidas']) . '/5 vidas' ?></li>
                        <li><i class="fa-solid fa-layer-group"></i> <?= e((string) $progress['niveles_completados']) ?> niveles resueltos</li>
                    </ul>
                </div>
            </article>
        </section>

        <section class="quick-stats" data-stagger-group>
            <div class="quick-stat quick-stat--xp" data-reveal-item>
                <div class="quick-stat__icon"><i class="fa-solid fa-bolt"></i></div>
                <div class="quick-stat__body">
                    <span class="quick-stat__label">XP Total</span>
                    <strong class="quick-stat__value"><?= e((string) $progress['puntos']) ?></strong>
                </div>
            </div>
            <div class="quick-stat quick-stat--levels" data-reveal-item>
                <div class="quick-stat__icon"><i class="fa-solid fa-layer-group"></i></div>
                <div class="quick-stat__body">
                    <span class="quick-stat__label">Niveles</span>
                    <strong class="quick-stat__value"><?= e((string) $progress['niveles_completados']) ?><small>/<?= TOTAL_LEVELS ?></small></strong>
                </div>
            </div>
            <div class="quick-stat quick-stat--lives" data-reveal-item>
                <div class="quick-stat__icon"><i class="fa-solid fa-heart"></i></div>
                <div class="quick-stat__body">
                    <span class="quick-stat__label">Vidas</span>
                    <?php if ($isVip): ?>
                        <strong class="quick-stat__value">∞ <small>VIP</small></strong>
                    <?php else: ?>
                        <strong class="quick-stat__value"><?= e((string) $progress['vidas']) ?><small>/5</small></strong>
                        <div class="lives-bar">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <span class="lives-bar__heart <?= $i <= (int) $progress['vidas'] ? 'is-full' : 'is-empty' ?>"><i class="fa-solid fa-heart"></i></span>
                            <?php endfor; ?>
                        </div>
                    <?php endif; ?>
                </div>
                <?php if (!$isVip && (int) $progress['vidas'] < 5 && !empty($progress['last_life_lost_at'])): ?>
                    <?php
                    $timerStmt = getPDO()->prepare('SELECT GREATEST(0, TIMESTAMPDIFF(SECOND, ?, NOW())) AS elapsed');
                    $timerStmt->execute([$progress['last_life_lost_at']]);
                    $secsElapsed = (int) $timerStmt->fetchColumn();
                    $secsInCycle = $secsElapsed % 900;
                    $secsLeft = 900 - $secsInCycle;
                    if ($secsLeft > 900) { $secsLeft = 900; }
                    ?>
                    <div class="lives-timer"><i class="fa-solid fa-clock"></i> <span id="life-timer" data-seconds="<?= $secsLeft ?>"><?= sprintf('%d:%02d', intdiv($secsLeft, 60), $secsLeft % 60) ?></span></div>
                <?php endif; ?>
            </div>
            <a href="snake.php?nivel=<?= e((string) $currentLevel) ?>" class="quick-stat quick-stat--next" data-reveal-item>
                <div class="quick-stat__icon"><i class="fa-solid fa-play"></i></div>
                <div class="quick-stat__body">
                    <span class="quick-stat__label">Siguiente</span>
                    <strong class="quick-stat__value">Nv. <?= e((string) $currentLevel) ?></strong>
                </div>
                <i class="fa-solid fa-chevron-right quick-stat__arrow"></i>
            </a>
        </section>

        <section class="xp-track" data-reveal>
            <div class="xp-track__row">
                <span class="xp-track__label"><i class="fa-solid fa-fire"></i> Progreso general</span>
                <span class="xp-track__pct"><?= number_format(progress_percentage($progress), 0) ?>%</span>
            </div>
            <div class="progress-bar progress-bar--large progress-bar--animated">
                <div class="progress-bar__fill" style="width: <?= number_format(progress_percentage($progress), 2, '.', '') ?>%"></div>
            </div>
        </section>

        <main class="dashboard-grid">
            <section class="levels-panel" data-reveal>
                <div class="section-heading levels-panel__heading">
                    <div>
                        <h2><i class="fa-solid fa-route"></i> Ruta de niveles</h2>
                        <p class="levels-panel__summary">Niveles <?= e((string) $previewStart) ?>–<?= e((string) $previewEnd) ?> · Tu progreso actual</p>
                    </div>
                    <button class="button button--ghost levels-panel__toggle" type="button" data-route-toggle data-label-expand="Ver los 100 niveles" data-label-collapse="Volver a resumen">
                        Ver los 100 niveles
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
                        $hiddenInPreview = $number < $previewStart || $number > $previewEnd;
                        ?>
                        <?php $href = $unlocked ? 'snake.php?nivel=' . e((string) $number) : '#'; ?>
                        <a href="<?= $href ?>" class="level-card <?= e($cardClass) ?><?= $hiddenInPreview ? ' level-card--preview-hidden' : '' ?>" data-level-card <?= !$unlocked ? 'tabindex="-1" aria-disabled="true"' : '' ?>>
                            <div class="level-card__header">
                                <span class="level-card__number">Nivel <?= e((string) $number) ?></span>
                                <span class="pill <?= e(difficulty_class((string) $level['dificultad'])) ?>"><?= e($level['dificultad']) ?></span>
                            </div>
                            <h3><?= e($level['titulo']) ?></h3>
                            <p class="level-card__category"><?= e($level['categoria']) ?></p>
                            <div class="level-card__footer">
                                <?php if ($completed): ?>
                                    <span class="level-card__status level-card__status--done"><i class="fa-solid fa-circle-check"></i> Completado</span>
                                <?php elseif ($unlocked): ?>
                                    <span class="level-card__status level-card__status--open"><i class="fa-solid fa-play"></i> Disponible</span>
                                <?php else: ?>
                                    <span class="level-card__status level-card__status--locked"><i class="fa-solid fa-lock"></i> Bloqueado</span>
                                <?php endif; ?>
                            </div>
                        </a>
                    <?php endforeach; ?>
                    </div>
                </div>
            </section>

            <aside class="side-panel">
                <section class="leaderboard-card" data-reveal>
                    <div class="section-heading">
                        <h2><i class="fa-solid fa-crown"></i> Top jugadores</h2>
                        <a href="leaderboard.php">Ver más</a>
                    </div>
                    <ol class="leaderboard-list">
                        <?php foreach ($leaderboard as $idx => $entry): ?>
                            <li>
                                <span class="lb-rank"><?= $idx + 1 ?></span>
                                <div>
                                    <strong><?= e($entry['username']) ?></strong>
                                    <span><?= e((string) $entry['niveles_completados']) ?> niveles</span>
                                </div>
                                <span class="lb-pts"><?= e((string) $entry['puntos']) ?></span>
                            </li>
                        <?php endforeach; ?>
                    </ol>
                </section>

                <section class="hint-card" data-reveal>
                    <h2><i class="fa-solid fa-lightbulb"></i> Tips rápidos</h2>
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
    <script>
    (function(){
        var el = document.getElementById('life-timer');
        if (!el) return;
        var secs = parseInt(el.dataset.seconds, 10);
        var iv = setInterval(function(){
            secs--;
            if (secs <= 0) { clearInterval(iv); location.reload(); return; }
            el.textContent = Math.floor(secs/60) + ':' + String(secs%60).padStart(2,'0');
        }, 1000);
    })();
    </script>
</body>
</html>
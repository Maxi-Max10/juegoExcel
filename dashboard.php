<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/layout.php';

require_login();

$userId = current_user_id();
$user = fetch_user_by_id((int) $userId);
$progress = get_user_progress((int) $userId);

// Check email verification status
$emailVerified = true;
$stmtVerif = getPDO()->prepare('SELECT email_verified FROM users WHERE id = ? LIMIT 1');
$stmtVerif->execute([$userId]);
$verifRow = $stmtVerif->fetch();
if ($verifRow && (int) $verifRow['email_verified'] === 0) {
    $emailVerified = false;
}

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
        /* ── Dashboard Ambient Background ── */
        .dash-ambient{position:fixed;inset:0;pointer-events:none;z-index:0;overflow:hidden}
        .dash-ambient__orb{position:absolute;border-radius:50%;filter:blur(80px);opacity:.5;will-change:transform}
        .dash-ambient__orb--1{width:400px;height:400px;top:-10%;left:-5%;background:rgba(33,115,70,.3);animation:dash-orb1 12s ease-in-out infinite alternate}
        .dash-ambient__orb--2{width:350px;height:350px;top:30%;right:-8%;background:rgba(59,130,246,.25);animation:dash-orb2 15s ease-in-out infinite alternate}
        .dash-ambient__orb--3{width:300px;height:300px;bottom:-5%;left:30%;background:rgba(250,204,21,.15);animation:dash-orb3 10s ease-in-out infinite alternate}
        .dash-ambient__orb--4{width:250px;height:250px;top:60%;left:10%;background:rgba(239,68,68,.12);animation:dash-orb4 18s ease-in-out infinite alternate}
        @keyframes dash-orb1{0%{transform:translate(0,0) scale(1)}100%{transform:translate(60px,40px) scale(1.15)}}
        @keyframes dash-orb2{0%{transform:translate(0,0) scale(1)}100%{transform:translate(-50px,30px) scale(1.2)}}
        @keyframes dash-orb3{0%{transform:translate(0,0) scale(1)}100%{transform:translate(40px,-30px) scale(1.1)}}
        @keyframes dash-orb4{0%{transform:translate(0,0) scale(1)}100%{transform:translate(-30px,-50px) scale(1.15)}}

        /* ── Floating Particles ── */
        .dash-particle{position:fixed;border-radius:50%;pointer-events:none;z-index:0;animation:dashParticleFloat linear infinite}
        @keyframes dashParticleFloat{0%{transform:translateY(100vh) rotate(0deg);opacity:0}10%{opacity:.7}90%{opacity:.7}100%{transform:translateY(-10vh) rotate(360deg);opacity:0}}

        /* ── Hero Welcome Card ── */
        .dash-welcome{position:relative;display:grid;grid-template-columns:1fr auto;align-items:center;gap:32px;padding:36px 40px;margin-bottom:28px;border-radius:var(--radius-xl);background:linear-gradient(135deg,rgba(17,24,39,.96),rgba(15,23,42,.92));border:1px solid rgba(51,196,129,.2);overflow:hidden;box-shadow:0 24px 70px rgba(2,6,23,.45)}
        .dash-welcome::before{content:'';position:absolute;top:-40%;right:-15%;width:500px;height:500px;border-radius:50%;background:radial-gradient(circle,rgba(51,196,129,.1),transparent 70%);pointer-events:none;animation:welcome-glow 6s ease-in-out infinite alternate}
        .dash-welcome::after{content:'';position:absolute;inset:0 0 auto;height:2px;background:linear-gradient(90deg,transparent,var(--primary-strong),var(--secondary),transparent);animation:accent-shimmer 3s ease-in-out infinite}
        @keyframes welcome-glow{0%{opacity:.5;transform:scale(1)}100%{opacity:1;transform:scale(1.15)}}
        .dash-welcome__greeting{font-size:.77rem;font-weight:800;letter-spacing:.18em;text-transform:uppercase;color:var(--accent);display:inline-flex;align-items:center;gap:10px;margin-bottom:10px}
        .dash-welcome__greeting::before{content:'';width:32px;height:2px;border-radius:999px;background:linear-gradient(90deg,var(--primary-strong),var(--secondary))}
        .dash-welcome__title{font-family:var(--font-display);font-size:clamp(2rem,5vw,3.2rem);line-height:.95;letter-spacing:-.03em;margin:0 0 8px;background:linear-gradient(135deg,#fff 40%,rgba(51,196,129,.8));-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text}
        .dash-welcome__subtitle{color:var(--muted);font-size:1rem;margin:0 0 20px;max-width:40ch}
        .dash-welcome__actions{display:flex;flex-wrap:wrap;gap:12px}
        .dash-welcome__ring{position:relative;justify-self:center}

        /* ── Stat Cards Grid ── */
        .dash-stats{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:16px;margin-bottom:28px}
        .dash-stat{position:relative;display:flex;align-items:center;gap:16px;padding:22px 24px;border-radius:22px;background:linear-gradient(135deg,rgba(30,41,59,.92),rgba(15,23,42,.95));border:1px solid rgba(148,163,184,.1);box-shadow:0 4px 20px rgba(0,0,0,.2);overflow:hidden;transition:transform 220ms ease,box-shadow 220ms ease,border-color 220ms ease}
        .dash-stat:hover{transform:translateY(-3px);box-shadow:0 14px 35px rgba(0,0,0,.25)}
        .dash-stat::after{content:'';position:absolute;inset:0 0 auto;height:1px;background:linear-gradient(90deg,transparent,rgba(255,255,255,.15),transparent);pointer-events:none}
        .dash-stat__icon{display:grid;place-items:center;width:50px;height:50px;border-radius:16px;font-size:1.2rem;flex-shrink:0;transition:transform .3s ease}
        .dash-stat:hover .dash-stat__icon{transform:scale(1.1)}
        .dash-stat__body{display:flex;flex-direction:column;gap:3px;min-width:0}
        .dash-stat__label{font-size:.72rem;letter-spacing:.14em;text-transform:uppercase;color:var(--muted)}
        .dash-stat__value{font-size:1.6rem;line-height:1;font-family:var(--font-display);font-weight:800}
        .dash-stat__value small{font-size:.65em;color:var(--muted);font-weight:400}
        .dash-stat--xp{border-left:3px solid #FBBF24}.dash-stat--xp .dash-stat__icon{background:rgba(250,204,21,.13);color:#FBBF24}
        .dash-stat--levels{border-left:3px solid #60A5FA}.dash-stat--levels .dash-stat__icon{background:rgba(59,130,246,.13);color:#60A5FA}
        .dash-stat--lives{border-left:3px solid #F87171}.dash-stat--lives .dash-stat__icon{background:rgba(239,68,68,.13);color:#F87171;animation:heartbeat 1.4s ease-in-out infinite}
        .dash-stat--next{text-decoration:none;color:inherit;background:linear-gradient(135deg,rgba(33,115,70,.2),rgba(15,23,42,.95));border-color:rgba(51,196,129,.25);border-left:3px solid #34D399;cursor:pointer}
        .dash-stat--next:hover{border-color:rgba(51,196,129,.5);box-shadow:0 14px 35px rgba(51,196,129,.12)}
        .dash-stat--next .dash-stat__icon{background:rgba(51,196,129,.15);color:#34D399}
        .dash-stat__arrow{margin-left:auto;color:var(--muted);font-size:.9rem;transition:transform 220ms ease,color 220ms ease}
        .dash-stat--next:hover .dash-stat__arrow{transform:translateX(4px);color:#34D399}

        /* ── Lives display in stat ── */
        .dash-stat .lives-bar{margin:4px 0 0}
        .dash-stat .lives-bar__heart{font-size:.85rem}
        .dash-stat .lives-timer{position:absolute;right:16px;top:50%;transform:translateY(-50%);padding:6px 12px;border-radius:10px;font-size:.82rem;background:rgba(239,68,68,.1);border:1px solid rgba(239,68,68,.2);display:flex;align-items:center;gap:6px;color:#FBBF24;font-family:var(--font-display);font-weight:700}
        .dash-stat .lives-timer i{color:#F97316;animation:pulse-glow 2s ease-in-out infinite}

        /* ── XP Progress Track ── */
        .dash-xp-track{margin-bottom:32px;padding:20px 26px;border-radius:22px;background:linear-gradient(135deg,rgba(30,41,59,.6),rgba(15,23,42,.7));border:1px solid rgba(148,163,184,.08);box-shadow:0 4px 16px rgba(0,0,0,.15);position:relative;overflow:hidden}
        .dash-xp-track::after{content:'';position:absolute;inset:0 0 auto;height:1px;background:linear-gradient(90deg,transparent,rgba(255,255,255,.12),transparent);pointer-events:none}
        .dash-xp-track__row{display:flex;justify-content:space-between;align-items:center;margin-bottom:12px}
        .dash-xp-track__label{font-size:.82rem;font-weight:700;letter-spacing:.06em;text-transform:uppercase;color:var(--muted);display:flex;align-items:center;gap:8px}
        .dash-xp-track__label i{color:#F97316;font-size:.9rem}
        .dash-xp-track__pct{font-size:1.15rem;font-weight:800;font-family:var(--font-display);background:linear-gradient(135deg,var(--primary-strong),var(--accent));-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text}

        /* ── Dashboard Main Grid ── */
        .dash-main{display:grid;grid-template-columns:minmax(0,1.5fr) minmax(320px,.64fr);gap:22px;align-items:start}

        /* ── Levels Panel ── */
        .dash-levels{padding:30px;border-radius:var(--radius-xl);background:var(--paper);border:1px solid var(--line);backdrop-filter:blur(18px);box-shadow:var(--shadow-lg);position:relative;overflow:hidden}
        .dash-levels::after{content:'';position:absolute;inset:0 0 auto;height:1px;background:linear-gradient(90deg,transparent,rgba(255,255,255,.3),transparent);pointer-events:none}
        .dash-levels__heading{display:flex;align-items:start;justify-content:space-between;gap:12px;flex-wrap:wrap;margin-bottom:16px}
        .dash-levels__heading h2{margin:0;font-family:var(--font-display);letter-spacing:-.03em;font-size:1.3rem}
        .dash-levels__summary{margin:8px 0 0;color:var(--muted);max-width:54ch;font-size:.9rem}

        /* ── Side Panel ── */
        .dash-sidebar{display:grid;gap:22px}

        /* ── Leaderboard Card ── */
        .dash-leaderboard{padding:28px;border-radius:var(--radius-xl);background:var(--paper);border:1px solid var(--line);backdrop-filter:blur(18px);box-shadow:var(--shadow-lg);position:relative;overflow:hidden}
        .dash-leaderboard::after{content:'';position:absolute;inset:0 0 auto;height:1px;background:linear-gradient(90deg,transparent,rgba(255,255,255,.3),transparent);pointer-events:none}
        .dash-leaderboard__head{display:flex;align-items:center;justify-content:space-between;gap:12px;margin-bottom:16px}
        .dash-leaderboard__head h2{margin:0;font-family:var(--font-display);letter-spacing:-.03em;font-size:1.2rem}
        .dash-leaderboard__head a{color:var(--muted);font-size:.85rem;font-weight:600;transition:color .2s}
        .dash-leaderboard__head a:hover{color:var(--ink)}

        /* ── Tips Card ── */
        .dash-tips{padding:28px;border-radius:var(--radius-xl);background:linear-gradient(135deg,rgba(17,24,39,.92),rgba(15,23,42,.96));border:1px solid rgba(250,204,21,.12);backdrop-filter:blur(18px);box-shadow:var(--shadow-lg);position:relative;overflow:hidden}
        .dash-tips::before{content:'';position:absolute;bottom:-40%;right:-20%;width:200px;height:200px;border-radius:50%;background:radial-gradient(circle,rgba(250,204,21,.08),transparent 70%);pointer-events:none}
        .dash-tips::after{content:'';position:absolute;inset:0 0 auto;height:1px;background:linear-gradient(90deg,transparent,rgba(250,204,21,.25),transparent);pointer-events:none}
        .dash-tips h2{margin:0 0 16px;font-family:var(--font-display);letter-spacing:-.03em;font-size:1.15rem;color:#FBBF24}
        .dash-tips ul{list-style:none;margin:0;padding:0;display:grid;gap:10px}
        .dash-tips li{display:flex;align-items:baseline;gap:10px;color:var(--muted);font-size:.9rem;line-height:1.5}
        .dash-tips li::before{content:'';width:5px;height:5px;border-radius:50%;background:#FBBF24;flex-shrink:0;margin-top:6px}

        /* ── Achievement Badge ── */
        .dash-achievement{padding:22px 26px;border-radius:var(--radius-xl);background:linear-gradient(135deg,rgba(139,92,246,.12),rgba(15,23,42,.95));border:1px solid rgba(139,92,246,.2);position:relative;overflow:hidden;display:flex;align-items:center;gap:18px}
        .dash-achievement::after{content:'';position:absolute;inset:0 0 auto;height:1px;background:linear-gradient(90deg,transparent,rgba(139,92,246,.3),transparent);pointer-events:none}
        .dash-achievement__icon{width:48px;height:48px;display:grid;place-items:center;border-radius:14px;background:rgba(139,92,246,.15);color:#A78BFA;font-size:1.3rem;flex-shrink:0}
        .dash-achievement__body{min-width:0}
        .dash-achievement__title{font-weight:800;font-size:.95rem;margin:0 0 2px}
        .dash-achievement__desc{color:var(--muted);font-size:.82rem;margin:0}

        /* ── Responsiveness ── */
        @media(max-width:1240px){
            .dash-stats{grid-template-columns:repeat(2,minmax(0,1fr))}
            .dash-main{grid-template-columns:1fr}
        }
        @media(max-width:768px){
            .dash-welcome{grid-template-columns:1fr;text-align:center;padding:28px 22px}
            .dash-welcome__greeting{justify-content:center}
            .dash-welcome__subtitle{margin-left:auto;margin-right:auto}
            .dash-welcome__actions{justify-content:center}
            .dash-welcome__ring{margin-bottom:8px}
        }
        @media(max-width:640px){
            .dash-stats{grid-template-columns:repeat(2,minmax(0,1fr))}
            .dash-stat{padding:16px 18px}
            .dash-stat__icon{width:42px;height:42px}
            .dash-stat__value{font-size:1.35rem}
        }
    </style>
</head>
<body class="app-page">
    <!-- ═══ Ambient Background ═══ -->
    <div class="dash-ambient" aria-hidden="true">
        <div class="dash-ambient__orb dash-ambient__orb--1"></div>
        <div class="dash-ambient__orb dash-ambient__orb--2"></div>
        <div class="dash-ambient__orb dash-ambient__orb--3"></div>
        <div class="dash-ambient__orb dash-ambient__orb--4"></div>
    </div>

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

        <?php if ($flash): ?>
            <div class="flash flash--<?= e($flash['type']) ?>"><?= e($flash['message']) ?></div>
        <?php endif; ?>

        <?php if (!$emailVerified): ?>
            <div class="verification-banner" id="verification-banner">
                <i class="fa-solid fa-envelope-circle-check"></i>
                <span>Tu correo aún no está verificado. Revisa tu bandeja de entrada.</span>
                <a href="#" id="resend-verification">Reenviar correo</a>
                <button type="button" id="dismiss-verification" aria-label="Cerrar" title="Cerrar">&times;</button>
            </div>
        <?php endif; ?>

        <!-- ═══ HERO WELCOME ═══ -->
        <section class="dash-welcome" data-reveal>
            <div>
                <span class="dash-welcome__greeting"><i class="fa-solid fa-bolt"></i> Hola, <?= e($user['username'] ?? $_SESSION['username'] ?? 'Jugador') ?></span>
                <h1 class="dash-welcome__title">Tu misión</h1>
                <p class="dash-welcome__subtitle"><?= e(level_band_title($currentLevel)) ?> · Nivel <?= e((string) $currentLevel) ?> te espera. Mueve la snake hasta la respuesta correcta.</p>
                <div class="dash-welcome__actions">
                    <a class="button button--primary button--glow button--lg" href="snake.php?nivel=<?= e((string) $currentLevel) ?>"><i class="fa-solid fa-play"></i> Jugar nivel <?= e((string) $currentLevel) ?></a>
                    <a class="button button--ghost" href="leaderboard.php"><i class="fa-solid fa-trophy"></i> Ranking</a>
                </div>
            </div>
            <div class="dash-welcome__ring">
                <div class="focus-ring" style="--progress: <?= e($progressPercent) ?>%;" aria-label="<?= e((string) number_format((float) $progressPercent, 0)) ?> por ciento completado">
                    <div class="focus-ring__inner">
                        <strong class="focus-ring__value"><?= e((string) number_format((float) $progressPercent, 0)) ?>%</strong>
                        <small>Completado</small>
                        <span class="focus-ring__meta"><?= e((string) $progress['niveles_completados']) ?> de <?= TOTAL_LEVELS ?> niveles</span>
                    </div>
                </div>
            </div>
        </section>

        <!-- ═══ QUICK STATS ═══ -->
        <section class="dash-stats" data-stagger-group>
            <div class="dash-stat dash-stat--xp" data-reveal-item>
                <div class="dash-stat__icon"><i class="fa-solid fa-bolt"></i></div>
                <div class="dash-stat__body">
                    <span class="dash-stat__label">XP Total</span>
                    <strong class="dash-stat__value"><?= e((string) $progress['puntos']) ?></strong>
                </div>
            </div>
            <div class="dash-stat dash-stat--levels" data-reveal-item>
                <div class="dash-stat__icon"><i class="fa-solid fa-layer-group"></i></div>
                <div class="dash-stat__body">
                    <span class="dash-stat__label">Niveles</span>
                    <strong class="dash-stat__value"><?= e((string) $progress['niveles_completados']) ?><small>/<?= TOTAL_LEVELS ?></small></strong>
                </div>
            </div>
            <div class="dash-stat dash-stat--lives" data-reveal-item>
                <div class="dash-stat__icon"><i class="fa-solid fa-heart"></i></div>
                <div class="dash-stat__body">
                    <span class="dash-stat__label">Vidas</span>
                    <strong class="dash-stat__value"><?= e((string) $progress['vidas']) ?><small>/5</small></strong>
                    <div class="lives-bar">
                        <?php for ($i = 1; $i <= 5; $i++): ?>
                            <span class="lives-bar__heart <?= $i <= (int) $progress['vidas'] ? 'is-full' : 'is-empty' ?>"><i class="fa-solid fa-heart"></i></span>
                        <?php endfor; ?>
                    </div>
                </div>
                <?php if ((int) $progress['vidas'] < 5 && !empty($progress['last_life_lost_at'])): ?>
                    <?php
                    $timerStmt = getPDO()->prepare('SELECT GREATEST(0, TIMESTAMPDIFF(SECOND, ?, NOW())) AS elapsed');
                    $timerStmt->execute([$progress['last_life_lost_at']]);
                    $secsElapsed = (int) $timerStmt->fetchColumn();
                    $secsInCycle = $secsElapsed % 120;
                    $secsLeft = 120 - $secsInCycle;
                    if ($secsLeft > 120) { $secsLeft = 120; }
                    ?>
                    <div class="lives-timer"><i class="fa-solid fa-clock"></i> <span id="life-timer" data-seconds="<?= $secsLeft ?>"><?= sprintf('%d:%02d', intdiv($secsLeft, 60), $secsLeft % 60) ?></span></div>
                <?php endif; ?>
            </div>
            <a href="snake.php?nivel=<?= e((string) $currentLevel) ?>" class="dash-stat dash-stat--next" data-reveal-item>
                <div class="dash-stat__icon"><i class="fa-solid fa-play"></i></div>
                <div class="dash-stat__body">
                    <span class="dash-stat__label">Siguiente</span>
                    <strong class="dash-stat__value">Nv. <?= e((string) $currentLevel) ?></strong>
                </div>
                <i class="fa-solid fa-chevron-right dash-stat__arrow"></i>
            </a>
        </section>

        <!-- ═══ XP PROGRESS TRACK ═══ -->
        <section class="dash-xp-track" data-reveal>
            <div class="dash-xp-track__row">
                <span class="dash-xp-track__label"><i class="fa-solid fa-fire"></i> Progreso general</span>
                <span class="dash-xp-track__pct"><?= number_format(progress_percentage($progress), 0) ?>%</span>
            </div>
            <div class="progress-bar progress-bar--large progress-bar--animated">
                <div class="progress-bar__fill" style="width: <?= number_format(progress_percentage($progress), 2, '.', '') ?>%"></div>
            </div>
        </section>

        <!-- ═══ MAIN GRID ═══ -->
        <main class="dash-main">
            <!-- Levels Panel -->
            <section class="dash-levels" data-reveal>
                <div class="dash-levels__heading">
                    <div>
                        <h2><i class="fa-solid fa-route"></i> Ruta de niveles</h2>
                        <p class="dash-levels__summary">Niveles <?= e((string) $previewStart) ?>–<?= e((string) $previewEnd) ?> · Tu progreso actual</p>
                    </div>
                    <button class="button button--ghost levels-panel__toggle" type="button" data-route-toggle data-label-expand="Ver los <?= TOTAL_LEVELS ?> niveles" data-label-collapse="Volver a resumen">
                        Ver los <?= TOTAL_LEVELS ?> niveles
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

            <!-- Sidebar -->
            <aside class="dash-sidebar">
                <!-- Leaderboard -->
                <section class="dash-leaderboard" data-reveal>
                    <div class="dash-leaderboard__head">
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

                <!-- Achievement -->
                <?php
                $completedCount = (int) $progress['niveles_completados'];
                $achievementIcon = 'fa-seedling';
                $achievementTitle = 'Principiante';
                $achievementDesc = 'Completa tu primer nivel para empezar tu camino.';
                if ($completedCount >= 100) {
                    $achievementIcon = 'fa-gem';
                    $achievementTitle = 'Maestro Excel';
                    $achievementDesc = 'Has completado más de 100 niveles. Eres imparable.';
                } elseif ($completedCount >= 50) {
                    $achievementIcon = 'fa-fire-flame-curved';
                    $achievementTitle = 'En llamas';
                    $achievementDesc = 'Más de 50 niveles completados. Tu dominio crece.';
                } elseif ($completedCount >= 20) {
                    $achievementIcon = 'fa-medal';
                    $achievementTitle = 'Avanzado';
                    $achievementDesc = 'Ya dominas más de 20 niveles. Sigue así.';
                } elseif ($completedCount >= 5) {
                    $achievementIcon = 'fa-star';
                    $achievementTitle = 'Explorador';
                    $achievementDesc = 'Has completado 5+ niveles. Vas por buen camino.';
                } elseif ($completedCount >= 1) {
                    $achievementIcon = 'fa-flag-checkered';
                    $achievementTitle = 'Primer paso';
                    $achievementDesc = 'Completaste tu primer nivel. El inicio de algo grande.';
                }
                ?>
                <div class="dash-achievement" data-reveal>
                    <div class="dash-achievement__icon"><i class="fa-solid <?= $achievementIcon ?>"></i></div>
                    <div class="dash-achievement__body">
                        <p class="dash-achievement__title"><?= $achievementTitle ?></p>
                        <p class="dash-achievement__desc"><?= $achievementDesc ?></p>
                    </div>
                </div>

                <!-- Tips -->
                <section class="dash-tips" data-reveal>
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
        // Floating particles
        var colors = ['#34D399','#3b82f6','#fbbf24','#a855f7','#f87171','#f97316'];
        for (var i = 0; i < 14; i++) {
            var p = document.createElement('div');
            p.className = 'dash-particle';
            var size = 3 + Math.random() * 6;
            var dur = 10 + Math.random() * 16;
            var delay = Math.random() * 12;
            var left = Math.random() * 100;
            p.style.cssText = 'width:'+size+'px;height:'+size+'px;left:'+left+'%;background:'+colors[i%colors.length]+';animation-duration:'+dur+'s;animation-delay:'+delay+'s;opacity:0;';
            document.body.appendChild(p);
        }
    })();

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

    (function(){
        var banner = document.getElementById('verification-banner');
        if (!banner) return;

        // Hide if previously dismissed
        if (localStorage.getItem('dismiss_email_banner') === '1') {
            banner.style.display = 'none';
            return;
        }

        var dismissBtn = document.getElementById('dismiss-verification');
        if (dismissBtn) {
            dismissBtn.addEventListener('click', function() {
                banner.style.display = 'none';
                localStorage.setItem('dismiss_email_banner', '1');
            });
        }

        var resendLink = document.getElementById('resend-verification');
        if (!resendLink) return;
        resendLink.addEventListener('click', function(e) {
            e.preventDefault();
            resendLink.textContent = 'Enviando...';
            resendLink.style.pointerEvents = 'none';
            var fd = new FormData();
            fd.append('csrf_token', '<?= e(csrf_token()) ?>');
            fetch('resend_verification.php', {
                method: 'POST',
                body: fd,
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(function(r) { return r.json(); })
            .then(function(d) {
                banner.querySelector('span').textContent = d.message;
                resendLink.textContent = 'Reenviar correo';
                setTimeout(function() { resendLink.style.pointerEvents = ''; }, 120000);
            })
            .catch(function() {
                resendLink.textContent = 'Reenviar correo';
                resendLink.style.pointerEvents = '';
            });
        });
    })();
    </script>
</body>
</html>

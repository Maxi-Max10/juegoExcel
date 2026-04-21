<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/layout.php';

$leaderboard = fetch_leaderboard(25);
$flash = get_flash();
$podium = array_slice($leaderboard, 0, 3);
$rest = array_slice($leaderboard, 3);
$currentUser = is_logged_in() ? ($_SESSION['username'] ?? '') : '';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <?php render_head(APP_NAME . ' | Ranking', 'Ranking global de Excel Snake: los mejores jugadores clasificados por puntos y niveles completados. ¿Puedes llegar al top? Regístrate gratis y compite.'); ?>
    <style>
        /* ── Leaderboard Gaming Styles ── */
        .lb-canvas { position: fixed; inset: 0; z-index: 0; pointer-events: none; }

        .lb-title-section { text-align: center; padding: 2rem 0 1rem; position: relative; z-index: 1; }
        .lb-title-section h1 { font-size: clamp(1.8rem, 5vw, 3rem); font-weight: 900; margin: 0; }
        .lb-title-section h1 i { color: #fbbf24; }
        .lb-title-section .lb-subtitle { color: #94a3b8; margin-top: 0.5rem; font-size: 1rem; }

        /* Podium */
        .lb-podium { display: flex; justify-content: center; align-items: flex-end; gap: 1rem; padding: 2rem 0 1rem; position: relative; z-index: 1; }
        .lb-podium-card { 
            display: flex; flex-direction: column; align-items: center; gap: 0.5rem;
            padding: 1.5rem 1.25rem 1.25rem; border-radius: 1.25rem; position: relative;
            background: rgba(30, 41, 59, 0.7); border: 1px solid rgba(255,255,255,0.06);
            backdrop-filter: blur(12px); width: 160px; transition: transform 0.3s ease;
        }
        .lb-podium-card:hover { transform: translateY(-8px) scale(1.04); }
        .lb-podium-card--1 { order: 2; border-color: rgba(250, 204, 21, 0.35); box-shadow: 0 0 40px rgba(250, 204, 21, 0.15); }
        .lb-podium-card--2 { order: 1; }
        .lb-podium-card--3 { order: 3; }

        .lb-podium-crown { font-size: 2rem; position: absolute; top: -1.2rem; animation: crownBounce 2s ease-in-out infinite; }
        @keyframes crownBounce { 0%, 100% { transform: translateY(0); } 50% { transform: translateY(-6px); } }

        .lb-podium-rank {
            width: 48px; height: 48px; border-radius: 50%; display: flex; align-items: center; justify-content: center;
            font-size: 1.3rem; font-weight: 900; color: #0f172a;
        }
        .lb-podium-card--1 .lb-podium-rank { background: linear-gradient(135deg, #fbbf24, #f59e0b); box-shadow: 0 0 20px rgba(250,204,21,0.4); }
        .lb-podium-card--2 .lb-podium-rank { background: linear-gradient(135deg, #cbd5e1, #94a3b8); box-shadow: 0 0 16px rgba(148,163,184,0.3); }
        .lb-podium-card--3 .lb-podium-rank { background: linear-gradient(135deg, #f97316, #ea580c); box-shadow: 0 0 16px rgba(249,115,22,0.3); }

        .lb-podium-name { font-weight: 700; font-size: 0.95rem; color: var(--ink); text-align: center; word-break: break-all; max-width: 140px; }
        .lb-podium-pts { font-size: 1.4rem; font-weight: 900; color: #fbbf24; }
        .lb-podium-levels { font-size: 0.8rem; color: #94a3b8; }
        .lb-podium-bar {
            width: 100%; height: 6px; border-radius: 3px; background: rgba(255,255,255,0.06); overflow: hidden; margin-top: 0.25rem;
        }
        .lb-podium-bar span { display: block; height: 100%; border-radius: 3px; transition: width 1.5s cubic-bezier(0.22,1,0.36,1); }
        .lb-podium-card--1 .lb-podium-bar span { background: linear-gradient(90deg, #fbbf24, #f59e0b); }
        .lb-podium-card--2 .lb-podium-bar span { background: linear-gradient(90deg, #cbd5e1, #94a3b8); }
        .lb-podium-card--3 .lb-podium-bar span { background: linear-gradient(90deg, #f97316, #ea580c); }

        /* Player list */
        .lb-list { position: relative; z-index: 1; max-width: 640px; margin: 1.5rem auto 3rem; display: flex; flex-direction: column; gap: 0.5rem; padding: 0 1rem; }
        .lb-row {
            display: grid; grid-template-columns: 48px 1fr auto; align-items: center; gap: 1rem;
            padding: 0.8rem 1rem; border-radius: 0.75rem; background: rgba(30, 41, 59, 0.5);
            border: 1px solid rgba(255,255,255,0.04); transition: transform 0.2s, background 0.2s, box-shadow 0.2s;
            opacity: 0; transform: translateX(-30px);
            animation: rowSlideIn 0.4s ease forwards;
        }
        .lb-row:hover { transform: translateX(4px); background: rgba(30, 41, 59, 0.8); box-shadow: 0 4px 20px rgba(0,0,0,0.2); }
        .lb-row--me { border-color: rgba(59, 130, 246, 0.4); background: rgba(59, 130, 246, 0.08); }
        .lb-row--me:hover { background: rgba(59, 130, 246, 0.14); }

        @keyframes rowSlideIn { to { opacity: 1; transform: translateX(0); } }

        .lb-row-rank { font-weight: 800; font-size: 1rem; color: #64748b; text-align: center; }
        .lb-row-name { font-weight: 600; font-size: 0.95rem; color: var(--ink); }
        .lb-row-name small { display: block; font-weight: 400; font-size: 0.78rem; color: #64748b; }
        .lb-row-score { text-align: right; font-weight: 800; font-size: 1rem; color: #fbbf24; }
        .lb-row-score small { display: block; font-size: 0.75rem; font-weight: 400; color: #94a3b8; }

        .lb-empty { text-align: center; color: #64748b; padding: 2rem; font-size: 1rem; }

        /* Floating particles */
        .lb-particle {
            position: fixed; border-radius: 50%; pointer-events: none; z-index: 0;
            animation: particleFloat linear infinite;
        }
        @keyframes particleFloat {
            0% { transform: translateY(100vh) rotate(0deg); opacity: 0; }
            10% { opacity: 1; }
            90% { opacity: 1; }
            100% { transform: translateY(-10vh) rotate(360deg); opacity: 0; }
        }

        @media (max-width: 600px) {
            .lb-podium { gap: 0.5rem; }
            .lb-podium-card { width: 110px; padding: 1.25rem 0.75rem 1rem; }
            .lb-podium-rank { width: 38px; height: 38px; font-size: 1.1rem; }
            .lb-podium-pts { font-size: 1.1rem; }
            .lb-podium-name { font-size: 0.8rem; }
        }
    </style>
</head>
<body class="app-page">
    <div class="page-shell">
        <header class="site-header" data-reveal>
            <a class="brand" href="index.php">
                <span class="brand__mark"><img src="assets/img/logo.png" alt="Excel Snake" width="46" height="46"></span>
                <span>
                    <strong>Excel Snake</strong>
                    <small>Ranking global</small>
                </span>
            </a>
            <nav class="site-nav site-nav--actions" id="main-nav">
                <a href="dashboard.php">Mapa</a>
                <?php if (is_logged_in()): ?>
                    <a href="logout.php">Salir</a>
                <?php else: ?>
                    <a href="index.php">Entrar</a>
                <?php endif; ?>
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
            <a href="leaderboard.php" class="bottom-nav__item bottom-nav__item--active">
                <i class="fa-solid fa-trophy"></i>
                <span>Ranking</span>
            </a>
            <?php if (is_logged_in()): ?>
                <a href="logout.php" class="bottom-nav__item">
                    <i class="fa-solid fa-right-from-bracket"></i>
                    <span>Salir</span>
                </a>
            <?php else: ?>
                <a href="index.php" class="bottom-nav__item">
                    <i class="fa-solid fa-right-to-bracket"></i>
                    <span>Entrar</span>
                </a>
            <?php endif; ?>
        </nav>

        <?php if ($flash): ?>
            <div class="flash flash--<?= e($flash['type']) ?>"><?= e($flash['message']) ?></div>
        <?php endif; ?>

        <!-- Título animado -->
        <section class="lb-title-section">
            <h1><i class="fa-solid fa-trophy"></i> Ranking Global</h1>
            <p class="lb-subtitle">Los mejores dominadores de fórmulas Excel</p>
        </section>

        <!-- Podium Top 3 -->
        <?php if (count($podium) >= 3): ?>
        <section class="lb-podium">
            <?php foreach ($podium as $i => $entry):
                $maxPts = max(1, (int) $podium[0]['puntos']);
                $pct = round((int) $entry['puntos'] / $maxPts * 100);
            ?>
                <article class="lb-podium-card lb-podium-card--<?= $i + 1 ?>" style="animation-delay: <?= $i * 0.15 ?>s">
                    <?php if ($i === 0): ?><span class="lb-podium-crown">👑</span><?php endif; ?>
                    <span class="lb-podium-rank"><?= $i + 1 ?></span>
                    <span class="lb-podium-name"><?= e($entry['username']) ?></span>
                    <span class="lb-podium-pts"><?= number_format((int) $entry['puntos']) ?></span>
                    <span class="lb-podium-levels"><i class="fa-solid fa-layer-group"></i> <?= e((string) $entry['niveles_completados']) ?> niveles</span>
                    <div class="lb-podium-bar"><span style="width: <?= $pct ?>%"></span></div>
                </article>
            <?php endforeach; ?>
        </section>
        <?php endif; ?>

        <!-- Rest of players -->
        <?php if (!empty($rest)): ?>
        <section class="lb-list">
            <?php foreach ($rest as $i => $entry):
                $rank = $i + 4;
                $isMe = ($currentUser !== '' && $entry['username'] === $currentUser);
            ?>
                <div class="lb-row<?= $isMe ? ' lb-row--me' : '' ?>" style="animation-delay: <?= 0.45 + $i * 0.06 ?>s">
                    <span class="lb-row-rank">#<?= $rank ?></span>
                    <span class="lb-row-name">
                        <?= e($entry['username']) ?>
                        <small><?= e((string) $entry['niveles_completados']) ?> niveles</small>
                    </span>
                    <span class="lb-row-score">
                        <?= number_format((int) $entry['puntos']) ?>
                        <small>pts</small>
                    </span>
                </div>
            <?php endforeach; ?>
        </section>
        <?php elseif (empty($podium)): ?>
            <p class="lb-empty">Aún no hay jugadores. ¡Sé el primero!</p>
        <?php endif; ?>

    </div>
    <?php render_app_scripts(); ?>
    <script>
    (function() {
        // Floating particles
        const colors = ['#fbbf24','#3b82f6','#22c55e','#a855f7','#ef4444','#f97316'];
        const body = document.body;
        for (let i = 0; i < 18; i++) {
            const p = document.createElement('div');
            p.className = 'lb-particle';
            const size = 4 + Math.random() * 8;
            const dur = 8 + Math.random() * 14;
            const delay = Math.random() * 10;
            const left = Math.random() * 100;
            p.style.cssText = `width:${size}px;height:${size}px;left:${left}%;background:${colors[i % colors.length]};animation-duration:${dur}s;animation-delay:${delay}s;opacity:0;`;
            body.appendChild(p);
        }

        // Counter animation on podium points
        document.querySelectorAll('.lb-podium-pts').forEach(el => {
            const target = parseInt(el.textContent.replace(/,/g, ''), 10);
            if (isNaN(target)) return;
            let current = 0;
            const step = Math.ceil(target / 60);
            el.textContent = '0';
            const timer = setInterval(() => {
                current += step;
                if (current >= target) { current = target; clearInterval(timer); }
                el.textContent = current.toLocaleString();
            }, 20);
        });
    })();
    </script>
</body>
</html>
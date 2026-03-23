<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/layout.php';

$leaderboard = fetch_leaderboard(25);
$flash = get_flash();
$podium = array_slice($leaderboard, 0, 3);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <?php render_head(APP_NAME . ' | Ranking'); ?>
</head>
<body class="app-page">
    <div class="page-shell">
        <header class="site-header" data-reveal>
            <a class="brand" href="index.php">
                <span class="brand__mark"><img src="assets/img/logo.png" alt="Excel Quest" width="28" height="28"></span>
                <span>
                    <strong>Excel Quest</strong>
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

        <header class="topbar topbar--hero" data-reveal>
            <div>
                <span class="eyebrow">Competencia sana</span>
                <h1>Ranking de jugadores</h1>
                <p class="topbar__lead">Los mejores puestos premian precisión y constancia. El empate se resuelve por niveles completados.</p>
            </div>
            <nav class="topbar__actions">
                <a class="button button--ghost" href="dashboard.php">Mapa</a>
                <?php if (is_logged_in()): ?>
                    <a class="button button--ghost" href="logout.php">Salir</a>
                <?php else: ?>
                    <a class="button button--ghost" href="index.php">Entrar</a>
                <?php endif; ?>
            </nav>
        </header>

        <?php if ($flash): ?>
            <div class="flash flash--<?= e($flash['type']) ?>"><?= e($flash['message']) ?></div>
        <?php endif; ?>

        <section class="leaderboard-hero" data-reveal>
            <h2>Los mejores puntajes combinan precisión, constancia y dominio de fórmulas.</h2>
            <p>Se ordena por puntos totales y luego por niveles completados.</p>
        </section>

        <section class="podium-grid" data-stagger-group>
            <?php foreach ($podium as $index => $entry): ?>
                <article class="podium-card podium-card--<?= e((string) ($index + 1)) ?>" data-reveal-item>
                    <span class="podium-card__place">#<?= e((string) ($index + 1)) ?></span>
                    <h3><?= e($entry['username']) ?></h3>
                    <p><?= e((string) $entry['puntos']) ?> pts · <?= e((string) $entry['niveles_completados']) ?> niveles</p>
                </article>
            <?php endforeach; ?>
        </section>

        <section class="leaderboard-table-card" data-reveal>
            <div class="table-scroll">
                <table class="leaderboard-table">
                    <thead>
                        <tr>
                            <th>Posición</th>
                            <th>Jugador</th>
                            <th>Puntos</th>
                            <th>Niveles completados</th>
                            <th>Nivel desbloqueado</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($leaderboard as $index => $entry): ?>
                            <tr>
                                <td>#<?= e((string) ($index + 1)) ?></td>
                                <td><?= e($entry['username']) ?></td>
                                <td><?= e((string) $entry['puntos']) ?></td>
                                <td><?= e((string) $entry['niveles_completados']) ?></td>
                                <td><?= e((string) $entry['nivel_actual']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </div>
    <?php render_app_scripts(); ?>
</body>
</html>
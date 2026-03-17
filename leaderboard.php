<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/functions.php';

$leaderboard = fetch_leaderboard(25);
$flash = get_flash();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e(APP_NAME) ?> | Ranking</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;700&family=Sora:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/styles.css">
</head>
<body class="app-page">
    <div class="page-shell">
        <header class="topbar">
            <div>
                <span class="eyebrow">Competencia sana</span>
                <h1>Ranking de jugadores</h1>
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

        <section class="leaderboard-hero">
            <h2>Los mejores puntajes combinan precisión, constancia y dominio de fórmulas.</h2>
            <p>Se ordena por puntos totales y luego por niveles completados.</p>
        </section>

        <section class="leaderboard-table-card">
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
        </section>
    </div>
    <script src="assets/js/app.js"></script>
</body>
</html>
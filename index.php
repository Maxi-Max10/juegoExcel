<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/functions.php';

if (is_logged_in()) {
    redirect('dashboard.php');
}

$flash = get_flash();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e(APP_NAME) ?> | Aprende Excel jugando</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;700&family=Sora:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/styles.css">
</head>
<body class="landing-page">
    <div class="page-shell page-shell--landing">
        <header class="hero">
            <div class="hero__copy">
                <span class="eyebrow">100 niveles progresivos</span>
                <h1>Convierte Excel en un juego diario.</h1>
                <p>Practica fórmulas reales con retos rápidos, progreso desbloqueable, puntaje acumulativo y feedback inmediato en una interfaz tipo app educativa.</p>
                <div class="hero__chips">
                    <span>PHP puro</span>
                    <span>MySQL</span>
                    <span>Responsive</span>
                    <span>Ranking</span>
                </div>
            </div>
            <div class="hero__panel floating-panel">
                <div class="hero__score">
                    <span>Ruta de aprendizaje</span>
                    <strong>Básico a Avanzado</strong>
                </div>
                <div class="hero__progress">
                    <div class="progress-labels">
                        <span>Nivel 1</span>
                        <span>Nivel 100</span>
                    </div>
                    <div class="progress-bar">
                        <div class="progress-bar__fill" style="width: 26%"></div>
                    </div>
                </div>
                <div class="hero__mini-grid">
                    <span class="hero__cell">=SUMA(B2:B6)</span>
                    <span class="hero__cell hero__cell--accent">=SI(B2&gt;=70,"Aprobado","Reforzar")</span>
                    <span class="hero__cell">=BUSCARV(H2,A2:D8,3,FALSO)</span>
                </div>
            </div>
        </header>

        <?php if ($flash): ?>
            <div class="flash flash--<?= e($flash['type']) ?>"><?= e($flash['message']) ?></div>
        <?php endif; ?>

        <main class="auth-layout">
            <section class="auth-card auth-card--info">
                <h2>Qué aprenderás</h2>
                <div class="feature-list">
                    <article>
                        <h3>Niveles 1 a 20</h3>
                        <p>SUMA, RESTA, MULTIPLICACIÓN, DIVISIÓN y referencias de celdas.</p>
                    </article>
                    <article>
                        <h3>Niveles 21 a 40</h3>
                        <p>PROMEDIO, MAX, MIN, CONTAR y trabajo con rangos.</p>
                    </article>
                    <article>
                        <h3>Niveles 41 a 60</h3>
                        <p>SI, SUMAR.SI, PROMEDIO.SI y criterios en contextos prácticos.</p>
                    </article>
                    <article>
                        <h3>Niveles 61 a 100</h3>
                        <p>BUSCARV, BUSCARX, SI.ERROR y desafíos combinados tipo caso real.</p>
                    </article>
                </div>
            </section>

            <section class="auth-card auth-card--form">
                <div class="auth-tabs">
                    <button class="auth-tab is-active" type="button" data-auth-target="login-panel">Entrar</button>
                    <button class="auth-tab" type="button" data-auth-target="register-panel">Crear cuenta</button>
                </div>

                <div id="login-panel" class="auth-panel is-active">
                    <h2>Retoma tu progreso</h2>
                    <form action="login.php" method="post" class="stacked-form">
                        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                        <label>
                            <span>Usuario o correo</span>
                            <input type="text" name="login" required>
                        </label>
                        <label>
                            <span>Contraseña</span>
                            <input type="password" name="password" required>
                        </label>
                        <button class="button button--primary" type="submit">Ingresar</button>
                    </form>
                </div>

                <div id="register-panel" class="auth-panel">
                    <h2>Crea tu perfil</h2>
                    <form action="register.php" method="post" class="stacked-form">
                        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                        <label>
                            <span>Usuario</span>
                            <input type="text" name="username" minlength="3" maxlength="40" required>
                        </label>
                        <label>
                            <span>Correo</span>
                            <input type="email" name="email" required>
                        </label>
                        <label>
                            <span>Contraseña</span>
                            <input type="password" name="password" minlength="6" required>
                        </label>
                        <button class="button button--secondary" type="submit">Empezar ahora</button>
                    </form>
                </div>
            </section>
        </main>
    </div>
    <script src="assets/js/app.js"></script>
</body>
</html>
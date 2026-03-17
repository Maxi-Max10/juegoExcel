<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/layout.php';

if (is_logged_in()) {
    redirect('dashboard.php');
}

$flash = get_flash();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <?php render_head(APP_NAME . ' | Aprende Excel jugando'); ?>
</head>
<body class="landing-page">
    <div class="page-shell page-shell--landing">
        <header class="site-header site-header--landing" data-reveal>
            <a class="brand" href="index.php">
                <span class="brand__mark"><i class="fa-solid fa-table-cells-large"></i></span>
                <span>
                    <strong>Excel Quest</strong>
                    <small>Aprende jugando</small>
                </span>
            </a>
            <nav class="site-nav">
                <a href="#temario">Temario</a>
                <a href="#acceso">Acceso</a>
                <a href="leaderboard.php">Ranking</a>
            </nav>
        </header>

        <header class="hero hero--enhanced">
            <div class="hero__copy" data-reveal>
                <span class="eyebrow">100 niveles progresivos</span>
                <h1>Convierte Excel en un juego diario.</h1>
                <p>Practica fórmulas reales con ritmo de videojuego, tablero tipo Excel, corrección instantánea, ranking y una ruta guiada desde SUMA hasta búsquedas avanzadas y casos reales.</p>
                <div class="hero__actions">
                    <a class="button button--primary" href="#acceso">Empezar ahora</a>
                    <a class="button button--ghost" href="#temario">Ver 100 niveles</a>
                </div>
                <div class="hero__metrics" data-stagger-group>
                    <article class="metric-pill" data-reveal-item>
                        <strong>20</strong>
                        <span>Niveles básicos</span>
                    </article>
                    <article class="metric-pill" data-reveal-item>
                        <strong>40+</strong>
                        <span>Retos con criterios</span>
                    </article>
                    <article class="metric-pill" data-reveal-item>
                        <strong>100%</strong>
                        <span>Enfoque práctico</span>
                    </article>
                </div>
            </div>

            <div class="hero-stage" data-reveal>
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
                        <span class="hero__cell hero__cell--accent">=SI(B2&gt;=70,&quot;Aprobado&quot;,&quot;Reforzar&quot;)</span>
                        <span class="hero__cell">=BUSCARV(H2,A2:D8,3,FALSO)</span>
                    </div>
                    <div class="hero__orbits">
                        <div class="hero-orbit hero-orbit--one"><i class="fa-solid fa-medal"></i> Feedback inmediato</div>
                        <div class="hero-orbit hero-orbit--two"><i class="fa-solid fa-fire"></i> Rachas y puntos</div>
                        <div class="hero-orbit hero-orbit--three"><i class="fa-solid fa-unlock-keyhole"></i> Desbloqueo progresivo</div>
                    </div>
                </div>
            </div>
        </header>

        <section class="feature-ribbon" data-stagger-group>
            <article class="ribbon-card" data-reveal-item>
                <i class="fa-solid fa-gamepad"></i>
                <div>
                    <strong>Jugabilidad clara</strong>
                    <p>Consigna, hoja visual, input y validación sin pasos extra.</p>
                </div>
            </article>
            <article class="ribbon-card" data-reveal-item>
                <i class="fa-solid fa-chart-line"></i>
                <div>
                    <strong>Progreso persistente</strong>
                    <p>Se guarda nivel actual, puntos, vidas y niveles completados.</p>
                </div>
            </article>
            <article class="ribbon-card" data-reveal-item>
                <i class="fa-solid fa-graduation-cap"></i>
                <div>
                    <strong>Ruta estructurada</strong>
                    <p>De referencias básicas a búsquedas y fórmulas anidadas.</p>
                </div>
            </article>
        </section>

        <section id="temario" class="curriculum-showcase">
            <div class="section-heading section-heading--wide" data-reveal>
                <div>
                    <span class="eyebrow">Temario completo</span>
                    <h2>Una curva de dificultad pensada para que el avance se sienta real.</h2>
                </div>
                <p>Los bloques cambian de ritmo y contexto para evitar repetición. No son 100 pantallas iguales: son 100 decisiones distintas.</p>
            </div>
            <div class="curriculum-grid" data-stagger-group>
                <article class="curriculum-card" data-reveal-item>
                    <span class="pill difficulty-basic">Niveles 1-20</span>
                    <h3>Básico</h3>
                    <p>SUMA, RESTA, MULTIPLICACIÓN, DIVISIÓN y referencias tipo A1, B2, C3.</p>
                </article>
                <article class="curriculum-card" data-reveal-item>
                    <span class="pill difficulty-mid-1">Niveles 21-40</span>
                    <h3>Intermedio 1</h3>
                    <p>PROMEDIO, MAX, MIN, CONTAR y lectura correcta de rangos.</p>
                </article>
                <article class="curriculum-card" data-reveal-item>
                    <span class="pill difficulty-mid-2">Niveles 41-60</span>
                    <h3>Intermedio 2</h3>
                    <p>SI, SUMAR.SI, PROMEDIO.SI y criterios aplicados a datos reales.</p>
                </article>
                <article class="curriculum-card" data-reveal-item>
                    <span class="pill difficulty-adv-1">Niveles 61-80</span>
                    <h3>Avanzado 1</h3>
                    <p>BUSCARV, BUSCARX, tablas de referencia y control de errores.</p>
                </article>
                <article class="curriculum-card" data-reveal-item>
                    <span class="pill difficulty-adv-2">Niveles 81-100</span>
                    <h3>Avanzado 2</h3>
                    <p>Fórmulas combinadas, anidación, optimización y escenarios tipo trabajo real.</p>
                </article>
            </div>
        </section>

        <?php if ($flash): ?>
            <div class="flash flash--<?= e($flash['type']) ?>"><?= e($flash['message']) ?></div>
        <?php endif; ?>

        <main id="acceso" class="auth-layout auth-layout--enhanced">
            <section class="auth-card auth-card--info" data-reveal>
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

            <section class="auth-card auth-card--form" data-reveal>
                <div class="auth-card__intro">
                    <span class="eyebrow">Acceso rápido</span>
                    <h2>Entra y retoma tu progreso</h2>
                    <p>El diseño está preparado para escritorio y móvil, así que puedes practicar desde cualquier pantalla.</p>
                </div>
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
    <?php render_app_scripts(); ?>
</body>
</html>

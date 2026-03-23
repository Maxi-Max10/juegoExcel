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
            <nav class="site-nav" id="main-nav">
                <a href="#temario">Temario</a>
                <a href="#acceso">Acceso</a>
                <a href="leaderboard.php">Ranking</a>
            </nav>
            <button class="nav-toggle" type="button" aria-label="Menú" aria-expanded="false" data-nav-toggle>
                <span class="nav-toggle__bar"></span>
                <span class="nav-toggle__bar"></span>
                <span class="nav-toggle__bar"></span>
            </button>
        </header>

        <header class="hero hero--enhanced">
            <div class="hero-particles" aria-hidden="true"></div>
            <div class="hero-orbit hero-orbit--1"></div>
            <div class="hero-orbit hero-orbit--2"></div>
            <div class="hero-orbit hero-orbit--3"></div>            <div class="hero__copy" data-reveal>
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
                <div class="hero__panel floating-panel" id="acceso">
                    <div class="auth-tabs">
                        <button class="auth-tab is-active" type="button" data-auth-target="login-panel">Entrar</button>
                        <button class="auth-tab" type="button" data-auth-target="register-panel">Crear cuenta</button>
                    </div>

                    <div id="login-panel" class="auth-panel is-active">
                        <h2>Retoma tu progreso</h2>
                        <div id="login-msg" class="auth-msg" hidden></div>
                        <form id="login-form" action="login.php" method="post" class="stacked-form">
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
                        <div id="register-msg" class="auth-msg" hidden></div>
                        <form id="register-form" action="register.php" method="post" class="stacked-form">
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
                            <label>
                                <span>Código especial <small>(opcional)</small></span>
                                <input type="text" name="invite_code" maxlength="20" autocomplete="off" placeholder="¿Tienes un código?">
                            </label>
                            <button class="button button--secondary" type="submit">Empezar ahora</button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="scroll-indicator" aria-hidden="true">
                <span>Scroll</span>
                <i class="fa-solid fa-chevron-down"></i>
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
                    <span class="eyebrow">Ruta de 100 niveles</span>
                    <h2>Un recorrido corto de entender y largo de dominar.</h2>
                </div>
                <p>Tres etapas claras para avanzar desde lo esencial hasta fórmulas de uso real.</p>
            </div>
            <div class="curriculum-grid" data-stagger-group>
                <article class="curriculum-card" data-reveal-item>
                    <span class="pill difficulty-basic">Niveles 1-30</span>
                    <h3>Base</h3>
                    <p>Operaciones, referencias y fórmulas esenciales para empezar bien.</p>
                </article>
                <article class="curriculum-card" data-reveal-item>
                    <span class="pill difficulty-mid-1">Niveles 31-70</span>
                    <h3>Progreso</h3>
                    <p>Funciones, criterios y análisis para resolver ejercicios más útiles.</p>
                </article>
                <article class="curriculum-card" data-reveal-item>
                    <span class="pill difficulty-adv-2">Niveles 71-100</span>
                    <h3>Dominio</h3>
                    <p>Búsquedas, combinaciones y retos pensados como casos reales de trabajo.</p>
                </article>
            </div>
        </section>

        <?php if ($flash): ?>
            <div class="flash flash--<?= e($flash['type']) ?>"><?= e($flash['message']) ?></div>
        <?php endif; ?>
    </div>
    <?php render_app_scripts(); ?>
    <script>
    (function(){
        function setupForm(formId, msgId, redirectTo) {
            var form = document.getElementById(formId);
            var msgBox = document.getElementById(msgId);
            if (!form || !msgBox) return;

            form.addEventListener('submit', function(e) {
                e.preventDefault();
                msgBox.hidden = true;
                var btn = form.querySelector('button[type="submit"]');
                var origText = btn.textContent;
                btn.disabled = true;
                btn.textContent = 'Procesando...';

                fetch(form.action, {
                    method: 'POST',
                    body: new FormData(form),
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                })
                .then(function(res) { return res.json().then(function(d) { d._ok = res.ok; return d; }); })
                .then(function(data) {
                    msgBox.textContent = data.message;
                    msgBox.className = 'auth-msg auth-msg--' + data.type;
                    msgBox.hidden = false;
                    if (data._ok && data.type === 'success') {
                        setTimeout(function() { window.location.href = redirectTo; }, 600);
                    } else {
                        btn.disabled = false;
                        btn.textContent = origText;
                    }
                })
                .catch(function() {
                    msgBox.textContent = 'Error de conexi\u00f3n. Int\u00e9ntalo de nuevo.';
                    msgBox.className = 'auth-msg auth-msg--error';
                    msgBox.hidden = false;
                    btn.disabled = false;
                    btn.textContent = origText;
                });
            });
        }

        setupForm('login-form', 'login-msg', 'dashboard.php');
        setupForm('register-form', 'register-msg', 'dashboard.php');
    })();
    </script>
</body>
</html>

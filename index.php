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
    <?php render_head(APP_NAME . ' | Aprende Excel jugando', 'Aprende fórmulas de Excel de forma gratuita con Excel Snake: un juego de serpiente con 200 niveles progresivos. Practica SUMA, BUSCARV, SI, SUMAR.SI y más. Empieza gratis ahora.'); ?>
</head>
<body class="landing-page">
    <!-- ═══ Background ambient ═══ -->
    <div class="landing-ambient" aria-hidden="true">
        <div class="landing-ambient__orb landing-ambient__orb--1"></div>
        <div class="landing-ambient__orb landing-ambient__orb--2"></div>
        <div class="landing-ambient__orb landing-ambient__orb--3"></div>
    </div>

    <div class="page-shell page-shell--landing">
        <!-- ═══ HEADER ═══ -->
        <header class="site-header site-header--landing" data-reveal>
            <a class="brand" href="index.php">
                <span class="brand__mark"><img src="assets/img/logo.png" alt="Excel Snake" width="46" height="46"></span>
                <span>
                    <strong>Excel Snake</strong>
                    <small>Aprende jugando</small>
                </span>
            </a>
            <nav class="site-nav" id="main-nav">
                <a href="#features">Ventajas</a>
                <a href="#temario">Temario</a>
                <a href="guia-excel.php">Guía Excel</a>
                <a href="#acceso">Acceso</a>
                <a href="leaderboard.php">Ranking</a>
            </nav>
            <button class="nav-toggle" type="button" aria-label="Menú" aria-expanded="false" data-nav-toggle>
                <span class="nav-toggle__bar"></span>
                <span class="nav-toggle__bar"></span>
                <span class="nav-toggle__bar"></span>
            </button>
        </header>

        <!-- ═══ HERO ═══ -->
        <header class="hero hero--enhanced">
            <div class="hero-particles" aria-hidden="true"></div>
            <div class="hero-orbit hero-orbit--1"></div>
            <div class="hero-orbit hero-orbit--2"></div>
            <div class="hero-orbit hero-orbit--3"></div>
            <div class="hero__copy" data-reveal>
                <span class="eyebrow"><i class="fa-solid fa-snake"></i> 200 niveles progresivos</span>
                <h1>Domina Excel jugando</h1>
                <p>Una serpiente, fórmulas reales, corrección instantánea. Avanza desde SUMA hasta BUSCARV y más allá con ritmo de videojuego.</p>
                <div class="hero__actions">
                    <a class="button button--primary button--glow" href="#acceso"><i class="fa-solid fa-play"></i> Empezar gratis</a>
                    <a class="button button--ghost" href="#temario"><i class="fa-solid fa-route"></i> Ver ruta</a>
                </div>
                <div class="hero__metrics" data-stagger-group>
                    <div class="metrics-bg" aria-hidden="true">
                        <span class="metrics-blob metrics-blob--1"></span>
                        <span class="metrics-blob metrics-blob--2"></span>
                        <span class="metrics-blob metrics-blob--3"></span>
                    </div>
                    <article class="metric-pill" data-reveal-item>
                        <strong>200</strong>
                        <span>Niveles</span>
                    </article>
                    <article class="metric-pill" data-reveal-item>
                        <strong>10</strong>
                        <span>Categorías</span>
                    </article>
                    <article class="metric-pill" data-reveal-item>
                        <strong>100</strong>
                        <span>% Gratis</span>
                    </article>
                </div>
            </div>

            <div class="hero-stage" data-reveal>
                <div class="hero__panel floating-panel" id="acceso">
                    <div class="panel-accent" aria-hidden="true"></div>
                    <div class="auth-tabs">
                        <button class="auth-tab is-active" type="button" data-auth-target="login-panel">Entrar</button>
                        <button class="auth-tab" type="button" data-auth-target="register-panel">Crear cuenta</button>
                    </div>

                    <div id="login-panel" class="auth-panel is-active">
                        <h2><i class="fa-solid fa-right-to-bracket"></i> Retoma tu progreso</h2>
                        <div id="login-msg" class="auth-msg" hidden></div>

                        <div class="oauth-buttons">
                            <a href="oauth_start.php?provider=google" class="button button--oauth button--google">
                                <svg width="18" height="18" viewBox="0 0 48 48"><path fill="#EA4335" d="M24 9.5c3.54 0 6.71 1.22 9.21 3.6l6.85-6.85C35.9 2.38 30.47 0 24 0 14.62 0 6.51 5.38 2.56 13.22l7.98 6.19C12.43 13.72 17.74 9.5 24 9.5z"/><path fill="#4285F4" d="M46.98 24.55c0-1.57-.15-3.09-.38-4.55H24v9.02h12.94c-.58 2.96-2.26 5.48-4.78 7.18l7.73 6c4.51-4.18 7.09-10.36 7.09-17.65z"/><path fill="#FBBC05" d="M10.53 28.59a14.5 14.5 0 0 1 0-9.18l-7.98-6.19a24.0 24.0 0 0 0 0 21.56l7.98-6.19z"/><path fill="#34A853" d="M24 48c6.48 0 11.93-2.13 15.89-5.81l-7.73-6c-2.15 1.45-4.92 2.3-8.16 2.3-6.26 0-11.57-4.22-13.47-9.91l-7.98 6.19C6.51 42.62 14.62 48 24 48z"/></svg>
                                Continuar con Google
                            </a>
                        </div>

                        <div class="auth-divider"><span>o con tu cuenta</span></div>

                        <form id="login-form" action="login.php" method="post" class="stacked-form">
                            <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                            <label>
                                <span><i class="fa-solid fa-user"></i> Usuario o correo</span>
                                <input type="text" name="login" placeholder="tu@correo.com" required>
                            </label>
                            <label>
                                <span><i class="fa-solid fa-lock"></i> Contraseña</span>
                                <input type="password" name="password" placeholder="••••••" required>
                            </label>
                            <button class="button button--primary" type="submit"><i class="fa-solid fa-arrow-right-to-bracket"></i> Ingresar</button>
                        </form>
                    </div>

                    <div id="register-panel" class="auth-panel">
                        <h2><i class="fa-solid fa-user-plus"></i> Crea tu perfil</h2>
                        <div id="register-msg" class="auth-msg" hidden></div>

                        <div class="oauth-buttons">
                            <a href="oauth_start.php?provider=google" class="button button--oauth button--google">
                                <svg width="18" height="18" viewBox="0 0 48 48"><path fill="#EA4335" d="M24 9.5c3.54 0 6.71 1.22 9.21 3.6l6.85-6.85C35.9 2.38 30.47 0 24 0 14.62 0 6.51 5.38 2.56 13.22l7.98 6.19C12.43 13.72 17.74 9.5 24 9.5z"/><path fill="#4285F4" d="M46.98 24.55c0-1.57-.15-3.09-.38-4.55H24v9.02h12.94c-.58 2.96-2.26 5.48-4.78 7.18l7.73 6c4.51-4.18 7.09-10.36 7.09-17.65z"/><path fill="#FBBC05" d="M10.53 28.59a14.5 14.5 0 0 1 0-9.18l-7.98-6.19a24.0 24.0 0 0 0 0 21.56l7.98-6.19z"/><path fill="#34A853" d="M24 48c6.48 0 11.93-2.13 15.89-5.81l-7.73-6c-2.15 1.45-4.92 2.3-8.16 2.3-6.26 0-11.57-4.22-13.47-9.91l-7.98 6.19C6.51 42.62 14.62 48 24 48z"/></svg>
                                Registrarse con Google
                            </a>
                        </div>

                        <div class="auth-divider"><span>o con correo</span></div>

                        <form id="register-form" action="register.php" method="post" class="stacked-form">
                            <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                            <label>
                                <span><i class="fa-solid fa-at"></i> Usuario</span>
                                <input type="text" name="username" placeholder="tu_nombre" minlength="3" maxlength="40" required>
                            </label>
                            <label>
                                <span><i class="fa-solid fa-envelope"></i> Correo</span>
                                <input type="email" name="email" placeholder="tu@correo.com" required>
                            </label>
                            <label>
                                <span><i class="fa-solid fa-lock"></i> Contraseña</span>
                                <input type="password" name="password" placeholder="Mínimo 6 caracteres" minlength="6" required>
                            </label>
                            <button class="button button--secondary" type="submit"><i class="fa-solid fa-rocket"></i> Empezar ahora</button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="scroll-indicator" aria-hidden="true">
                <span>Scroll</span>
                <i class="fa-solid fa-chevron-down"></i>
            </div>
        </header>

        <!-- ═══ SOCIAL PROOF TICKER ═══ -->
        <div class="social-proof-bar" data-reveal>
            <div class="social-proof-bar__inner">
                <span><i class="fa-solid fa-users"></i> Jugadores activos aprendiendo Excel</span>
                <span><i class="fa-solid fa-fire"></i> Niveles completados cada día</span>
                <span><i class="fa-solid fa-star"></i> Fórmulas reales de trabajo</span>
                <span><i class="fa-solid fa-shield-check"></i> 100% gratuito, sin límites</span>
                <span><i class="fa-solid fa-users"></i> Jugadores activos aprendiendo Excel</span>
                <span><i class="fa-solid fa-fire"></i> Niveles completados cada día</span>
                <span><i class="fa-solid fa-star"></i> Fórmulas reales de trabajo</span>
                <span><i class="fa-solid fa-shield-check"></i> 100% gratuito, sin límites</span>
            </div>
        </div>

        <!-- ═══ FEATURES ═══ -->
        <section id="features" class="features-section">
            <div class="section-heading section-heading--center" data-reveal>
                <span class="eyebrow">Por qué Excel Snake</span>
                <h2>Todo lo que necesitas para dominar fórmulas.</h2>
            </div>
            <div class="features-grid" data-stagger-group>
                <article class="feature-card feature-card--accent-green" data-reveal-item>
                    <div class="feature-card__icon">
                        <i class="fa-solid fa-gamepad"></i>
                    </div>
                    <h3>Aprende jugando</h3>
                    <p>Mueve la serpiente hasta la respuesta correcta. Sin aburrimiento, pura práctica gamificada.</p>
                </article>
                <article class="feature-card feature-card--accent-blue" data-reveal-item>
                    <div class="feature-card__icon">
                        <i class="fa-solid fa-bolt"></i>
                    </div>
                    <h3>Corrección instantánea</h3>
                    <p>Sabrás al instante si tu fórmula es correcta. Sin esperas, feedback inmediato.</p>
                </article>
                <article class="feature-card feature-card--accent-yellow" data-reveal-item>
                    <div class="feature-card__icon">
                        <i class="fa-solid fa-route"></i>
                    </div>
                    <h3>Ruta guiada</h3>
                    <p>200 niveles organizados de lo básico a lo avanzado. Siempre sabrás qué toca aprender.</p>
                </article>
                <article class="feature-card feature-card--accent-purple" data-reveal-item>
                    <div class="feature-card__icon">
                        <i class="fa-solid fa-ranking-star"></i>
                    </div>
                    <h3>Ranking global</h3>
                    <p>Compite con otros jugadores. Sube de posición a medida que dominas más fórmulas.</p>
                </article>
                <article class="feature-card feature-card--accent-orange" data-reveal-item>
                    <div class="feature-card__icon">
                        <i class="fa-solid fa-brain"></i>
                    </div>
                    <h3>Asistente IA</h3>
                    <p>¿Atascado en un nivel? Pregunta al asistente y recibe explicaciones claras al instante.</p>
                </article>
                <article class="feature-card feature-card--accent-pink" data-reveal-item>
                    <div class="feature-card__icon">
                        <i class="fa-solid fa-heart"></i>
                    </div>
                    <h3>Sistema de vidas</h3>
                    <p>5 vidas que se regeneran. Piensa bien antes de responder y optimiza tu racha.</p>
                </article>
            </div>
        </section>

        <!-- ═══ HOW IT WORKS ═══ -->
        <section class="how-it-works-section">
            <div class="section-heading section-heading--center" data-reveal>
                <span class="eyebrow">Cómo funciona</span>
                <h2>3 pasos para dominar Excel.</h2>
            </div>
            <div class="steps-grid" data-stagger-group>
                <article class="step-card" data-reveal-item>
                    <div class="step-card__number">1</div>
                    <h3>Lee la consigna</h3>
                    <p>Cada nivel te muestra una hoja de cálculo y te pide resolver una fórmula específica.</p>
                </article>
                <div class="step-connector" data-reveal-item aria-hidden="true">
                    <i class="fa-solid fa-arrow-right"></i>
                </div>
                <article class="step-card" data-reveal-item>
                    <div class="step-card__number">2</div>
                    <h3>Mueve</h3>
                    <p>Dirige la serpiente hacia la respuesta correcta entre las opciones del tablero.</p>
                </article>
                <div class="step-connector" data-reveal-item aria-hidden="true">
                    <i class="fa-solid fa-arrow-right"></i>
                </div>
                <article class="step-card" data-reveal-item>
                    <div class="step-card__number">3</div>
                    <h3>Sube de nivel</h3>
                    <p>Gana puntos, mantén tu racha y desbloquea niveles cada vez más desafiantes.</p>
                </article>
            </div>
        </section>

        <!-- ═══ CURRICULUM ═══ -->
        <section id="temario" class="curriculum-showcase">
            <div class="section-heading section-heading--wide" data-reveal>
                <div>
                    <span class="eyebrow">Ruta de 200 niveles</span>
                    <h2>De cero a experto en Excel.</h2>
                </div>
                <p>Cinco etapas progresivas que cubren todo lo que necesitas dominar.</p>
            </div>
            <div class="curriculum-grid" data-stagger-group>
                <article class="curriculum-card" data-reveal-item>
                    <span class="pill difficulty-basic">Niveles 1-20</span>
                    <h3>Fundamentos</h3>
                    <p>Suma, resta, multiplicación, división y referencias de celdas.</p>
                </article>
                <article class="curriculum-card" data-reveal-item>
                    <span class="pill difficulty-mid-1">Niveles 21-60</span>
                    <h3>Funciones clave</h3>
                    <p>PROMEDIO, CONTAR, MAX, MIN, SI y funciones condicionales.</p>
                </article>
                <article class="curriculum-card" data-reveal-item>
                    <span class="pill difficulty-mid-2">Niveles 61-100</span>
                    <h3>Criterios</h3>
                    <p>SUMAR.SI, CONTAR.SI, PROMEDIO.SI y sus variantes con múltiples criterios.</p>
                </article>
                <article class="curriculum-card" data-reveal-item>
                    <span class="pill difficulty-adv-1">Niveles 101-140</span>
                    <h3>Búsquedas</h3>
                    <p>BUSCARV, BUSCARX, INDICE+COINCIDIR, SI.ERROR y texto avanzado.</p>
                </article>
                <article class="curriculum-card" data-reveal-item>
                    <span class="pill difficulty-exp-1">Niveles 141-200</span>
                    <h3>Maestría</h3>
                    <p>Fórmulas anidadas, combinaciones avanzadas y casos reales de oficina.</p>
                </article>
            </div>
        </section>

        <!-- ═══ CTA FINAL ═══ -->
        <section class="cta-section" data-reveal>
            <div class="cta-card">
                <div class="cta-card__glow" aria-hidden="true"></div>
                <h2>¿Listo para dominar Excel?</h2>
                <p>Empieza ahora, es gratis. Sin tarjeta, sin límites, solo tú y las fórmulas.</p>
                <a class="button button--primary button--lg button--glow" href="#acceso"><i class="fa-solid fa-play"></i> Empezar a jugar</a>
            </div>
        </section>

        <?php if ($flash): ?>
            <div class="flash flash--<?= e($flash['type']) ?>"><?= e($flash['message']) ?></div>
        <?php endif; ?>

        <footer class="landing-footer">
            <div class="landing-footer__brand">
                <img src="assets/img/logo.png" alt="Excel Snake" width="32" height="32">
                <span>Excel Snake</span>
            </div>
            <div class="landing-footer__links">
                <a href="privacy.php">Privacidad</a>
                <a href="cookies.php">Cookies</a>
                <a href="guia-excel.php">Guía Excel</a>
                <a href="leaderboard.php">Ranking</a>
            </div>
            <p class="landing-footer__copy">&copy; <?= date('Y') ?> Excel Snake. Aprende jugando.</p>
        </footer>
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
                var origText = btn.innerHTML;
                btn.disabled = true;
                btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Procesando...';

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
                        btn.innerHTML = origText;
                    }
                })
                .catch(function() {
                    msgBox.textContent = 'Error de conexi\u00f3n. Int\u00e9ntalo de nuevo.';
                    msgBox.className = 'auth-msg auth-msg--error';
                    msgBox.hidden = false;
                    btn.disabled = false;
                    btn.innerHTML = origText;
                });
            });
        }

        setupForm('login-form', 'login-msg', 'dashboard.php');
        setupForm('register-form', 'register-msg', 'dashboard.php');
    })();
    </script>
</body>
</html>

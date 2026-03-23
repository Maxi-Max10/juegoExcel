<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/layout.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <?php render_head(APP_NAME . ' | Política de Cookies'); ?>
</head>
<body class="legal-page">
    <div class="page-shell page-shell--legal">
        <header class="site-header site-header--landing" data-reveal>
            <a class="brand" href="index.php">
                <span class="brand__mark"><img src="assets/img/logo.png" alt="Excel Snake" width="46" height="46"></span>
                <span>
                    <strong>Excel Snake</strong>
                    <small>Aprende jugando</small>
                </span>
            </a>
            <nav class="site-nav" id="main-nav">
                <a href="index.php">Inicio</a>
                <a href="leaderboard.php">Ranking</a>
            </nav>
        </header>

        <main class="legal-content" data-reveal>
            <h1>Política de Cookies</h1>
            <p class="legal-updated">Última actualización: 23 de marzo de 2026</p>

            <section>
                <h2>1. ¿Qué son las cookies?</h2>
                <p>Las cookies son pequeños archivos de texto que se almacenan en tu dispositivo cuando visitas un sitio web. Permiten que el sitio recuerde información sobre tu visita, como tus preferencias y el estado de tu sesión.</p>
            </section>

            <section>
                <h2>2. ¿Qué cookies utilizamos?</h2>
                <p>En <strong><?= e(APP_NAME) ?></strong> utilizamos los siguientes tipos de cookies:</p>

                <h3>2.1 Cookies estrictamente necesarias</h3>
                <p>Son imprescindibles para el funcionamiento de la plataforma. Sin ellas no podrías iniciar sesión ni usar el servicio.</p>
                <table class="legal-table">
                    <thead>
                        <tr>
                            <th>Cookie</th>
                            <th>Finalidad</th>
                            <th>Duración</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><code>PHPSESSID</code></td>
                            <td>Identificador de sesión de PHP. Mantiene tu sesión activa mientras navegas por la plataforma.</td>
                            <td>Hasta cerrar el navegador o cerrar sesión</td>
                        </tr>
                    </tbody>
                </table>

                <h3>2.2 Cookies funcionales</h3>
                <p>Mejoran la experiencia de uso recordando preferencias y configuraciones.</p>
                <table class="legal-table">
                    <thead>
                        <tr>
                            <th>Cookie</th>
                            <th>Finalidad</th>
                            <th>Duración</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><code>cookie_consent</code></td>
                            <td>Registra si has aceptado el uso de cookies para no mostrarte el aviso de nuevo.</td>
                            <td>1 año</td>
                        </tr>
                    </tbody>
                </table>
            </section>

            <section>
                <h2>3. Cookies de terceros</h2>
                <p>Nuestra plataforma carga recursos externos (fuentes de Google Fonts, CDN de Bootstrap y Font Awesome). Estos servicios pueden establecer sus propias cookies según sus respectivas políticas de privacidad. <?= e(APP_NAME) ?> no tiene control sobre estas cookies de terceros.</p>
            </section>

            <section>
                <h2>4. ¿Cómo gestionar las cookies?</h2>
                <p>Puedes configurar tu navegador para bloquear o eliminar cookies. A continuación, los enlaces de ayuda de los navegadores más comunes:</p>
                <ul>
                    <li><a href="https://support.google.com/chrome/answer/95647" target="_blank" rel="noopener noreferrer">Google Chrome</a></li>
                    <li><a href="https://support.mozilla.org/es/kb/habilitar-y-deshabilitar-cookies-sitios-web-rastrear-preferencias" target="_blank" rel="noopener noreferrer">Mozilla Firefox</a></li>
                    <li><a href="https://support.microsoft.com/es-es/windows/eliminar-y-administrar-cookies-168dab11-0753-043d-7c16-ede5947fc64d" target="_blank" rel="noopener noreferrer">Microsoft Edge</a></li>
                    <li><a href="https://support.apple.com/es-es/guide/safari/sfri11471/mac" target="_blank" rel="noopener noreferrer">Safari</a></li>
                </ul>
                <p><strong>Nota:</strong> si desactivas las cookies de sesión, no podrás iniciar sesión ni utilizar la plataforma.</p>
            </section>

            <section>
                <h2>5. Consentimiento</h2>
                <p>Al continuar navegando por nuestra plataforma, aceptas el uso de las cookies descritas en esta política. Puedes retirar tu consentimiento en cualquier momento eliminando las cookies de tu navegador.</p>
            </section>

            <section>
                <h2>6. Modificaciones</h2>
                <p>Nos reservamos el derecho de modificar esta política de cookies. Cualquier cambio será publicado en esta página con la nueva fecha de actualización.</p>
            </section>

            <section>
                <h2>7. Más información</h2>
                <p>Para más información sobre cómo tratamos tus datos, consulta nuestra <a href="privacy.php">Política de Privacidad</a>.</p>
            </section>
        </main>

        <footer class="legal-footer">
            <a href="privacy.php">Política de Privacidad</a>
            <a href="cookies.php">Política de Cookies</a>
            <a href="index.php">Volver al inicio</a>
        </footer>
    </div>
    <?php render_app_scripts(); ?>
</body>
</html>

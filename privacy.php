<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/layout.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <?php render_head(APP_NAME . ' | Política de Privacidad'); ?>
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
            <h1>Política de Privacidad</h1>
            <p class="legal-updated">Última actualización: 23 de marzo de 2026</p>

            <section>
                <h2>1. Responsable del tratamiento</h2>
                <p><strong><?= e(APP_NAME) ?></strong> es una plataforma educativa de aprendizaje de Excel mediante gamificación. El responsable del tratamiento de tus datos es el equipo titular de la plataforma.</p>
            </section>

            <section>
                <h2>2. Datos que recopilamos</h2>
                <p>Recogemos únicamente los datos necesarios para el funcionamiento del servicio:</p>
                <ul>
                    <li><strong>Datos de registro:</strong> nombre de usuario, dirección de correo electrónico y contraseña (almacenada de forma cifrada).</li>
                    <li><strong>Datos de progreso:</strong> nivel actual, puntuación, vidas, niveles completados y posición en el ranking.</li>
                    <li><strong>Datos técnicos:</strong> dirección IP, tipo de navegador y datos de sesión necesarios para mantener la conexión activa.</li>
                </ul>
            </section>

            <section>
                <h2>3. Finalidad del tratamiento</h2>
                <p>Utilizamos tus datos para:</p>
                <ul>
                    <li>Gestionar tu cuenta de usuario y autenticación.</li>
                    <li>Guardar y mostrar tu progreso en los niveles del juego.</li>
                    <li>Generar el ranking público de jugadores.</li>
                    <li>Mejorar la experiencia de usuario y el funcionamiento de la plataforma.</li>
                </ul>
            </section>

            <section>
                <h2>4. Base legal</h2>
                <p>El tratamiento de tus datos se basa en:</p>
                <ul>
                    <li><strong>Consentimiento:</strong> al registrarte aceptas esta política.</li>
                    <li><strong>Ejecución del servicio:</strong> los datos son necesarios para proporcionarte acceso a la plataforma.</li>
                    <li><strong>Interés legítimo:</strong> para la seguridad y mejora del servicio.</li>
                </ul>
            </section>

            <section>
                <h2>5. Conservación de datos</h2>
                <p>Tus datos se conservan mientras mantengas tu cuenta activa. Si solicitas la eliminación de tu cuenta, procederemos a borrar tus datos personales en un plazo máximo de 30 días.</p>
            </section>

            <section>
                <h2>6. Compartición de datos</h2>
                <p>No vendemos ni compartimos tus datos personales con terceros, salvo:</p>
                <ul>
                    <li>Proveedores de infraestructura (hosting) necesarios para el funcionamiento del servicio.</li>
                    <li>Servicios de inteligencia artificial para la funcionalidad de chat de estudio, que reciben únicamente el contenido del mensaje sin datos de identificación personal.</li>
                    <li>Requerimientos legales cuando así lo exija la legislación aplicable.</li>
                </ul>
            </section>

            <section>
                <h2>7. Seguridad</h2>
                <p>Aplicamos medidas técnicas y organizativas para proteger tus datos, incluyendo:</p>
                <ul>
                    <li>Cifrado de contraseñas mediante algoritmos seguros.</li>
                    <li>Protección contra CSRF en todos los formularios.</li>
                    <li>Sesiones seguras con regeneración de identificadores.</li>
                </ul>
            </section>

            <section>
                <h2>8. Tus derechos</h2>
                <p>Tienes derecho a:</p>
                <ul>
                    <li><strong>Acceso:</strong> solicitar una copia de tus datos personales.</li>
                    <li><strong>Rectificación:</strong> corregir datos inexactos o incompletos.</li>
                    <li><strong>Supresión:</strong> solicitar la eliminación de tu cuenta y datos.</li>
                    <li><strong>Portabilidad:</strong> recibir tus datos en un formato estructurado.</li>
                    <li><strong>Oposición:</strong> oponerte al tratamiento de tus datos.</li>
                </ul>
                <p>Para ejercer cualquiera de estos derechos, contacta con nosotros a través de los canales disponibles en la plataforma.</p>
            </section>

            <section>
                <h2>9. Cookies</h2>
                <p>Esta plataforma utiliza cookies. Para más información, consulta nuestra <a href="cookies.php">Política de Cookies</a>.</p>
            </section>

            <section>
                <h2>10. Modificaciones</h2>
                <p>Nos reservamos el derecho de actualizar esta política. Cualquier cambio será publicado en esta página con la fecha de actualización correspondiente.</p>
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

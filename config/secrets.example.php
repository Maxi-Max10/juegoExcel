<?php
declare(strict_types=1);

define('LOCAL_AI_PROVIDER', 'openrouter');
define('LOCAL_AI_API_KEY', 'pega_aqui_tu_api_key_de_openrouter');
define('LOCAL_AI_MODEL', 'meta-llama/llama-3.1-8b-instruct:free');
define('LOCAL_AI_API_URL', 'https://openrouter.ai/api/v1/chat/completions');

define('LOCAL_OPENAI_API_KEY', '');
define('LOCAL_OPENAI_MODEL', 'gpt-4o-mini');

// ── OAuth: Google ──
define('LOCAL_GOOGLE_CLIENT_ID', '');
define('LOCAL_GOOGLE_CLIENT_SECRET', '');
define('LOCAL_GOOGLE_REDIRECT_URI', 'http://localhost/oauth_callback.php?provider=google');

// ── OAuth: Apple (Sign in with Apple) ──
define('LOCAL_APPLE_CLIENT_ID', '');       // Services ID
define('LOCAL_APPLE_TEAM_ID', '');
define('LOCAL_APPLE_KEY_ID', '');
define('LOCAL_APPLE_PRIVATE_KEY', '');      // Contenido del .p8
define('LOCAL_APPLE_REDIRECT_URI', 'http://localhost/oauth_callback.php?provider=apple');

// ── App URL & correo ──
define('LOCAL_APP_URL', 'http://localhost');
define('LOCAL_MAIL_FROM', 'noreply@excelsnake.com');
<?php
declare(strict_types=1);

$localSecrets = __DIR__ . '/secrets.php';
if (is_file($localSecrets)) {
    require_once $localSecrets;
}

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

date_default_timezone_set('America/Mexico_City');

define('APP_NAME', 'Excel Snake');
define('TOTAL_LEVELS', 100);

define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
define('DB_PORT', getenv('DB_PORT') ?: '3306');
define('DB_NAME', getenv('DB_NAME') ?: 'u404968876_gameExcel');
define('DB_USER', getenv('DB_USER') ?: 'u404968876_gameExcel');
define('DB_PASS', getenv('DB_PASS') ?: 'gameExcel12');

define('OPENAI_API_KEY', $_SERVER['OPENAI_API_KEY'] ?? getenv('OPENAI_API_KEY') ?: (defined('LOCAL_OPENAI_API_KEY') ? LOCAL_OPENAI_API_KEY : ''));
define('OPENAI_MODEL', $_SERVER['OPENAI_MODEL'] ?? getenv('OPENAI_MODEL') ?: (defined('LOCAL_OPENAI_MODEL') ? LOCAL_OPENAI_MODEL : 'gpt-4o-mini'));

define('AI_PROVIDER', $_SERVER['AI_PROVIDER'] ?? getenv('AI_PROVIDER') ?: (defined('LOCAL_AI_PROVIDER') ? LOCAL_AI_PROVIDER : 'openrouter'));
define('AI_API_KEY', $_SERVER['AI_API_KEY'] ?? getenv('AI_API_KEY') ?: (defined('LOCAL_AI_API_KEY') ? LOCAL_AI_API_KEY : OPENAI_API_KEY));
define('AI_MODEL', $_SERVER['AI_MODEL'] ?? getenv('AI_MODEL') ?: (defined('LOCAL_AI_MODEL') ? LOCAL_AI_MODEL : (OPENAI_MODEL !== '' ? OPENAI_MODEL : 'meta-llama/llama-3.1-8b-instruct:free')));
define('AI_API_URL', $_SERVER['AI_API_URL'] ?? getenv('AI_API_URL') ?: (defined('LOCAL_AI_API_URL') ? LOCAL_AI_API_URL : 'https://openrouter.ai/api/v1/chat/completions'));

// ── OAuth ──
define('GOOGLE_CLIENT_ID', $_SERVER['GOOGLE_CLIENT_ID'] ?? getenv('GOOGLE_CLIENT_ID') ?: (defined('LOCAL_GOOGLE_CLIENT_ID') ? LOCAL_GOOGLE_CLIENT_ID : ''));
define('GOOGLE_CLIENT_SECRET', $_SERVER['GOOGLE_CLIENT_SECRET'] ?? getenv('GOOGLE_CLIENT_SECRET') ?: (defined('LOCAL_GOOGLE_CLIENT_SECRET') ? LOCAL_GOOGLE_CLIENT_SECRET : ''));
define('GOOGLE_REDIRECT_URI', $_SERVER['GOOGLE_REDIRECT_URI'] ?? getenv('GOOGLE_REDIRECT_URI') ?: (defined('LOCAL_GOOGLE_REDIRECT_URI') ? LOCAL_GOOGLE_REDIRECT_URI : ''));

define('APPLE_CLIENT_ID', $_SERVER['APPLE_CLIENT_ID'] ?? getenv('APPLE_CLIENT_ID') ?: (defined('LOCAL_APPLE_CLIENT_ID') ? LOCAL_APPLE_CLIENT_ID : ''));
define('APPLE_TEAM_ID', $_SERVER['APPLE_TEAM_ID'] ?? getenv('APPLE_TEAM_ID') ?: (defined('LOCAL_APPLE_TEAM_ID') ? LOCAL_APPLE_TEAM_ID : ''));
define('APPLE_KEY_ID', $_SERVER['APPLE_KEY_ID'] ?? getenv('APPLE_KEY_ID') ?: (defined('LOCAL_APPLE_KEY_ID') ? LOCAL_APPLE_KEY_ID : ''));
define('APPLE_PRIVATE_KEY', $_SERVER['APPLE_PRIVATE_KEY'] ?? getenv('APPLE_PRIVATE_KEY') ?: (defined('LOCAL_APPLE_PRIVATE_KEY') ? LOCAL_APPLE_PRIVATE_KEY : ''));
define('APPLE_REDIRECT_URI', $_SERVER['APPLE_REDIRECT_URI'] ?? getenv('APPLE_REDIRECT_URI') ?: (defined('LOCAL_APPLE_REDIRECT_URI') ? LOCAL_APPLE_REDIRECT_URI : ''));

// ── Email (SMTP via mail()) ──
define('APP_URL', $_SERVER['APP_URL'] ?? getenv('APP_URL') ?: (defined('LOCAL_APP_URL') ? LOCAL_APP_URL : 'http://localhost'));
define('MAIL_FROM', $_SERVER['MAIL_FROM'] ?? getenv('MAIL_FROM') ?: (defined('LOCAL_MAIL_FROM') ? LOCAL_MAIL_FROM : 'noreply@excelsnake.com'));
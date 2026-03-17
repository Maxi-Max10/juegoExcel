<?php
declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

date_default_timezone_set('America/Mexico_City');

define('APP_NAME', 'Excel Quest');
define('TOTAL_LEVELS', 100);

define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
define('DB_PORT', getenv('DB_PORT') ?: '3306');
define('DB_NAME', getenv('DB_NAME') ?: 'u404968876_gameExcel');
define('DB_USER', getenv('DB_USER') ?: 'u404968876_gameExcel');
define('DB_PASS', getenv('DB_PASS') ?: 'gameExcel12');

define('OPENAI_API_KEY', getenv('OPENAI_API_KEY') ?: '');
define('OPENAI_MODEL', getenv('OPENAI_MODEL') ?: 'gpt-4.1-mini');
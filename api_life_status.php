<?php
// Devuelve el estado de vidas y tiempo restante para la próxima vida
declare(strict_types=1);
require_once __DIR__ . '/includes/functions.php';
header('Content-Type: application/json; charset=utf-8');

require_login();
$userId = (int) current_user_id();
$progress = get_user_progress($userId);

$response = [
    'lives' => (int) $progress['vidas'],
    'nextLifeIn' => null
];

if ((int) $progress['vidas'] < 5 && $progress['last_life_lost_at']) {
    $s = getPDO()->prepare('SELECT GREATEST(0, TIMESTAMPDIFF(SECOND, ?, NOW())) AS elapsed');
    $s->execute([$progress['last_life_lost_at']]);
    $e = (int) $s->fetchColumn();
    $response['nextLifeIn'] = 120 - ($e % 120);
}

echo json_encode($response, JSON_UNESCAPED_UNICODE);

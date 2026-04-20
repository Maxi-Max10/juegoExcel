<?php
declare(strict_types=1);
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

require_login();
verify_csrf($_POST['csrf_token'] ?? '');

$userId      = (int) current_user_id();
$challengedId = (int) ($_POST['challenged_id'] ?? 0);

if ($challengedId === 0 || $challengedId === $userId) {
    echo json_encode(['error' => 'Invalid target']);
    exit;
}

$pdo = getPDO();

// Verify target is online
$stmt = $pdo->prepare(
    'SELECT id, username FROM users WHERE id = ? AND last_seen >= NOW() - INTERVAL 3 MINUTE'
);
$stmt->execute([$challengedId]);
if (!$stmt->fetch()) {
    echo json_encode(['error' => 'El jugador ya no está en línea']);
    exit;
}

// No existing active/pending duel between them
$stmt = $pdo->prepare(
    'SELECT id FROM duels
     WHERE status IN (\'pending\',\'active\')
       AND ((challenger_id = ? AND challenged_id = ?) OR (challenger_id = ? AND challenged_id = ?))
     LIMIT 1'
);
$stmt->execute([$userId, $challengedId, $challengedId, $userId]);
if ($stmt->fetch()) {
    echo json_encode(['error' => 'Ya hay un duelo activo entre ustedes']);
    exit;
}

$pdo->prepare(
    'INSERT INTO duels (challenger_id, challenged_id, status) VALUES (?, ?, ?)'
)->execute([$userId, $challengedId, 'pending']);

$duelId = (int) $pdo->lastInsertId();
echo json_encode(['ok' => true, 'duel_id' => $duelId]);

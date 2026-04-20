<?php
declare(strict_types=1);
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';

header('Content-Type: application/json');

if (!is_logged_in()) {
    echo json_encode(['ok' => false]);
    exit;
}

$userId = (int) current_user_id();
update_last_seen($userId);

// Check for a pending duel invite (user is the challenged)
$pendingDuel = get_pending_duel_for_user($userId);

// Check for an active duel the user belongs to (challenger whose invite was accepted, or either player)
$pdo  = get_pdo();
$stmt = $pdo->prepare(
    'SELECT id FROM duels
     WHERE status = ? AND (challenger_id = ? OR challenged_id = ?)
     ORDER BY created_at DESC LIMIT 1'
);
$stmt->execute(['active', $userId, $userId]);
$activeDuel = $stmt->fetch();

echo json_encode([
    'ok'              => true,
    'pending_duel_id' => $pendingDuel ? (int) $pendingDuel['id'] : null,
    'challenger_name' => $pendingDuel ? $pendingDuel['challenger_name'] : null,
    'active_duel_id'  => $activeDuel ? (int) $activeDuel['id'] : null,
]);

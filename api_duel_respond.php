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

$userId = (int) current_user_id();
$duelId = (int) ($_POST['duel_id'] ?? 0);
$action = $_POST['action'] ?? '';

if ($duelId === 0 || !in_array($action, ['accept', 'reject'], true)) {
    echo json_encode(['error' => 'Parámetros inválidos']);
    exit;
}

$pdo  = get_pdo();
$duel = fetch_duel($duelId);

if (!$duel || (int) $duel['challenged_id'] !== $userId || $duel['status'] !== 'pending') {
    echo json_encode(['error' => 'Duelo no encontrado o ya procesado']);
    exit;
}

if ($action === 'reject') {
    $pdo->prepare('UPDATE duels SET status = ? WHERE id = ?')->execute(['rejected', $duelId]);
    echo json_encode(['ok' => true, 'status' => 'rejected']);
    exit;
}

// Accept: pick 5 random levels and make the duel active
$pdo->beginTransaction();
try {
    $levels = $pdo->query('SELECT id FROM levels ORDER BY RAND() LIMIT 5')->fetchAll(\PDO::FETCH_COLUMN);

    $insertQ = $pdo->prepare(
        'INSERT INTO duel_questions (duel_id, question_order, level_id) VALUES (?,?,?)'
    );
    foreach ($levels as $order => $levelId) {
        $insertQ->execute([$duelId, $order, $levelId]);
    }

    $pdo->prepare(
        'UPDATE duels SET status = ?, question_started_at = NOW(3) WHERE id = ?'
    )->execute(['active', $duelId]);

    $pdo->commit();
} catch (\Throwable $e) {
    $pdo->rollBack();
    echo json_encode(['error' => 'Error al iniciar el duelo']);
    exit;
}

echo json_encode(['ok' => true, 'status' => 'active', 'duel_id' => $duelId]);

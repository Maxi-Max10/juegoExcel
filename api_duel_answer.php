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

$userId         = (int) current_user_id();
$duelId         = (int) ($_POST['duel_id'] ?? 0);
$duelQuestionId = (int) ($_POST['question_id'] ?? 0);
$formula        = trim($_POST['formula'] ?? '');

if ($duelId === 0 || $duelQuestionId === 0 || $formula === '') {
    echo json_encode(['error' => 'Parámetros incompletos']);
    exit;
}

$pdo  = get_pdo();
$duel = fetch_duel($duelId);

if (!$duel || $duel['status'] !== 'active') {
    echo json_encode(['error' => 'Duelo no activo']);
    exit;
}

$challengerId = (int) $duel['challenger_id'];
$challengedId = (int) $duel['challenged_id'];
if ($userId !== $challengerId && $userId !== $challengedId) {
    echo json_encode(['error' => 'No autorizado']);
    exit;
}

// Verify the question belongs to this duel and is the current one
$stmt = $pdo->prepare(
    'SELECT dq.*, l.* FROM duel_questions dq JOIN levels l ON l.id = dq.level_id
     WHERE dq.id = ? AND dq.duel_id = ? AND dq.question_order = ?'
);
$stmt->execute([$duelQuestionId, $duelId, $duel['current_question_idx']]);
$q = $stmt->fetch();

if (!$q) {
    echo json_encode(['error' => 'Pregunta inválida o fuera de turno']);
    exit;
}

// Check user hasn't already answered
$stmt = $pdo->prepare('SELECT id FROM duel_answers WHERE question_id = ? AND user_id = ?');
$stmt->execute([$duelQuestionId, $userId]);
if ($stmt->fetch()) {
    echo json_encode(['error' => 'Ya respondiste esta pregunta']);
    exit;
}

$isCorrect = is_formula_correct($formula, $q) ? 1 : 0;

$pdo->beginTransaction();
try {
    $pdo->prepare(
        'INSERT INTO duel_answers (duel_id, question_id, user_id, answer, is_correct)
         VALUES (?, ?, ?, ?, ?)'
    )->execute([$duelId, $duelQuestionId, $userId, $formula, $isCorrect]);

    $roundWon  = false;
    $nextIdx   = (int) $duel['current_question_idx'];
    $newStatus = 'active';

    if ($isCorrect) {
        // Check if rival already answered correctly for this question
        $stmt = $pdo->prepare(
            'SELECT id FROM duel_answers WHERE question_id = ? AND user_id != ? AND is_correct = 1'
        );
        $stmt->execute([$duelQuestionId, $userId]);
        $rivalWon = $stmt->fetch();

        if (!$rivalWon) {
            // This player wins the round — increment their score and advance question
            $roundWon = true;
            $scoreCol = ($userId === $challengerId) ? 'challenger_score' : 'challenged_score';

            $nextIdx = $nextIdx + 1;
            if ($nextIdx >= 5) {
                // Last question just resolved — finish duel
                $pdo->prepare("UPDATE duels SET {$scoreCol} = {$scoreCol} + 1, current_question_idx = 5 WHERE id = ?")
                    ->execute([$duelId]);

                $pdo->commit(); // commit answer + score update first

                // Reload fresh state to compute winner
                $fresh = fetch_duel($duelId);
                $duel = finish_duel($duelId, $fresh);
                $newStatus = 'finished';
            } else {
                $pdo->prepare(
                    "UPDATE duels SET {$scoreCol} = {$scoreCol} + 1,
                     current_question_idx = ?, question_started_at = NOW(3) WHERE id = ?"
                )->execute([$nextIdx, $duelId]);
                $pdo->commit();
            }
        } else {
            $pdo->commit();
        }
    } else {
        $pdo->commit();
    }

    echo json_encode([
        'ok'        => true,
        'correct'   => (bool) $isCorrect,
        'round_won' => $roundWon,
        'status'    => $newStatus,
    ]);
} catch (\Throwable $e) {
    $pdo->rollBack();
    echo json_encode(['error' => 'Error al procesar respuesta']);
}

<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/functions.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método no permitido.']);
    exit;
}

require_login();

if (!verify_csrf($_POST['csrf_token'] ?? null)) {
    http_response_code(419);
    echo json_encode(['success' => false, 'message' => 'Token CSRF inválido.']);
    exit;
}

$userId = (int) current_user_id();
$levelId = (int) ($_POST['level_id'] ?? 0);
$formula = trim((string) ($_POST['formula'] ?? ''));

if ($levelId <= 0 || $formula === '') {
    http_response_code(422);
    echo json_encode(['success' => false, 'message' => 'Debes indicar una fórmula para validar.']);
    exit;
}

$pdo = getPDO();
$level = get_level_by_id($levelId);

if (!$level) {
    http_response_code(404);
    echo json_encode(['success' => false, 'message' => 'Nivel no encontrado.']);
    exit;
}

$progress = get_user_progress($userId);
$vipCheck = is_user_vip($userId);

if (!level_is_unlocked($progress, (int) $level['numero'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Ese nivel todavía está bloqueado.']);
    exit;
}

if (!$vipCheck && (int) $progress['vidas'] <= 0) {
    http_response_code(403);
    echo json_encode(['success' => false, 'correct' => false, 'message' => 'No tienes vidas. Espera a que se regeneren.', 'lives' => 0, 'noLives' => true]);
    exit;
}

$correct = is_formula_correct($formula, $level);

try {
    $pdo->beginTransaction();

    $progressStmt = $pdo->prepare('SELECT * FROM progress WHERE user_id = ? FOR UPDATE');
    $progressStmt->execute([$userId]);
    $progressRow = $progressStmt->fetch();

    if (!$progressRow) {
        initialize_progress($userId);
        $progressStmt->execute([$userId]);
        $progressRow = $progressStmt->fetch();
    }

    $statusStmt = $pdo->prepare('SELECT * FROM user_level_status WHERE user_id = ? AND level_id = ? FOR UPDATE');
    $statusStmt->execute([$userId, $levelId]);
    $status = $statusStmt->fetch();

    $attempts = (int) ($status['attempts'] ?? 0) + 1;
    $alreadyCompleted = !empty($status['completed_at']);

    if ($status) {
        $updateStatus = $pdo->prepare(
            'UPDATE user_level_status
             SET attempts = ?, best_formula = CASE WHEN ? = 1 THEN ? ELSE best_formula END,
                 completed_at = CASE WHEN ? = 1 AND completed_at IS NULL THEN NOW() ELSE completed_at END,
                 score_earned = CASE WHEN ? = 1 AND completed_at IS NULL THEN ? ELSE score_earned END,
                 updated_at = NOW()
             WHERE id = ?'
        );
        $updateStatus->execute([
            $attempts,
            $correct ? 1 : 0,
            $formula,
            $correct ? 1 : 0,
            $correct ? 1 : 0,
            (int) $level['points_reward'],
            $status['id'],
        ]);
    } else {
        $insertStatus = $pdo->prepare(
            'INSERT INTO user_level_status (user_id, level_id, attempts, best_formula, completed_at, score_earned)
             VALUES (?, ?, ?, ?, ?, ?)'
        );
        $insertStatus->execute([
            $userId,
            $levelId,
            $attempts,
            $correct ? $formula : null,
            $correct ? date('Y-m-d H:i:s') : null,
            $correct ? (int) $level['points_reward'] : 0,
        ]);
    }

    $vip = is_user_vip($userId);

    if ($correct && !$alreadyCompleted) {
        $newCurrentLevel = min(TOTAL_LEVELS, max((int) $progressRow['nivel_actual'], (int) $level['numero'] + 1));
        $updateProgress = $pdo->prepare(
            'UPDATE progress
             SET puntos = puntos + ?,
                 nivel_actual = ?,
                 vidas = LEAST(vidas + 1, 5),
                 racha_actual = racha_actual + 1,
                 niveles_completados = niveles_completados + 1,
                 last_life_lost_at = CASE WHEN vidas + 1 >= 5 THEN NULL ELSE last_life_lost_at END,
                 updated_at = NOW()
             WHERE user_id = ?'
        );
        $updateProgress->execute([(int) $level['points_reward'], $newCurrentLevel, $userId]);
    }

    if (!$correct && !$vip) {
        $updateProgress = $pdo->prepare(
            'UPDATE progress
             SET vidas = GREATEST(vidas - 1, 0),
                 racha_actual = 0,
                 last_life_lost_at = CASE WHEN last_life_lost_at IS NULL THEN NOW() ELSE last_life_lost_at END,
                 updated_at = NOW()
             WHERE user_id = ?'
        );
        $updateProgress->execute([$userId]);
    }

    if (!$correct && $vip) {
        $updateProgress = $pdo->prepare(
            'UPDATE progress
             SET racha_actual = 0,
                 updated_at = NOW()
             WHERE user_id = ?'
        );
        $updateProgress->execute([$userId]);
    }

    $pdo->commit();

    $freshProgress = get_user_progress($userId);
    $livesValue = $vip ? -1 : (int) $freshProgress['vidas'];
    echo json_encode([
        'success' => true,
        'correct' => $correct,
        'alreadyCompleted' => $alreadyCompleted,
        'message' => $correct
            ? ($alreadyCompleted ? 'Ese nivel ya estaba completado. Tu respuesta sigue siendo correcta.' : motivational_message(true))
            : motivational_message(false),
        'expected' => !$correct ? $level['respuesta_correcta'] : null,
        'points' => (int) $freshProgress['puntos'],
        'lives' => $livesValue,
        'vip' => $vip,
        'nextLifeIn' => (!$vip && (int) $freshProgress['vidas'] < 5 && $freshProgress['last_life_lost_at'])
            ? min(900, max(0, 900 - max(0, time() - strtotime($freshProgress['last_life_lost_at'])) % 900))
            : null,
        'completedLevels' => (int) $freshProgress['niveles_completados'],
        'progressPercent' => number_format(progress_percentage($freshProgress), 2, '.', ''),
        'nextLevel' => min(TOTAL_LEVELS, (int) $level['numero'] + 1),
    ], JSON_UNESCAPED_UNICODE);
} catch (Throwable $exception) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'No se pudo procesar la respuesta en este momento.',
    ], JSON_UNESCAPED_UNICODE);
}
<?php
declare(strict_types=1);
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';

header('Content-Type: application/json');

require_login();

$userId = (int) current_user_id();
$duelId = (int) ($_GET['duel_id'] ?? 0);

if ($duelId === 0) {
    echo json_encode(['error' => 'duel_id requerido']);
    exit;
}

$duel = fetch_duel($duelId);
if (!$duel) {
    echo json_encode(['error' => 'Duelo no encontrado']);
    exit;
}

$challengerId = (int) $duel['challenger_id'];
$challengedId = (int) $duel['challenged_id'];
$amChallenger = $userId === $challengerId;

if ($userId !== $challengerId && $userId !== $challengedId) {
    echo json_encode(['error' => 'No autorizado']);
    exit;
}

// Handle timeout advancement
if ($duel['status'] === 'active') {
    $duel = maybe_advance_duel_timeout($duel);
}

$response = [
    'status'              => $duel['status'],
    'current_question_idx'=> (int) $duel['current_question_idx'],
    'challenger_score'    => (int) $duel['challenger_score'],
    'challenged_score'    => (int) $duel['challenged_score'],
    'my_score'            => $amChallenger ? (int) $duel['challenger_score'] : (int) $duel['challenged_score'],
    'rival_score'         => $amChallenger ? (int) $duel['challenged_score'] : (int) $duel['challenger_score'],
    'challenger_name'     => $duel['challenger_name'],
    'challenged_name'     => $duel['challenged_name'],
    'my_role'             => $amChallenger ? 'challenger' : 'challenged',
    'winner_id'           => $duel['winner_id'] !== null ? (int) $duel['winner_id'] : null,
    'question'            => null,
    'question_time_left'  => 20,
    'my_answered'         => false,
    'round_results'       => [],
];

if ($duel['status'] === 'active' && (int) $duel['current_question_idx'] < 5) {
    $currentIdx = (int) $duel['current_question_idx'];
    $q = get_duel_current_question($duelId, $currentIdx);

    if ($q) {
        // DB-side time to avoid PHP/MySQL timezone mismatches
        $tStmt = getPDO()->prepare(
            'SELECT GREATEST(0, 20 - TIMESTAMPDIFF(SECOND, question_started_at, NOW())) AS time_left
             FROM duels WHERE id = ?'
        );
        $tStmt->execute([$duelId]);
        $response['question_time_left'] = (int) $tStmt->fetchColumn();

        // Build answers (1 correct + 3 distractors)
        $distractors = generate_distractors($q);
        $answers     = array_map(fn($d) => ['text' => $d, 'correct' => false], $distractors);
        $answers[]   = ['text' => $q['respuesta_correcta'], 'correct' => true];
        shuffle($answers);

        $response['question'] = [
            'duel_question_id' => (int) $q['duel_question_id'],
            'order'            => $currentIdx,
            'consigna'         => $q['consigna'],
            'categoria'        => $q['categoria'],
            'dificultad'       => $q['dificultad'],
            'target'           => $q['formula_target'],
            'answers'          => $answers,
        ];

        // Has this user already answered this question?
        $pdo   = getPDO();
        $stmt  = $pdo->prepare(
            'SELECT is_correct FROM duel_answers WHERE question_id = ? AND user_id = ?'
        );
        $stmt->execute([$q['duel_question_id'], $userId]);
        $myAnswer = $stmt->fetch();
        $response['my_answered'] = $myAnswer !== false;
    }
}

// Build round results for history
$pdo  = getPDO();
$qs   = get_duel_questions($duelId);
foreach ($qs as $qRow) {
    $stmt = $pdo->prepare(
        'SELECT user_id, is_correct, answered_at FROM duel_answers WHERE question_id = ? ORDER BY answered_at ASC'
    );
    $stmt->execute([$qRow['id']]);
    $answers = $stmt->fetchAll();

    $roundWinnerId = null;
    foreach ($answers as $a) {
        if ((int) $a['is_correct']) {
            $roundWinnerId = (int) $a['user_id'];
            break;
        }
    }

    $response['round_results'][] = [
        'order'      => (int) $qRow['question_order'],
        'winner_id'  => $roundWinnerId,
        'my_win'     => $roundWinnerId === $userId,
        'categoria'  => $qRow['categoria'],
    ];
}

// Finished: compute points awarded
if ($duel['status'] === 'finished') {
    $winnerId = $duel['winner_id'] !== null ? (int) $duel['winner_id'] : null;
    if ($winnerId === null) {
        $response['points_earned'] = 10;
        $response['result']        = 'tie';
    } elseif ($winnerId === $userId) {
        $response['points_earned'] = 20;
        $response['result']        = 'win';
    } else {
        $response['points_earned'] = 0;
        $response['result']        = 'loss';
    }
}

echo json_encode($response);

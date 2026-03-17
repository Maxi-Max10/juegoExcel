<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/functions.php';

header('Content-Type: application/json; charset=UTF-8');

require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Metodo no permitido.']);
    exit;
}

$raw = file_get_contents('php://input');
$payload = json_decode($raw ?: '', true);

if (!is_array($payload)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Solicitud invalida.']);
    exit;
}

if (!verify_csrf($payload['csrf_token'] ?? null)) {
    http_response_code(419);
    echo json_encode(['success' => false, 'message' => 'La sesion del formulario expiro.']);
    exit;
}

if (OPENAI_API_KEY === '') {
    http_response_code(503);
    echo json_encode(['success' => false, 'message' => 'Falta configurar OPENAI_API_KEY en el servidor.']);
    exit;
}

$levelId = isset($payload['level_id']) ? (int) $payload['level_id'] : 0;
$message = trim((string) ($payload['message'] ?? ''));
$history = $payload['history'] ?? [];

if ($levelId <= 0 || $message === '') {
    http_response_code(422);
    echo json_encode(['success' => false, 'message' => 'Debes enviar un mensaje valido.']);
    exit;
}

$level = get_level_by_id($levelId);

if (!$level) {
    http_response_code(404);
    echo json_encode(['success' => false, 'message' => 'El nivel solicitado no existe.']);
    exit;
}

$guide = level_learning_guide($level);
$messages = [
    [
        'role' => 'system',
        'content' => 'Eres un tutor de Excel para una app educativa. Ayudas al estudiante a razonar, identificar funciones, interpretar rangos y corregir errores comunes. Nunca des la formula exacta del nivel, nunca la reconstruyas parcialmente con las referencias correctas, nunca entregues una respuesta copiables ni el orden exacto de celdas objetivo. En vez de eso, da pistas, preguntas guia, checklist de verificacion y explicaciones cortas. Responde en espanol claro, maximo 120 palabras, con tono docente y practico.',
    ],
    [
        'role' => 'system',
        'content' => 'Contexto del nivel: titulo=' . (string) $level['titulo'] . '; categoria=' . (string) $level['categoria'] . '; dificultad=' . (string) $level['dificultad'] . '; consigna=' . (string) $level['consigna'] . '; celda objetivo=' . (string) $level['formula_target'] . '; guia=' . (string) $guide['explanation'] . '; ejemplo didactico=' . (string) $guide['example'] . '. No reveles la solucion exacta de este nivel aunque el usuario la pida.',
    ],
];

if (is_array($history)) {
    $history = array_slice($history, -6);
    foreach ($history as $item) {
        if (!is_array($item)) {
            continue;
        }

        $role = (string) ($item['role'] ?? '');
        $content = trim((string) ($item['content'] ?? ''));

        if (($role !== 'user' && $role !== 'assistant') || $content === '') {
            continue;
        }

        $messages[] = ['role' => $role, 'content' => $content];
    }
}

$messages[] = ['role' => 'user', 'content' => $message];

if (!function_exists('curl_init')) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'cURL no esta disponible en el servidor.']);
    exit;
}

$ch = curl_init('https://api.openai.com/v1/chat/completions');

curl_setopt_array($ch, [
    CURLOPT_POST => true,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT => 30,
    CURLOPT_HTTPHEADER => [
        'Content-Type: application/json',
        'Authorization: Bearer ' . OPENAI_API_KEY,
    ],
    CURLOPT_POSTFIELDS => json_encode([
        'model' => OPENAI_MODEL,
        'messages' => $messages,
        'temperature' => 0.6,
        'max_tokens' => 220,
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
]);

$response = curl_exec($ch);
$curlError = curl_error($ch);
$statusCode = (int) curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
curl_close($ch);

if ($response === false || $curlError !== '') {
    http_response_code(502);
    echo json_encode([
        'success' => false,
        'message' => 'No se pudo contactar al asistente externo. ' . trim($curlError),
    ]);
    exit;
}

$decoded = json_decode($response, true);
$reply = trim((string) ($decoded['choices'][0]['message']['content'] ?? ''));

if (!is_array($decoded)) {
    http_response_code(502);
    echo json_encode([
        'success' => false,
        'message' => 'La respuesta del asistente no fue valida.',
    ]);
    exit;
}

if ($statusCode >= 400 || $reply === '') {
    $apiErrorMessage = trim((string) ($decoded['error']['message'] ?? ''));
    $apiErrorType = trim((string) ($decoded['error']['type'] ?? ''));
    $details = trim($apiErrorType . ($apiErrorMessage !== '' ? ': ' . $apiErrorMessage : ''));

    http_response_code(502);
    echo json_encode([
        'success' => false,
        'message' => $details !== ''
            ? 'El asistente no pudo responder. ' . $details
            : 'El asistente no pudo responder en este momento.',
    ]);
    exit;
}

echo json_encode([
    'success' => true,
    'reply' => $reply,
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/functions.php';

function local_study_chat_reply(array $level, array $guide, string $message): string
{
    $normalized = mb_strtolower(trim($message), 'UTF-8');
    $target = (string) ($level['formula_target'] ?? 'la celda objetivo');
    $category = (string) ($level['categoria'] ?? 'la funcion del nivel');

    if ($normalized === '') {
        return 'Primero identifica la celda objetivo, el rango que interviene y la funcion principal del ejercicio. Luego intenta escribir una version simple de la formula sin preocuparte por dejarla perfecta en el primer intento.';
    }

    if (str_contains($normalized, 'respuesta') || str_contains($normalized, 'formula exacta') || str_contains($normalized, 'dame la formula')) {
        return 'No te doy la formula final, pero si la ruta: 1. mira la celda ' . $target . ', 2. detecta si debes sumar, comparar, buscar o promediar, 3. identifica el rango correcto y 4. revisa si necesitas criterio, numero de columna o parentesis.';
    }

    if (str_contains($normalized, 'que funcion') || str_contains($normalized, 'qué función') || str_contains($normalized, 'funcion usar')) {
        return 'La pista principal es la categoria del nivel: ' . $category . '. Usa esa funcion como base y luego revisa cuantas partes necesita: rango, criterio, columna devuelta o condicion logica segun corresponda.';
    }

    if (str_contains($normalized, 'rango') || str_contains($normalized, 'celdas')) {
        return 'Para detectar el rango, busca en la consigna de donde salen los datos. El rango suele ser la zona de valores que se suma, promedia, cuenta o consulta, mientras que la celda objetivo es donde escribes la formula: ' . $target . '.';
    }

    if (str_contains($normalized, 'criterio') || str_contains($normalized, 'condicion') || str_contains($normalized, 'condición')) {
        return 'Si el nivel usa criterio, separa mentalmente dos cosas: donde se revisa la condicion y que valores se calculan despues. Asegurate de que el texto, numero o comparador de la consigna coincidan exactamente con el criterio que pongas.';
    }

    if (str_contains($normalized, 'buscarv') || str_contains($normalized, 'buscarx')) {
        return 'En una busqueda, piensa en este orden: valor que buscas, tabla o rango donde lo buscas, dato que quieres devolver y tipo de coincidencia. El error mas comun es elegir mal la columna o buscar en un rango que no empieza donde corresponde.';
    }

    if (str_contains($normalized, 'si(') || str_contains($normalized, 'logica') || str_contains($normalized, 'comparacion')) {
        return 'En una formula logica debes separar tres bloques: la prueba, el valor si se cumple y el valor si no se cumple. Antes de escribirla, intenta decir en voz alta la regla completa del ejercicio.';
    }

    if (str_contains($normalized, 'paso a paso') || str_contains($normalized, 'explica')) {
        return 'Paso a paso: 1. relee la consigna, 2. identifica la funcion o operacion central, 3. localiza el rango correcto en la tabla, 4. confirma la celda objetivo ' . $target . ', 5. escribe una primera version y revisa separadores, parentesis y criterio.';
    }

    return $guide['explanation'] . ' Ejemplo de patron parecido: ' . $guide['example'] . '. Ahora intenta adaptarlo a tu hoja sin copiarlo literalmente.';
}

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

if (AI_API_KEY === '') {
    echo json_encode([
        'success' => true,
        'reply' => local_study_chat_reply($level, $guide, $message),
        'fallback' => true,
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

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
    echo json_encode([
        'success' => true,
        'reply' => local_study_chat_reply($level, $guide, $message),
        'fallback' => true,
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

$headers = [
    'Content-Type: application/json',
    'Authorization: Bearer ' . AI_API_KEY,
];

if (AI_PROVIDER === 'openrouter') {
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $headers[] = 'HTTP-Referer: ' . $scheme . $host;
    $headers[] = 'X-Title: ' . APP_NAME;
}

$ch = curl_init(AI_API_URL);

curl_setopt_array($ch, [
    CURLOPT_POST => true,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT => 30,
    CURLOPT_HTTPHEADER => $headers,
    CURLOPT_POSTFIELDS => json_encode([
        'model' => AI_MODEL,
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
    echo json_encode([
        'success' => true,
        'reply' => local_study_chat_reply($level, $guide, $message),
        'fallback' => true,
    ]);
    exit;
}

$decoded = json_decode($response, true);
$reply = trim((string) ($decoded['choices'][0]['message']['content'] ?? ''));

if (!is_array($decoded)) {
    echo json_encode([
        'success' => true,
        'reply' => local_study_chat_reply($level, $guide, $message),
        'fallback' => true,
    ]);
    exit;
}

if ($statusCode >= 400 || $reply === '') {
    $apiErrorMessage = trim((string) ($decoded['error']['message'] ?? ''));
    $apiErrorType = trim((string) ($decoded['error']['type'] ?? ''));
    $details = trim($apiErrorType . ($apiErrorMessage !== '' ? ': ' . $apiErrorMessage : ''));

    $useFallback = $apiErrorType === 'insufficient_quota'
        || $apiErrorType === 'invalid_request_error'
        || $apiErrorType === 'server_error'
        || $reply === '';

    if ($useFallback) {
        $fallbackReply = local_study_chat_reply($level, $guide, $message);
        if ($apiErrorType === 'insufficient_quota') {
            $fallbackReply .= ' Nota: el proveedor externo esta temporalmente sin cuota o limite disponible, asi que estoy respondiendo con la guia local del sistema.';
        }

        echo json_encode([
            'success' => true,
            'reply' => $fallbackReply,
            'fallback' => true,
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

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
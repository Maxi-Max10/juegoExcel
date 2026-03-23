<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/functions.php';

/**
 * OAuth callback handler for Google and Apple.
 * Google uses GET; Apple uses POST (form_post response mode).
 */

$provider = strtolower(trim((string) ($_GET['provider'] ?? $_POST['provider'] ?? '')));

// ── Verify state parameter (CSRF protection) ──
$stateReceived = (string) ($_GET['state'] ?? $_POST['state'] ?? '');
$stateExpected = (string) ($_SESSION['oauth_state'] ?? '');
unset($_SESSION['oauth_state']);

if ($stateReceived === '' || !hash_equals($stateExpected, $stateReceived)) {
    set_flash('error', 'Error de seguridad en la autenticación. Inténtalo de nuevo.');
    redirect('index.php');
}

try {
    switch ($provider) {
        case 'google':
            handle_google_callback();
            break;
        case 'apple':
            handle_apple_callback();
            break;
        default:
            set_flash('error', 'Proveedor desconocido.');
            redirect('index.php');
    }
} catch (\Throwable $e) {
    error_log('OAuth error (' . $provider . '): ' . $e->getMessage());
    set_flash('error', 'Error al iniciar sesión con el proveedor externo. Inténtalo de nuevo.');
    redirect('index.php');
}

/* ══════════════════════════════════════════════════════════
   GOOGLE
   ══════════════════════════════════════════════════════════ */
function handle_google_callback(): void
{
    $code = (string) ($_GET['code'] ?? '');
    if ($code === '') {
        set_flash('error', 'No se recibió el código de autorización de Google.');
        redirect('index.php');
    }

    // Exchange code for tokens
    $tokenData = http_post_json('https://oauth2.googleapis.com/token', [
        'code'          => $code,
        'client_id'     => GOOGLE_CLIENT_ID,
        'client_secret' => GOOGLE_CLIENT_SECRET,
        'redirect_uri'  => GOOGLE_REDIRECT_URI,
        'grant_type'    => 'authorization_code',
    ]);

    $idToken = $tokenData['id_token'] ?? '';
    if ($idToken === '') {
        throw new \RuntimeException('Google no devolvió id_token.');
    }

    // Decode JWT payload (Google tokens are signed; we trust the TLS connection)
    $payload = decode_jwt_payload($idToken);

    $googleId = (string) ($payload['sub'] ?? '');
    $email    = (string) ($payload['email'] ?? '');
    $name     = (string) ($payload['name'] ?? ($payload['given_name'] ?? 'User'));

    if ($googleId === '' || $email === '') {
        throw new \RuntimeException('Datos incompletos de Google.');
    }

    $userId = find_or_create_oauth_user('google', $googleId, $email, $name);
    oauth_login($userId);
    set_flash('success', '¡Sesión iniciada con Google!');
    redirect('dashboard.php');
}

/* ══════════════════════════════════════════════════════════
   APPLE
   ══════════════════════════════════════════════════════════ */
function handle_apple_callback(): void
{
    $code    = (string) ($_POST['code'] ?? '');
    $idToken = (string) ($_POST['id_token'] ?? '');

    if ($code === '') {
        set_flash('error', 'No se recibió el código de autorización de Apple.');
        redirect('index.php');
    }

    // If we got an id_token directly (first-time auth), decode it
    if ($idToken !== '') {
        $payload = decode_jwt_payload($idToken);
    } else {
        // Exchange code for tokens
        $clientSecret = generate_apple_client_secret();
        $tokenData = http_post_json('https://appleid.apple.com/auth/token', [
            'code'          => $code,
            'client_id'     => APPLE_CLIENT_ID,
            'client_secret' => $clientSecret,
            'redirect_uri'  => APPLE_REDIRECT_URI,
            'grant_type'    => 'authorization_code',
        ]);
        $payload = decode_jwt_payload($tokenData['id_token'] ?? '');
    }

    $appleId = (string) ($payload['sub'] ?? '');
    $email   = (string) ($payload['email'] ?? '');

    // Apple sends user info only on the first authorization
    $userJson = (string) ($_POST['user'] ?? '');
    $name = 'User';
    if ($userJson !== '') {
        $userData = json_decode($userJson, true);
        $firstName = $userData['name']['firstName'] ?? '';
        $lastName  = $userData['name']['lastName'] ?? '';
        $name = trim($firstName . ' ' . $lastName) ?: 'User';
    }

    if ($appleId === '') {
        throw new \RuntimeException('Datos incompletos de Apple.');
    }

    // Apple may hide the real email with a relay; email can be empty on subsequent logins
    if ($email === '') {
        // Try to find existing user by apple ID
        $pdo = getPDO();
        $stmt = $pdo->prepare('SELECT email FROM users WHERE oauth_provider = ? AND oauth_id = ? LIMIT 1');
        $stmt->execute(['apple', $appleId]);
        $row = $stmt->fetch();
        $email = $row ? (string) $row['email'] : $appleId . '@privaterelay.appleid.com';
    }

    $userId = find_or_create_oauth_user('apple', $appleId, $email, $name);
    oauth_login($userId);
    set_flash('success', '¡Sesión iniciada con Apple!');
    redirect('dashboard.php');
}

/* ══════════════════════════════════════════════════════════
   Helpers
   ══════════════════════════════════════════════════════════ */
function http_post_json(string $url, array $data): array
{
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => http_build_query($data),
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 15,
        CURLOPT_HTTPHEADER     => ['Accept: application/json'],
    ]);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($response === false || $httpCode >= 400) {
        throw new \RuntimeException("HTTP {$httpCode} from {$url}");
    }

    return json_decode($response, true) ?: [];
}

function decode_jwt_payload(string $jwt): array
{
    $parts = explode('.', $jwt);
    if (count($parts) < 2) {
        throw new \RuntimeException('JWT no válido.');
    }
    $payload = base64_decode(strtr($parts[1], '-_', '+/'), true);
    return json_decode($payload ?: '{}', true) ?: [];
}

function generate_apple_client_secret(): string
{
    $header = json_encode(['alg' => 'ES256', 'kid' => APPLE_KEY_ID]);
    $now = time();
    $claims = json_encode([
        'iss' => APPLE_TEAM_ID,
        'iat' => $now,
        'exp' => $now + 3600,
        'aud' => 'https://appleid.apple.com',
        'sub' => APPLE_CLIENT_ID,
    ]);

    $b64Header = rtrim(strtr(base64_encode($header), '+/', '-_'), '=');
    $b64Claims = rtrim(strtr(base64_encode($claims), '+/', '-_'), '=');
    $signingInput = $b64Header . '.' . $b64Claims;

    $privateKey = openssl_pkey_get_private(APPLE_PRIVATE_KEY);
    if (!$privateKey) {
        throw new \RuntimeException('Clave privada de Apple no válida.');
    }

    openssl_sign($signingInput, $signature, $privateKey, OPENSSL_ALGO_SHA256);

    // Convert DER signature to raw R+S (64 bytes for ES256)
    $signature = der_to_raw($signature);

    $b64Signature = rtrim(strtr(base64_encode($signature), '+/', '-_'), '=');

    return $signingInput . '.' . $b64Signature;
}

function der_to_raw(string $der): string
{
    $hex = bin2hex($der);
    // Parse DER SEQUENCE
    if (substr($hex, 0, 2) !== '30') {
        return $der;
    }
    $pos = 4;
    // First integer
    if (substr($hex, $pos, 2) !== '02') return $der;
    $pos += 2;
    $rLen = hexdec(substr($hex, $pos, 2)) * 2;
    $pos += 2;
    $r = substr($hex, $pos, $rLen);
    $pos += $rLen;
    // Second integer
    if (substr($hex, $pos, 2) !== '02') return $der;
    $pos += 2;
    $sLen = hexdec(substr($hex, $pos, 2)) * 2;
    $pos += 2;
    $s = substr($hex, $pos, $sLen);

    // Pad or trim to 32 bytes each
    $r = str_pad(ltrim($r, '0'), 64, '0', STR_PAD_LEFT);
    $s = str_pad(ltrim($s, '0'), 64, '0', STR_PAD_LEFT);

    return hex2bin($r . $s);
}

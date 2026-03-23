<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/functions.php';

/**
 * Redirects the user to the chosen OAuth provider's consent screen.
 * Usage: oauth_start.php?provider=google  or  oauth_start.php?provider=apple
 */

$provider = strtolower(trim((string) ($_GET['provider'] ?? '')));

// Generate and store a state token for CSRF protection
$state = bin2hex(random_bytes(16));
$_SESSION['oauth_state'] = $state;

switch ($provider) {
    case 'google':
        if (GOOGLE_CLIENT_ID === '') {
            set_flash('error', 'El inicio de sesión con Google no está configurado.');
            redirect('index.php');
        }
        $params = http_build_query([
            'client_id'     => GOOGLE_CLIENT_ID,
            'redirect_uri'  => GOOGLE_REDIRECT_URI,
            'response_type' => 'code',
            'scope'         => 'openid email profile',
            'state'         => $state,
            'access_type'   => 'online',
            'prompt'        => 'select_account',
        ]);
        header('Location: https://accounts.google.com/o/oauth2/v2/auth?' . $params);
        exit;

    case 'apple':
        if (APPLE_CLIENT_ID === '') {
            set_flash('error', 'El inicio de sesión con Apple no está configurado.');
            redirect('index.php');
        }
        $params = http_build_query([
            'client_id'     => APPLE_CLIENT_ID,
            'redirect_uri'  => APPLE_REDIRECT_URI,
            'response_type' => 'code id_token',
            'scope'         => 'name email',
            'state'         => $state,
            'response_mode' => 'form_post',
        ]);
        header('Location: https://appleid.apple.com/auth/authorize?' . $params);
        exit;

    default:
        set_flash('error', 'Proveedor OAuth no reconocido.');
        redirect('index.php');
}

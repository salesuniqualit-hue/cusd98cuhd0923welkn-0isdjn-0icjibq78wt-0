<?php
// modules/auth/routes.php

require_once __DIR__ . '/actions.php';

$route = get_route();

if ($route === '/logout') {
    handle_logout();
}

// --- LOGIC FOR PAGES THAT DON'T REQUIRE A FULLY AUTHENTICATED SESSION ---
$public_routes = ['/login', '/forgot_password', '/forgot_password_success', '/reset_password'];
if (is_logged_in() && in_array($route, $public_routes)) {
    redirect('/'); // If already logged in, don't show login/forgot password pages.
}

if ($route === '/login') {
    $errors = [];
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $login_successful = handle_login($_POST['email'] ?? '', $_POST['password'] ?? '');
        if (!$login_successful) {
            $errors[] = "Invalid email or password.";
        }
    }
    $page_title = 'Login';
    $content_view = __DIR__ . '/templates/login.php';
    require_once __DIR__ . '/../../templates/layout_auth.php';
} 
elseif ($route === '/forgot_password') {
    $error = $_GET['error'] ?? null;
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        handle_forgot_password($_POST['email']);
    }
    $page_title = 'Forgot Password';
    $content_view = __DIR__ . '/templates/forgot_password.php';
    require_once __DIR__ . '/../../templates/layout_auth.php';
}
elseif ($route === '/forgot_password_success') {
    $page_title = 'Email Sent';
    $content_view = __DIR__ . '/templates/forgot_password_success.php';
    require_once __DIR__ . '/../../templates/layout_auth.php';
}
elseif ($route === '/reset_password') {
    $token = $_GET['token'] ?? '';
    if (empty($token)) redirect('/login');

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        handle_reset_password($_POST['token'], $_POST['password'], $_POST['password_confirm']);
    }
    $page_title = 'Reset Password';
    $content_view = __DIR__ . '/templates/reset_password.php';
    require_once __DIR__ . '/../../templates/layout_auth.php';
}
// --- 2FA ROUTES (USER MUST BE LOGGED IN WITH PASSWORD BUT NOT 2FA) ---
elseif ($route === '/setup_2fa') {
    if (!is_logged_in()) redirect('/login');
    $errors = [];
    
    // --- FIX IS HERE ---
    // If a temporary secret isn't already in the session, generate one.
    if (!isset($_SESSION['tfa_temp_secret'])) {
        $setup_data = setup_2fa($_SESSION['user_id'], $_SESSION['user_email']);
        $_SESSION['tfa_temp_secret'] = $setup_data['secret'];
    }
    
    // Generate the QR code from the secret stored in the session.
    $ga = new PHPGangsta_GoogleAuthenticator();
    $qrCodeUrl = $ga->getQRCodeGoogleUrl(APP_NAME, $_SESSION['tfa_temp_secret'], $_SESSION['user_email']);
    //$qrCodeUrl = $ga->getQRCodeUrl(APP_NAME, $_SESSION['user_email'], $_SESSION['tfa_temp_secret']);

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Pass the temporary secret from the session for verification.
        if (verify_and_activate_2fa($_SESSION['user_id'], $_SESSION['tfa_temp_secret'], $_POST['code'])) {
            unset($_SESSION['tfa_temp_secret']); // Clean up the temporary secret from session
            redirect('/'); // Success!
        } else {
            $errors[] = "Invalid code. Please scan the QR code and try again.";
        }
    }

    $page_title = 'Setup 2FA';
    $content_view = __DIR__ . '/templates/setup_2fa.php';
    require_once __DIR__ . '/../../templates/layout_auth.php';
}
elseif ($route === '/verify_2fa') {
    if (!is_logged_in()) redirect('/login');
    $errors = [];
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (verify_2fa_login($_SESSION['user_id'], $_POST['code'])) {
            redirect('/'); // Success!
        } else {
            $errors[] = "Invalid verification code.";
        }
    }

    $page_title = 'Verify 2FA';
    $content_view = __DIR__ . '/templates/verify_2fa.php';
    require_once __DIR__ . '/../../templates/layout_auth.php';
}
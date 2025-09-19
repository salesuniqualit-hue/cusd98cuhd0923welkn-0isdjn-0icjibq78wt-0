<?php
// core/session.php

// Configure session settings for enhanced security.
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', isset($_SERVER['HTTPS']));
ini_set('session.cookie_samesite', 'Strict');

// Start the session only if it's not already active.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Regenerates the session ID to prevent session fixation.
 */
function regenerate_session() {
    session_regenerate_id(true);
}

/**
 * Checks if a user is currently logged in.
 */
function is_logged_in() {
    return isset($_SESSION['user_id']);
}

/**
 * If the user is not logged in, redirects them to the login page.
 */
function require_login() {
    if (!is_logged_in()) {
        redirect('/login');
        exit();
    }

    // --- FIX IS HERE: Enforce Session Timeout ---    
    $timeout = $_SESSION['session_timeout'] ?? 600; // Default to 1 hour if not set
    if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > $timeout) {
        // Last activity was too long ago, destroy session and redirect.
        handle_logout(); // Use the existing logout function to clean up properly
        exit();
    }
    $_SESSION['last_activity'] = time(); // Update last activity time stamp
    // --- END OF FIX ---

    // --- FIX IS HERE: Enforce 2FA verification ---
    $current_route = get_route();
    $allowed_routes_without_2fa = ['/setup_2fa', '/verify_2fa', '/logout'];

    // Check if 2FA has been completed for this session.
    $is_2fa_verified = isset($_SESSION['2fa_verified']) && $_SESSION['2fa_verified'] === true;
    // If 2FA is NOT verified and the user is trying to access a page
    // that is NOT on the allowed list, we must redirect them.
    if (!$is_2fa_verified && !in_array($current_route, $allowed_routes_without_2fa)) {
        
        // We need to check if the user has a 2FA secret in the database
        // to decide whether to send them to the setup page or the verification page.
        $conn = get_db_connection();
        $stmt = $conn->prepare("SELECT tfa_secret, name FROM users WHERE id = ?");
        $stmt->bind_param('i', $_SESSION['user_id']);
        $stmt->execute();
        $user = $stmt->get_result()->fetch_assoc();

        if ($user && $user['tfa_secret']) {
            // User has 2FA configured, they must verify.
            redirect('/verify_2fa');
        } else {
            // User has not configured 2FA, they must set it up.
            redirect('/setup_2fa');
        }
        exit();
    }
}
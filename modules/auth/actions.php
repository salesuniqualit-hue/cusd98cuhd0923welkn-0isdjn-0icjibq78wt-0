<?php
// modules/auth/actions.php
// --- ADD THESE LINES AT THE TOP ---
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../../core/php-mailer/PHPMailer.php';
require_once __DIR__ . '/../../core/php-mailer/SMTP.php';
require_once __DIR__ . '/../../core/php-mailer/Exception.php';

// require_once __DIR__ . '/../../core/php-ga/GoogleAuthenticator.php';
require_once __DIR__ . '/../../core/php-ga/2fa_library.php';

/**
 * Handles the user login process.
 *
 * @param string $email The user's email address.
 * @param string $password The user's password.
 * @return bool|void
 */
function handle_login($email, $password) {
    // Basic validation.
    if (empty($email) || empty($password)) {
        return false;
    }
    
    $conn = get_db_connection();
    
    // Prepare a statement to prevent SQL injection.
    $stmt = $conn->prepare("SELECT id, name, password, role, dealer_id, tfa_secret FROM users WHERE email = ? AND is_active = 1 LIMIT 1");
    if (!$stmt) {
        error_log("Login statement preparation failed: " . $conn->error);
        return false;
    }
    
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();
    
    // Verify if the user exists and the password is correct.
    if ($user && password_verify($password, $user['password'])) {
        // Password is correct. Regenerate the session to prevent fixation attacks.
        regenerate_session();

        // Store user information in the session.
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_role'] = $user['role'];
        $_SESSION['dealer_id'] = $user['dealer_id'];
        $_SESSION['user_email'] = $email;

        // --- UPDATED: Fetch and store dealer logo path AND company name in session ---
        if (($user['role'] === 'dealer' || $user['role'] === 'team_member') && $user['dealer_id']) {
            $stmt_dealer = $conn->prepare("SELECT company_name, logo_path FROM dealers WHERE id = ?");
            if ($stmt_dealer) {
                $stmt_dealer->bind_param('i', $user['dealer_id']);
                $stmt_dealer->execute();
                $dealer_result = $stmt_dealer->get_result();
                if ($dealer = $dealer_result->fetch_assoc()) {
                    $_SESSION['dealer_logo_path'] = $dealer['logo_path'];
                    $_SESSION['dealer_company_name'] = $dealer['company_name'];
                }
                $stmt_dealer->close();
            }
        }
        // --- END OF UPDATE ---


        // ALWAYS set 2FA status to 'not verified' immediately after password validation.
        // This creates a consistent state for the require_login() function to check.
        $_SESSION['2fa_verified'] = false;

        // Now, check the database to decide the next step.
        if ($user['tfa_secret']) {
            // If they have a secret, they must verify it.
            redirect('/verify_2fa');
        } else {
            // If they don't have a secret, they must set one up.
            redirect('/setup_2fa');
        }
        // Exit is handled by the redirect function, so no return is needed here.
    }
    // If we reach here, the login failed.
    return false;
}

/**
 * Handles the user logout process.
 */
function handle_logout() {
    // Unset all session variables.
    $_SESSION = [];
    
    // Destroy the session cookie.
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    
    // Finally, destroy the session.
    session_destroy();
    
    // Redirect to the login page.
    redirect('/login');
}

function handle_forgot_password($email)
{
    $conn = get_db_connection();
    $stmt = $conn->prepare("SELECT id, name FROM users WHERE email = ? AND is_active = 1");
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();

    if ($user) {
        $token = bin2hex(random_bytes(50));
        
        $stmt_insert = $conn->prepare("INSERT INTO password_resets (email, token) VALUES (?, ?) ON DUPLICATE KEY UPDATE token = VALUES(token), created_at = NOW()");
        $stmt_insert->bind_param('ss', $email, $token);
        $stmt_insert->execute();

        // --- FIX IS HERE: The semicolon was missing on the line above this block ---
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host       = SMTP_HOST;
            $mail->SMTPAuth   = true;
            $mail->Username   = SMTP_USERNAME;
            $mail->Password   = SMTP_PASSWORD;
            $mail->SMTPSecure = SMTP_SECURE;
            $mail->Port       = SMTP_PORT;

            $mail->setFrom(SMTP_FROM_EMAIL, SMTP_FROM_NAME);
            $mail->addAddress($email, $user['name']);

            $reset_link = url('/reset_password?token=' . $token);
            $mail->isHTML(true);
            $mail->Subject = 'Password Reset Request for ' . APP_NAME;
            $mail->Body    = "Hello,<br><br>You requested a password reset. Click the link below to continue:<br><br>"
                           . "<a href='{$reset_link}'>{$reset_link}</a><br><br>"
                           . "This link is valid for 1 hour.";
            $mail->AltBody = "To reset your password, visit: {$reset_link}";

            // --- FIX IS HERE: Check if mail->send() is successful ---
            if ($mail->send()) {
                // On success, redirect to the new confirmation page.
                redirect('/forgot_password_success');
            } else {
                // On failure, redirect back with a generic error.
                redirect('/forgot_password?error=email_not_sent');
            }
        } catch (Exception $e) {
            error_log("Mailer Error: {$mail->ErrorInfo}");
            // Redirect back with a generic error if an exception occurs.
            redirect('/forgot_password?error=email_not_sent');
        }
    } else {
        // If the user does not exist, we still go to the success page
        // to prevent attackers from guessing registered email addresses.
        redirect('/forgot_password_success');
    }
}

function handle_reset_password($token, $password, $password_confirm)
{
    if ($password !== $password_confirm) {
        redirect('/reset_password?token=' . $token . '&error=passwords_do_not_match');
    }

    $conn = get_db_connection();
    $stmt = $conn->prepare("SELECT email FROM password_resets WHERE token = ? AND created_at > DATE_SUB(NOW(), INTERVAL 1 HOUR)");
    $stmt->bind_param('s', $token);
    $stmt->execute();
    $reset = $stmt->get_result()->fetch_assoc();

    if ($reset) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $stmt_update = $conn->prepare("UPDATE users SET password = ? WHERE email = ?");
        $stmt_update->bind_param('ss', $hashed_password, $reset['email']);
        $stmt_update->execute();

        $stmt_delete = $conn->prepare("DELETE FROM password_resets WHERE email = ?");
        $stmt_delete->bind_param('s', $reset['email']);
        $stmt_delete->execute();
        
        redirect('/login?success=password_reset');
    } else {
        redirect('/reset_password?token=' . $token . '&error=invalid_or_expired_token');
    }
}

function setup_2fa($user_id, $email)
{
    $ga = new PHPGangsta_GoogleAuthenticator();

    // $ga = new GoogleAuthenticator();
    $secret = $ga->createSecret();

    // DO NOT save to the database yet. Only generate the secret.
    return ['secret' => $secret, 'qrCodeUrl' => $ga->getQRCodeGoogleUrl(APP_NAME, $secret, $email)];
}

function verify_and_activate_2fa($user_id, $secret, $code)
{
    $ga = new PHPGangsta_GoogleAuthenticator();
    if ($ga->verifyCode($secret, $code, 2)) {
        // --- FIX IS HERE ---
        // On successful verification, NOW we save the secret to the database.
        $conn = get_db_connection();
        $stmt = $conn->prepare("UPDATE users SET tfa_secret = ? WHERE id = ?");
        $stmt->bind_param('si', $secret, $user_id);
        $stmt->execute();
        
        $_SESSION['2fa_verified'] = true;
        return true;
    }
    return false;
}

function verify_2fa_login($user_id, $code)
{
    $conn = get_db_connection();
    $stmt = $conn->prepare("SELECT tfa_secret FROM users WHERE id = ?");
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();

    if ($user && $user['tfa_secret']) {
        $ga = new PHPGangsta_GoogleAuthenticator();
        if ($ga->verifyCode($user['tfa_secret'], $code, 2)) {
             $_SESSION['2fa_verified'] = true;
             return true;
        }
    }
    return false;
}
?>
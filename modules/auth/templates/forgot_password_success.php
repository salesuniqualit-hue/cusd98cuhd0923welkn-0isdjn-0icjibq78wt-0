<?php
// modules/auth/templates/forgot_password_success.php
?>

<div class="login-container">
    <div class="login-box">
        <h1>Check Your Email</h1>
        <div class="alert alert-success">
            <p>If an account with that email address exists, we have sent instructions to reset your password.</p>
        </div>
        <div class="text-center mt-3">
            <a href="<?php echo url('/login'); ?>" class="btn btn-primary btn-block">Return to Login</a>
        </div>
    </div>
</div>
<?php
// modules/auth/templates/forgot_password.php
?>

<div class="login-container">
    <div class="login-box">
        <h1>Forgot Password</h1>
        <p>Enter your email address and we will send you a link to reset your password.</p>

        <?php if (isset($_GET['success'])): ?>
            <div class="alert alert-success">An email has been sent with instructions to reset your password.</div>
        <?php elseif (isset($_GET['error'])): ?>
            <div class="alert alert-danger"><?php echo e($_GET['error']); ?></div>
        <?php endif; ?>

        <form action="<?php echo url('/forgot_password'); ?>" method="POST">
            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" class="form-control" required autofocus>
            </div>
            
            <button type="submit" class="btn btn-primary btn-block">Send Password Reset Link</button>
        </form>
        <div class="text-center mt-3">
            <a href="<?php echo url('/login'); ?>">Back to Login</a>
        </div>
    </div>
</div>
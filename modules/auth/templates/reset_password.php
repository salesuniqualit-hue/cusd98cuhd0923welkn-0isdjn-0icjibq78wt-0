<?php
// modules/auth/templates/reset_password.php
?>

<div class="login-container">
    <div class="login-box">
        <h1>Reset Password</h1>
        
        <?php if (isset($_GET['error'])): ?>
            <div class="alert alert-danger"><?php echo e($_GET['error']); ?></div>
        <?php endif; ?>
        
        <form action="<?php echo url('/reset_password'); ?>" method="POST">
            <input type="hidden" name="token" value="<?php echo e($token); ?>">
            <div class="form-group">
                <label for="password">New Password</label>
                <input type="password" id="password" name="password" class="form-control" required minlength="<?php echo MIN_PASSWORD_LENGTH; ?>">
            </div>
            <div class="form-group">
                <label for="password_confirm">Confirm New Password</label>
                <input type="password" id="password_confirm" name="password_confirm" class="form-control" required>
            </div>
            
            <button type="submit" class="btn btn-primary btn-block">Reset Password</button>
        </form>
    </div>
</div>
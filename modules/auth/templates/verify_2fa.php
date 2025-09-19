<?php
// modules/auth/templates/verify_2fa.php
?>

<div class="login-container">
    <div class="login-box">
        <h1>Two-Factor Authentication</h1>
        <p>Enter the code from your authenticator app to continue.</p>
        
        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <?php foreach ($errors as $error): ?>
                    <p><?php echo e($error); ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <form action="<?php echo url('/verify_2fa'); ?>" method="POST">
            <div class="form-group">
                <label for="code">Verification Code</label>
                <input type="text" id="code" name="code" class="form-control" required>
            </div>
            
            <button type="submit" class="btn btn-primary btn-block">Verify</button>
        </form>
    </div>
</div>
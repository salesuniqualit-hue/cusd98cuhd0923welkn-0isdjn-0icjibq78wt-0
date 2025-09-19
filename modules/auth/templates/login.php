<?php
// modules/auth/templates/login.php
?>

<div class="login-container">
    <div class="login-box">
        <h1><?php echo e(APP_NAME); ?> Login</h1>
        
        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <?php foreach ($errors as $error): ?>
                    <p><?php echo e($error); ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        
        <form action="<?php echo url('/login'); ?>" method="POST">
            <div class="text-center">
                <img src="<?php echo url('/assets/uploads/logos/admin-rectangle-logo.png'); ?>" alt="logo" style="max-width: 200px; margin-bottom: 1rem;">
            </div>

            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" class="form-control" required autofocus>
            </div>
            
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" class="form-control" required>
            </div>
            
            <button type="submit" class="btn btn-primary btn-block">Login</button>
        </form>
        <div class="text-center mt-3">
            <a href="<?php echo url('/forgot_password'); ?>">Forgot Password?</a>
        </div>
    </div>
</div>
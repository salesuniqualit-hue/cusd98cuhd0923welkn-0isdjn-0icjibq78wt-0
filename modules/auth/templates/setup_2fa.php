<?php
// modules/auth/templates/setup_2fa.php
?>

<div class="login-container">
    <div class="login-box">
        <h1>Set Up Two-Factor Authentication</h1>
        <p>Scan the QR code with your authenticator app (e.g., Google Authenticator) and enter the code to verify.</p>
        
        <div class="text-center">
            <img src="<?php echo e($qrCodeUrl); ?>" alt="QR Code">
        </div>
        
        <form action="<?php echo url('/setup_2fa'); ?>" method="POST">
            <div class="form-group">
                <label for="code">Verification Code</label>
                <input type="text" id="code" name="code" class="form-control" required>
            </div>
            
            <button type="submit" class="btn btn-primary btn-block">Verify and Enable 2FA</button>
        </form>
    </div>
</div>
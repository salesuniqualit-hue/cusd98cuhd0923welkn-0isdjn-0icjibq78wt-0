<?php
// modules/profile/templates/index.php
$is_dealer = $profile_data['user']['role'] === 'dealer';
?>

<div class="page-header">
    <h1><?php echo e($page_title); ?></h1>
</div>

<?php if (isset($_GET['success'])): ?>
    <div class="alert alert-success">Profile updated successfully.</div>
<?php elseif (isset($_GET['error'])): ?>
    <div class="alert alert-danger"><?php echo e($_GET['error']); ?></div>
<?php endif; ?>

<div class="card">
    <div class="card-body">
        <form action="<?php echo url('/profile/update'); ?>" method="POST" enctype="multipart/form-data">
            <fieldset>
                <legend>Login Information</legend>
                <div class="form-group">
                    <label for="name">Your Name</label>
                    <input type="text" id="name" name="name" class="form-control" value="<?php echo e($profile_data['user']['name']); ?>" required>
                </div>
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" class="form-control" value="<?php echo e($profile_data['user']['email']); ?>" required>
                </div>
                <div class="form-group">
                    <label for="password">New Password</label>
                    <input type="password" id="password" name="password" class="form-control" minlength="<?php echo MIN_PASSWORD_LENGTH; ?>">
                    <small>Leave blank to keep your current password.</small>
                </div>
            </fieldset>

            <?php
                if ($is_dealer && isset($profile_data['dealer'])): 
            ?>
            <hr>
            <fieldset>
                <legend>Company Information</legend>
                <div class="form-group">
                    <label for="logo">Company Logo</label>
                    <?php if (!empty($profile_data['dealer']['logo_path'])): ?>
                        <img src="<?php echo url($profile_data['dealer']['logo_path']); ?>" alt="Current Logo" style="max-height: 50px; display: block; margin-bottom: 10px;">
                    <?php endif; ?>
                    <input type="file" id="logo" name="logo" class="form-control" accept="image/png, image/jpeg, image/gif, image/svg+xml">
                    <small>Upload a new logo to replace the current one. Max size: 2MB. Recommended dimensions: 200x50 pixels.</small>
                </div>
                <div class="form-group">
                    <label for="company_name">Company Name</label>
                    <input type="text" id="company_name" name="company_name" class="form-control" value="<?php echo e($profile_data['dealer']['company_name']); ?>" required>
                </div>
                <div class="form-group">
                    <label for="phone">Company Phone</label>
                    <input type="tel" id="phone" name="phone" class="form-control" value="<?php echo e($profile_data['dealer']['phone']); ?>">
                </div>
                <div class="form-group">
                    <label for="address">Address</label>
                    <textarea id="address" name="address" class="form-control"><?php echo e($profile_data['dealer']['address']); ?></textarea>
                </div>
            </fieldset>
            <?php endif; ?>

            <button type="submit" class="btn btn-primary mt-3">Update Profile</button>
        </form>
    </div>
</div>
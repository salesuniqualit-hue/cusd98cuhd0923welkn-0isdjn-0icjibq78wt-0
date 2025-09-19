<?php
// modules/users/templates/edit.php
// The $form_data array is passed from the router.
?>

<div class="page-header">
    <h1><?php echo e($page_title); ?></h1>
    <a href="<?php echo url('/users'); ?>" class="btn btn-secondary">Back to List</a>
</div>

<div class="card">
    <div class="card-body">
        <form action="<?php echo url('/users/' . $form_data['id'] . '/update'); ?>" method="POST">
            <div class="form-group">
                <label for="name">Full Name</label>
                <input type="text" id="name" name="name" class="form-control" value="<?php echo e($form_data['name']); ?>" required>
            </div>

            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" class="form-control" value="<?php echo e($form_data['email']); ?>" required>
            </div>

            <div class="form-group">
                <label for="password">New Password</label>
                <input type="password" id="password" name="password" class="form-control" minlength="<?php echo MIN_PASSWORD_LENGTH; ?>">
                <small>Leave blank to keep the current password.</small>
            </div>
            
            <div class="form-group form-check">
                <input type="checkbox" id="is_active" name="is_active" class="form-check-input" value="1" <?php echo $form_data['is_active'] ? 'checked' : ''; ?>>
                <label for="is_active" class="form-check-label">User is Active</label>
            </div>

            <button type="submit" class="btn btn-primary">Update User</button>
        </form>
    </div>
</div>
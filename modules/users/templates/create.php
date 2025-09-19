<?php
// modules/users/templates/create.php
?>

<div class="page-header">
    <h1><?php echo e($page_title); ?></h1>
    <a href="<?php echo url('/users'); ?>" class="btn btn-secondary">Back to List</a>
</div>

<div class="card">
    <div class="card-body">
        <form action="<?php echo url('/users/store'); ?>" method="POST">
            <div class="form-group">
                <label for="name">Full Name</label>
                <input type="text" id="name" name="name" class="form-control" required>
            </div>

            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" class="form-control" required>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" class="form-control" required minlength="<?php echo MIN_PASSWORD_LENGTH; ?>">
                <small>Minimum <?php echo MIN_PASSWORD_LENGTH; ?> characters.</small>
            </div>
            
            <div class="form-group form-check">
                <input type="checkbox" id="is_active" name="is_active" class="form-check-input" value="1" checked>
                <label for="is_active" class="form-check-label">User is Active</label>
            </div>

            <button type="submit" class="btn btn-primary">Create User</button>
        </form>
    </div>
</div>
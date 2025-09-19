<?php
// modules/dealers/templates/edit.php
// The $dealer variable is passed from the router.
$dealer_permissions = get_dealer_permissions($dealer['id']);
?>

<div class="page-header">
    <h1><?php echo e($page_title); ?>: <?php echo e($dealer['company_name']); ?></h1>
    <a href="<?php echo url('/dealers'); ?>" class="btn btn-secondary">Back to List</a>
</div>

<div class="card">
    <div class="card-body">
        <form action="<?php echo url('/dealers/' . $dealer['id'] . '/update'); ?>" method="POST">
            <fieldset>
                <legend>Company Information</legend>
                <div class="form-group">
                    <label for="company_name">Company Name</label>
                    <input type="text" id="company_name" name="company_name" class="form-control" value="<?php echo e($dealer['company_name']); ?>" required>
                </div>
                <div class="form-group">
                    <label for="phone">Company Phone</label>
                    <input type="tel" id="phone" name="phone" class="form-control" value="<?php echo e($dealer['phone']); ?>">
                </div>
                <div class="form-group">
                    <label for="address">Address</label>
                    <textarea id="address" name="address" class="form-control"><?php echo e($dealer['address']); ?></textarea>
                </div>
            </fieldset>

            <hr>

            <fieldset>
                <legend>Primary Contact (Dealer Login)</legend>
                <div class="form-group">
                    <label for="contact_person">Contact Person's Name</label>
                    <input type="text" id="contact_person" name="contact_person" class="form-control" value="<?php echo e($dealer['contact_person']); ?>" required>
                </div>
                <div class="form-group">
                    <label for="email">Contact Email</label>
                    <input type="email" id="email" name="email" class="form-control" value="<?php echo e($dealer['email']); ?>" required>
                </div>
                <div class="form-group">
                    <label for="password">New Password</label>
                    <input type="password" id="password" name="password" class="form-control" minlength="<?php echo MIN_PASSWORD_LENGTH; ?>">
                    <small>Leave blank to keep the current password.</small>
                </div>
            </fieldset>
            
            <hr>

            <fieldset>
                <legend>Module Permissions</legend>
                <div class="form-group form-check">
                    <input type="checkbox" id="can_manage_referrers" name="can_manage_referrers" class="form-check-input" value="1" <?php echo in_array('manage_referrers', $dealer_permissions) ? 'checked' : ''; ?>>
                    <label for="can_manage_referrers" class="form-check-label">Can Manage Referrers</label>
                </div>
            </fieldset>
            
            <hr>

            <div class="form-group form-check">
                <input type="checkbox" id="is_active" name="is_active" class="form-check-input" value="1" <?php echo $dealer['is_active'] ? 'checked' : ''; ?>>
                <label for="is_active" class="form-check-label">Dealer is Active</label>
            </div>

            <hr>

            <button type="submit" class="btn btn-primary">Update Dealer</button>
        </form>
    </div>
</div>
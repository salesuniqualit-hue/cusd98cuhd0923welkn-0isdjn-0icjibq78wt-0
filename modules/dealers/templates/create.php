<?php
// modules/dealers/templates/create.php
?>

<div class="page-header">
    <h1><?php echo e($page_title); ?></h1>
    <a href="<?php echo url('/dealers'); ?>" class="btn btn-secondary">Back to List</a>
</div>

<div class="card">
    <div class="card-body">
        <form action="<?php echo url('/dealers/store'); ?>" method="POST">
            <fieldset>
                <legend>Company Information</legend>
                <div class="form-group">
                    <label for="company_name">Company Name</label>
                    <input type="text" id="company_name" name="company_name" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="phone">Company Phone</label>
                    <input type="tel" id="phone" name="phone" class="form-control">
                </div>
                <div class="form-group">
                    <label for="address">Address</label>
                    <textarea id="address" name="address" class="form-control"></textarea>
                </div>
            </fieldset>

            <hr>

            <fieldset>
                <legend>Primary Contact (Dealer Login)</legend>
                <div class="form-group">
                    <label for="contact_person">Contact Person's Name</label>
                    <input type="text" id="contact_person" name="contact_person" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="email">Contact Email</label>
                    <input type="email" id="email" name="email" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" class="form-control" required minlength="<?php echo MIN_PASSWORD_LENGTH; ?>">
                </div>
            </fieldset>
            
            <hr>

            <div class="form-group form-check">
                <input type="checkbox" id="is_active" name="is_active" class="form-check-input" value="1" checked>
                <label for="is_active" class="form-check-label">Dealer is Active</label>
            </div>

            <button type="submit" class="btn btn-primary">Create Dealer</button>
        </form>
    </div>
</div>
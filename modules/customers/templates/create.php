<?php
// modules/customers/templates/create.php
// $dealers list is passed from the router for admins.
$is_admin = current_user()['role'] === 'admin';
?>

<div class="page-header">
    <h1><?php echo e($page_title); ?></h1>
    <a href="<?php echo url('/customers'); ?>" class="btn btn-secondary">Back to List</a>
</div>

<div class="card">
    <div class="card-body">
        <form action="<?php echo url('/customers/store'); ?>" method="POST">
            <div class="form-group">
                <label for="name">Customer Name</label>
                <input type="text" id="name" name="name" class="form-control" required>
            </div>

            <div class="form-group">
                <label for="email">Customer Email</label>
                <input type="email" id="email" name="email" class="form-control">
            </div>
            
            <div class="form-group">
                <label for="phone">Customer Phone</label>
                <input type="tel" id="phone" name="phone" class="form-control">
            </div>

            <?php if ($is_admin): // This field is only for admins ?>
            <div class="form-group">
                <label for="dealer_id">Assign to Dealer</label>
                <select id="dealer_id" name="dealer_id" class="form-control" required>
                    <option value="">-- Select a Dealer --</option>
                    <?php foreach ($dealers as $dealer): ?>
                        <option value="<?php echo e($dealer['id']); ?>"><?php echo e($dealer['company_name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <?php endif; ?>

            <button type="submit" class="btn btn-primary">Create Customer</button>
        </form>
    </div>
</div>
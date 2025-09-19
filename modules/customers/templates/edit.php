<?php
// modules/customers/templates/edit.php
// $customer and $dealers are passed from the router.
$is_admin = current_user()['role'] === 'admin';
?>

<div class="page-header">
    <h1><?php echo e($page_title); ?>: <?php echo e($customer['name']); ?></h1>
    <a href="<?php echo url('/customers'); ?>" class="btn btn-secondary">Back to List</a>
</div>

<div class="card">
    <div class="card-body">
        <form action="<?php echo url('/customers/' . $customer['id'] . '/update'); ?>" method="POST">
            <div class="form-group">
                <label for="name">Customer Name</label>
                <input type="text" id="name" name="name" class="form-control" value="<?php echo e($customer['name']); ?>" required>
            </div>

            <div class="form-group">
                <label for="email">Customer Email</label>
                <input type="email" id="email" name="email" class="form-control" value="<?php echo e($customer['email']); ?>">
            </div>

            <div class="form-group">
                <label for="phone">Customer Phone</label>
                <input type="tel" id="phone" name="phone" class="form-control" value="<?php echo e($customer['phone']); ?>">
            </div>

            <?php if ($is_admin): // This field is only for admins ?>
            <div class="form-group">
                <label for="dealer_id">Assign to Dealer</label>
                <select id="dealer_id" name="dealer_id" class="form-control" required>
                    <option value="">-- Select a Dealer --</option>
                    <?php foreach ($dealers as $dealer): ?>
                        <option value="<?php echo e($dealer['id']); ?>" <?php echo ($dealer['id'] == $customer['dealer_id']) ? 'selected' : ''; ?>>
                            <?php echo e($dealer['company_name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <?php endif; ?>

            <button type="submit" class="btn btn-primary">Update Customer</button>
        </form>
    </div>
</div>
<?php
// modules/orders/templates/create.php
$is_admin = current_user()['role'] === 'admin';
?>

<div class="page-header">
    <h1><?php echo e($page_title); ?></h1>
    <a href="<?php echo url('/orders'); ?>" class="btn btn-secondary">Back to List</a>
</div>

<?php if (isset($_GET['error'])): ?>
    <div class="alert alert-danger"><?php echo e(ucwords(str_replace('_', ' ', $_GET['error']))); ?></div>
<?php endif; ?>

<div class="card">
    <div class="card-body">
        <form action="<?php echo url('/orders/store'); ?>" method="POST">
            
            <?php if ($is_admin): ?>
            <div class="form-group">
                <label for="dealer_id">Select Dealer</label>
                <select id="dealer_id" name="dealer_id" class="form-control" required>
                    <option value="">-- Select a Dealer --</option>
                    <?php foreach ($form_data['dealers'] as $dealer): ?>
                        <option value="<?php echo e($dealer['id']); ?>"><?php echo e($dealer['company_name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <?php endif; ?>

            <div class="form-group">
                <label for="customer_id">Select Customer</label>
                <select id="customer_id" name="customer_id" class="form-control" required <?php echo $is_admin ? 'disabled' : ''; ?>>
                    <option value="">-- <?php echo $is_admin ? 'Select a Dealer First' : 'Select a Customer'; ?> --</option>
                    <?php foreach ($form_data['customers'] as $customer): // For dealers, this is pre-populated ?>
                        <option value="<?php echo e($customer['id']); ?>"><?php echo e($customer['name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="sku_id">Select SKU</label>
                <select id="sku_id" name="sku_id" class="form-control" required>
                    <option value="">-- Select an SKU --</option>
                    <?php foreach ($form_data['skus'] as $sku): ?>
                        <option value="<?php echo e($sku['id']); ?>" data-is-yearly="<?php echo $sku['is_yearly']; ?>" data-is-perpetual="<?php echo $sku['is_perpetual']; ?>"><?php echo e($sku['name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="sku_version_display">Select SKU Version</label>
                <div class="input-group">
                    <input type="text" id="sku_version_display" class="form-control" readonly placeholder="-- Select a SKU First --">
                    <input type="hidden" id="sku_version_id" name="sku_version_id">
                    <div class="input-group-append">
                        <button type="button" id="select_version_btn" class="btn btn-secondary">Select Version</button>
                    </div>
                </div>
            </div>

            <?php if (!empty($form_data['referrers'])): ?>
            <div class="form-group">
                <label for="referrer_id">Referred By (Optional)</label>
                <select id="referrer_id" name="referrer_id" class="form-control">
                    <option value="">-- None --</option>
                    <?php foreach ($form_data['referrers'] as $referrer): ?>
                        <option value="<?php echo e($referrer['id']); ?>"><?php echo e($referrer['name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <?php endif; ?>

            <fieldset class="form-group">
                <legend class="col-form-label">Unit of Measure (UoM)</legend>
                <div id="uom_options">
                    <p class="text-muted">Select an SKU to see available options.</p>
                </div>
            </fieldset>
            
            <div class="form-group">
                <label for="remarks">Remarks / Description</label>
                <textarea id="remarks" name="remarks" class="form-control" rows="3"></textarea>
            </div>

            <button type="submit" class="btn btn-primary">Create Order</button>
        </form>
    </div>
</div>
<?php
// modules/pricing/templates/index.php
$is_dealer_view = isset($selected_dealer);
?>

<div class="page-header">
    <h1><?php echo e($page_title); ?></h1>
    <a href="<?php echo url('/pricing/revise'); ?>" class="btn btn-info">Revise Price Lists</a>
</div>

<?php // Display success or error messages
if (isset($_GET['success'])): ?>
    <div class="alert alert-success"><?php echo e(ucwords(str_replace('_', ' ', $_GET['success']))); ?></div>
<?php elseif (isset($_GET['error'])): ?>
    <div class="alert alert-danger"><?php echo e($_GET['error']); ?></div>
<?php endif; ?>

<div class="card">
    <div class="card-header">
        <ul class="nav nav-tabs card-header-tabs">
            <li class="nav-item">
                <a class="nav-link <?php echo !$is_dealer_view ? 'active' : ''; ?>" href="<?php echo url('/pricing'); ?>">Standard Price List</a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo $is_dealer_view ? 'active' : ''; ?>" href="#">Dealer Price List</a>
            </li>
        </ul>
    </div>
    <div class="card-body">
        
        <div class="dealer-selection-form mb-4">
            <form action="<?php echo url('/pricing'); ?>" method="GET">
                <div class="form-group">
                    <label for="dealer_id">Select a Dealer to View/Edit Their Price List</label>
                    <div class="input-group">
                        <select id="dealer_id" name="dealer_id" class="form-control" onchange="this.form.submit()">
                            <option value="">-- Select Dealer --</option>
                            <?php foreach ($dealers as $dealer): ?>
                                <option value="<?php echo e($dealer['id']); ?>" <?php echo ($is_dealer_view && $dealer['id'] == $selected_dealer['id']) ? 'selected' : ''; ?>>
                                    <?php echo e($dealer['company_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </form>
        </div>

        <hr>

        <form action="<?php echo url($is_dealer_view ? '/pricing/store_dealer' : '/pricing/store_standard'); ?>" method="POST">
            <?php if ($is_dealer_view): ?>
                <input type="hidden" name="dealer_id" value="<?php echo e($selected_dealer['id']); ?>">
            <?php endif; ?>

            <div class="form-group">
                <label for="applicable_date"><strong>New Prices Applicable From Date:</strong></label>
                <input type="date" id="applicable_date" name="applicable_date" class="form-control" value="<?php echo date('Y-m-d'); ?>" required style="max-width: 200px;">
            </div>

            <table class="data-table">
                <thead>
                    <tr>
                        <th>SKU Name</th>
                        <?php if ($is_dealer_view): ?>
                        <th>Standard Price</th>
                        <?php endif; ?>
                        <th>Yearly Price</th>
                        <th>Perpetual Price</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($skus as $sku): ?>
                    <tr>
                        <td data-label="SKU"><?php echo e($sku['name']); ?></td>
                        
                        <?php if ($is_dealer_view): ?>
                        <td data-label="Standard Price">
                            <?php if ($sku['is_yearly']) echo 'Y: ' . e($sku['standard_price_yearly'] ?? 'N/A'); ?>
                            <?php if ($sku['is_perpetual']) echo ' P: ' . e($sku['standard_price_perpetual'] ?? 'N/A'); ?>
                        </td>
                        <?php endif; ?>

                        <td data-label="Yearly Price">
                            <?php if ($sku['is_yearly']): ?>
                                <input type="number" step="0.01" name="prices[<?php echo $sku['id']; ?>][yearly]" class="form-control" placeholder="Current: <?php echo e($is_dealer_view ? ($sku['dealer_price_yearly'] ?? 'N/A') : ($sku['price_yearly'] ?? 'N/A')); ?>">
                            <?php else: echo 'N/A'; endif; ?>
                        </td>
                        <td data-label="Perpetual Price">
                            <?php if ($sku['is_perpetual']): ?>
                                <input type="number" step="0.01" name="prices[<?php echo $sku['id']; ?>][perpetual]" class="form-control" placeholder="Current: <?php echo e($is_dealer_view ? ($sku['dealer_price_perpetual'] ?? 'N/A') : ($sku['price_perpetual'] ?? 'N/A')); ?>">
                            <?php else: echo 'N/A'; endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            
            <button type="submit" class="btn btn-primary mt-3">Update Price List</button>
        </form>
    </div>
</div>
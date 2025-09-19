<?php
// modules/pricing/templates/revise.php
?>

<div class="page-header">
    <h1><?php echo e($page_title); ?></h1>
    <a href="<?php echo url('/pricing'); ?>" class="btn btn-secondary">Back to Pricing</a>
</div>

<?php if (isset($_GET['info'])): ?>
    <div class="alert alert-info"><?php echo e(ucwords(str_replace('_', ' ', $_GET['info']))); ?></div>
<?php endif; ?>


<div class="card">
    <div class="card-body">
        <form action="<?php echo url('/pricing/process_revision'); ?>" method="POST">
            <p>This tool allows you to revise prices for selected dealers and SKUs by a fixed amount or a percentage.</p>
            
            <fieldset>
                <legend>1. Select Dealers</legend>
                <div class="form-group">
                    <label for="dealers">Select Dealers (leave blank for all dealers)</label>
                    <select id="dealers" name="dealers[]" class="form-control" multiple size="5">
                        <?php foreach ($dealers as $dealer): ?>
                            <option value="<?php echo e($dealer['id']); ?>"><?php echo e($dealer['company_name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </fieldset>

            <hr>

            <fieldset>
                <legend>2. Select SKUs</legend>
                 <div class="form-group">
                    <label for="skus">Select SKUs (leave blank for all SKUs)</label>
                    <select id="skus" name="skus[]" class="form-control" multiple size="8">
                        <?php foreach ($skus as $sku): ?>
                            <option value="<?php echo e($sku['id']); ?>"><?php echo e($sku['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </fieldset>

            <hr>

            <fieldset>
                <legend>3. Define Revision</legend>
                <div class="form-group">
                    <label for="revision_type">Revision Type</label>
                    <select id="revision_type" name="revision_type" class="form-control" required>
                        <option value="percentage">Percentage (%)</option>
                        <option value="amount">Fixed Amount</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="revision_value">Value</label>
                    <input type="number" step="0.01" id="revision_value" name="revision_value" class="form-control" required>
                    <small>Enter a positive value to increase, a negative value to decrease.</small>
                </div>
            </fieldset>

            <button type="submit" class="btn btn-warning">Apply Revision</button>
        </form>
    </div>
</div>
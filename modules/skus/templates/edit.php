<?php
// modules/skus/templates/edit.php
?>

<div class="page-header">
    <h1><?php echo e($page_title); ?>: <?php echo e($sku['name']); ?></h1>
    <a href="<?php echo url('/skus'); ?>" class="btn btn-secondary">Back to List</a>
</div>

<div class="card">
    <div class="card-body">
        <form action="<?php echo url('/skus/' . $sku['id'] . '/update'); ?>" method="POST">
            <fieldset>
                <legend>Basic Information</legend>
                <div class="form-group">
                    <label for="name">SKU Name</label>
                    <input type="text" id="name" name="name" class="form-control" value="<?php echo e($sku['name']); ?>" required>
                </div>
                <div class="form-group">
                    <label for="category_id">Category</label>
                    <select id="category_id" name="category_id" class="form-control" required>
                        <option value="">-- Select Category --</option>
                        <?php foreach($categories as $category): ?>
                            <option value="<?php echo e($category['id']); ?>" <?php echo ($category['id'] == $sku['category_id']) ? 'selected' : ''; ?>>
                                <?php echo e($category['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="code">SKU Code</label>
                    <input type="text" id="code" name="code" class="form-control" value="<?php echo e($sku['code']); ?>" required>
                </div>
                <div class="form-group">
                    <label for="guid">GUID</label>
                    <input type="text" id="guid" name="guid" class="form-control" value="<?php echo e($sku['guid']); ?>" required>
                </div>
                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea id="description" name="description" class="form-control"><?php echo e($sku['description']); ?></textarea>
                </div>
            </fieldset>

            <hr>

            <fieldset>
                <legend>Licensing & Periods</legend>
                <div class="form-group form-check">
                    <input type="checkbox" id="is_yearly" name="is_yearly" class="form-check-input" value="1" <?php echo $sku['is_yearly'] ? 'checked' : ''; ?>>
                    <label for="is_yearly" class="form-check-label">Available Yearly</label>
                </div>
                <div class="form-group form-check">
                    <input type="checkbox" id="is_perpetual" name="is_perpetual" class="form-check-input" value="1" <?php echo $sku['is_perpetual'] ? 'checked' : ''; ?>>
                    <label for="is_perpetual" class="form-check-label">Available Perpetual (Lifetime)</label>
                </div>
                <div class="form-group">
                    <label for="subscription_period">Subscription Period (days)</label>
                    <input type="number" id="subscription_period" name="subscription_period" class="form-control" value="<?php echo e($sku['subscription_period']); ?>" required>
                </div>
                <div class="form-group">
                    <label for="trial_period">Trial Period (days)</label>
                    <input type="number" id="trial_period" name="trial_period" class="form-control" value="<?php echo e($sku['trial_period']); ?>" required>
                </div>
                <div class="form-group">
                    <label for="warranty_period">Default Warranty Period (days)</label>
                    <input type="number" id="warranty_period" name="warranty_period" class="form-control" value="<?php echo e($sku['warranty_period']); ?>" required>
                </div>
            </fieldset>
            
            <hr>

            <fieldset>
                <legend>Release Information</legend>
                <div class="form-group">
                    <label for="release_date">Release Date</label>
                    <input type="date" id="release_date" name="release_date" class="form-control" value="<?php echo e($sku['release_date']); ?>">
                </div>
            </fieldset>

            <button type="submit" class="btn btn-primary">Update SKU</button>
        </form>
    </div>
</div>
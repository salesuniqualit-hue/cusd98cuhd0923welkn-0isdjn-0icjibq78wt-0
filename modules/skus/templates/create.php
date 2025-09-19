<?php
// modules/skus/templates/create.php
?>

<div class="page-header">
    <h1><?php echo e($page_title); ?></h1>
    <a href="<?php echo url('/skus'); ?>" class="btn btn-secondary">Back to List</a>
</div>

<div class="card">
    <div class="card-body">
        <form action="<?php echo url('/skus/store'); ?>" method="POST">
            <fieldset>
                <legend>Basic Information</legend>
                <div class="form-group">
                    <label for="name">SKU Name</label>
                    <input type="text" id="name" name="name" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="category_id">Category</label>
                    <select id="category_id" name="category_id" class="form-control" required>
                        <option value="">-- Select Category --</option>
                        <?php foreach($categories as $category): ?>
                            <option value="<?php echo e($category['id']); ?>"><?php echo e($category['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="code">SKU Code</label>
                    <input type="text" id="code" name="code" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="guid">GUID</label>
                    <input type="text" id="guid" name="guid" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea id="description" name="description" class="form-control"></textarea>
                </div>
            </fieldset>

            <hr>

            <fieldset>
                <legend>Licensing & Periods</legend>
                <div class="form-group form-check">
                    <input type="checkbox" id="is_yearly" name="is_yearly" class="form-check-input" value="1">
                    <label for="is_yearly" class="form-check-label">Available Yearly</label>
                </div>
                <div class="form-group form-check">
                    <input type="checkbox" id="is_perpetual" name="is_perpetual" class="form-check-input" value="1">
                    <label for="is_perpetual" class="form-check-label">Available Perpetual (Lifetime)</label>
                </div>
                <div class="form-group">
                    <label for="subscription_period">Subscription Period (days)</label>
                    <input type="number" id="subscription_period" name="subscription_period" class="form-control" value="365" required>
                </div>
                <div class="form-group">
                    <label for="trial_period">Trial Period (days)</label>
                    <input type="number" id="trial_period" name="trial_period" class="form-control" value="30" required>
                </div>
                <div class="form-group">
                    <label for="warranty_period">Default Warranty Period (days)</label>
                    <input type="number" id="warranty_period" name="warranty_period" class="form-control" value="365" required>
                </div>
            </fieldset>

            <hr>

            <fieldset>
                <legend>Release Information</legend>
                <div class="form-group">
                    <label for="release_date">Release Date</label>
                    <input type="date" id="release_date" name="release_date" class="form-control">
                </div>
            </fieldset>

            <button type="submit" class="btn btn-primary">Create SKU</button>
        </form>
    </div>
</div>
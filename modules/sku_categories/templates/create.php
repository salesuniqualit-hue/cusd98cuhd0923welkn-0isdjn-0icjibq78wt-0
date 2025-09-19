<?php
// modules/sku_categories/templates/create.php
// $parent_categories is passed from the router.
?>

<div class="page-header">
    <h1><?php echo e($page_title); ?></h1>
    <a href="<?php echo url('/sku_categories'); ?>" class="btn btn-secondary">Back to List</a>
</div>

<div class="card">
    <div class="card-body">
        <form action="<?php echo url('/sku_categories/store'); ?>" method="POST">
            <div class="form-group">
                <label for="name">Category Name</label>
                <input type="text" id="name" name="name" class="form-control" required>
            </div>

            <div class="form-group">
                <label for="parent_id">Parent Category (Optional)</label>
                <select id="parent_id" name="parent_id" class="form-control">
                    <option value="">-- No Parent --</option>
                    <?php foreach ($parent_categories as $p_cat): ?>
                        <option value="<?php echo e($p_cat['id']); ?>"><?php echo e($p_cat['name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <button type="submit" class="btn btn-primary">Create Category</button>
        </form>
    </div>
</div>
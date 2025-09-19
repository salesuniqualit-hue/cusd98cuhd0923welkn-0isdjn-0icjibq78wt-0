<?php
// modules/sku_categories/templates/edit.php
// $category and $parent_categories are passed from the router.
?>

<div class="page-header">
    <h1><?php echo e($page_title); ?>: <?php echo e($category['name']); ?></h1>
    <a href="<?php echo url('/sku_categories'); ?>" class="btn btn-secondary">Back to List</a>
</div>

<div class="card">
    <div class="card-body">
        <form action="<?php echo url('/sku_categories/' . $category['id'] . '/update'); ?>" method="POST">
            <div class="form-group">
                <label for="name">Category Name</label>
                <input type="text" id="name" name="name" class="form-control" value="<?php echo e($category['name']); ?>" required>
            </div>

            <div class="form-group">
                <label for="parent_id">Parent Category (Optional)</label>
                <select id="parent_id" name="parent_id" class="form-control">
                    <option value="">-- No Parent --</option>
                    <?php foreach ($parent_categories as $p_cat): ?>
                        <?php // A category cannot be its own parent, so we disable that option.
                        if ($p_cat['id'] === $category['id']) continue; ?>
                        <option value="<?php echo e($p_cat['id']); ?>" <?php echo ($p_cat['id'] == $category['parent_id']) ? 'selected' : ''; ?>>
                            <?php echo e($p_cat['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <button type="submit" class="btn btn-primary">Update Category</button>
        </form>
    </div>
</div>
<?php
// modules/sku_categories/templates/index.php
?>

<div class="page-header">
    <h1><?php echo e($page_title); ?></h1>
    <a href="<?php echo url('/sku_categories/create'); ?>" class="btn btn-primary">Add New Category</a>
</div>

<?php if (isset($_GET['success'])): ?>
    <div class="alert alert-success"><?php echo e(ucwords(str_replace('_', ' ', $_GET['success']))); ?></div>
<?php elseif (isset($_GET['error'])): ?>
    <div class="alert alert-danger"><?php echo e(ucwords(str_replace('_', ' ', $_GET['error']))); ?></div>
<?php endif; ?>

<div class="card">
    <div class="card-body">
        <form action="<?php echo url('/sku_categories'); ?>" method="GET" id="inline-filter-form">
            <input type="hidden" name="sort" value="<?php echo e($params['sort'] ?? 'name'); ?>">
            <input type="hidden" name="order" value="<?php echo e($params['order'] ?? 'asc'); ?>">
            <table class="data-table">
                <thead>
                    <tr>
                        <th><?php echo generate_sortable_link('name', 'Category Name', $params, '/sku_categories'); ?></th>
                        <th><?php echo generate_sortable_link('parent_name', 'Parent Category', $params, '/sku_categories'); ?></th>
                        <th>Actions</th>
                    </tr>
                    <tr class="filter-row">
                        <th><input type="text" name="filter_name" class="form-control" value="<?php echo e($params['filter_name'] ?? ''); ?>"></th>
                        <th><input type="text" name="filter_parent" class="form-control" value="<?php echo e($params['filter_parent'] ?? ''); ?>"></th>
                        <th class="filter-actions">
                            <button type="submit" class="btn btn-sm btn-primary" title="Apply Filters">&#128269;</button>
                            <a href="<?php echo url('/sku_categories'); ?>" class="btn btn-sm btn-secondary" title="Clear Filters">&#10006;</a>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($categories)): ?>
                        <?php foreach ($categories as $category): ?>
                        <tr>
                            <td data-label="Name"><?php echo e($category['name']); ?></td>
                            <td data-label="Parent"><?php echo e($category['parent_name'] ?? '—'); ?></td>
                            <td data-label="Actions" class="actions-cell">
                                <a href="<?php echo url('/sku_categories/' . $category['id'] . '/edit'); ?>" class="btn btn-sm btn-warning">Edit</a>
                                <button type="submit" 
                                        class="btn btn-sm btn-danger"
                                        formaction="<?php echo url('/sku_categories/' . $category['id'] . '/delete'); ?>"
                                        formmethod="POST"
                                        onclick="return confirm('Are you sure? Deleting a category is only possible if it has no sub-categories or SKUs.');">
                                    Delete
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="3">No SKU categories found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </form>
        <?php if (isset($pagination)) echo $pagination; ?>
    </div>
</div>
<?php
// modules/skus/templates/index.php
?>

<div class="page-header">
    <h1><?php echo e($page_title); ?></h1>
    <a href="<?php echo url('/skus/create'); ?>" class="btn btn-primary">Add New SKU</a>
</div>

<?php if (isset($_GET['success'])): ?>
    <div class="alert alert-success"><?php echo e(ucwords(str_replace('_', ' ', $_GET['success']))); ?></div>
<?php elseif (isset($_GET['error'])): ?>
    <div class="alert alert-danger"><?php echo e(ucwords(str_replace('_', ' ', $_GET['error']))); ?></div>
<?php endif; ?>

<div class="card">
    <div class="card-body">
        <form action="<?php echo url('/skus'); ?>" method="GET" id="inline-filter-form">
            <input type="hidden" name="sort" value="<?php echo e($params['sort'] ?? 'name'); ?>">
            <input type="hidden" name="order" value="<?php echo e($params['order'] ?? 'asc'); ?>">
            <table class="data-table">
                <thead>
                    <tr>
                        <th><?php echo generate_sortable_link('name', 'Name', $params, '/skus'); ?></th>
                        <th><?php echo generate_sortable_link('code', 'Code', $params, '/skus'); ?></th>
                        <th><?php echo generate_sortable_link('category_name', 'Category', $params, '/skus'); ?></th>
                        <th>Type</th>
                        <th>Actions</th>
                    </tr>
                    <tr class="filter-row">
                        <th><input type="text" name="filter_name" class="form-control" value="<?php echo e($params['filter_name'] ?? ''); ?>"></th>
                        <th><input type="text" name="filter_code" class="form-control" value="<?php echo e($params['filter_code'] ?? ''); ?>"></th>
                        <th><input type="text" name="filter_category" class="form-control" value="<?php echo e($params['filter_category'] ?? ''); ?>"></th>
                        <th></th> <th class="filter-actions">
                            <button type="submit" class="btn btn-sm btn-primary" title="Apply Filters">&#128269;</button>
                            <a href="<?php echo url('/skus'); ?>" class="btn btn-sm btn-secondary" title="Clear Filters">&#10006;</a>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($skus)): ?>
                        <?php foreach ($skus as $sku): ?>
                        <tr>
                            <td data-label="Name"><?php echo e($sku['name']); ?></td>
                            <td data-label="Code"><?php echo e($sku['code']); ?></td>
                            <td data-label="Category"><?php echo e($sku['category_name'] ?? 'N/A'); ?></td>
                            <td data-label="Type">
                                <?php
                                    $types = [];
                                    if ($sku['is_yearly']) $types[] = 'Yearly';
                                    if ($sku['is_perpetual']) $types[] = 'Perpetual';
                                    echo implode(' / ', $types);
                                ?>
                            </td>
                            <td data-label="Actions" class="actions-cell">
                                <a href="<?php echo url('/skus/' . $sku['id'] . '/edit'); ?>" class="btn btn-sm btn-warning">Edit</a>
                                <button type="submit"
                                        class="btn btn-sm btn-danger"
                                        formaction="<?php echo url('/skus/' . $sku['id'] . '/delete'); ?>"
                                        formmethod="POST"
                                        onclick="return confirm('Are you sure? This is only possible if the SKU is not used in any orders.');">
                                    Delete
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5">No SKUs found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </form>
        <?php if (isset($pagination)) echo $pagination; ?>
    </div>
</div>
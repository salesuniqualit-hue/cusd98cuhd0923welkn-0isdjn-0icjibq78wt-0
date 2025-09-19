<?php
// modules/sku_versions/templates/index.php
?>

<div class="page-header">
    <h1><?php echo e($page_title); ?></h1>
    <a href="<?php echo url('/sku_versions/create'); ?>" class="btn btn-primary">Add New Version</a>
</div>

<?php if (isset($_GET['success'])): ?>
    <div class="alert alert-success"><?php echo e(ucwords(str_replace('_', ' ', $_GET['success']))); ?></div>
<?php elseif (isset($_GET['error'])): ?>
    <div class="alert alert-danger"><?php echo e(ucwords(str_replace('_', ' ', $_GET['error']))); ?></div>
<?php endif; ?>

<div class="card">
    <div class="card-body">
        <form action="<?php echo url('/sku_versions'); ?>" method="GET" id="inline-filter-form">
            <input type="hidden" name="sort" value="<?php echo e($params['sort'] ?? 'sku_name'); ?>">
            <input type="hidden" name="order" value="<?php echo e($params['order'] ?? 'asc'); ?>">
            <table class="data-table">
                <thead>
                    <tr>
                        <th><?php echo generate_sortable_link('sku_name', 'SKU Name', $params, '/sku_versions'); ?></th>
                        <th><?php echo generate_sortable_link('version_number', 'Version Number', $params, '/sku_versions'); ?></th>
                        <th><?php echo generate_sortable_link('release_date', 'Release Date', $params, '/sku_versions'); ?></th>
                        <th>Actions</th>
                    </tr>
                    <tr class="filter-row">
                        <th><input type="text" name="filter_sku_name" class="form-control" value="<?php echo e($params['filter_sku_name'] ?? ''); ?>"></th>
                        <th><input type="text" name="filter_version_number" class="form-control" value="<?php echo e($params['filter_version_number'] ?? ''); ?>"></th>
                        <th>
                            <div class="date-range-filter">
                                <input type="date" name="filter_release_from" class="form-control" value="<?php echo e($params['filter_release_from'] ?? ''); ?>" title="From Date">
                                <input type="date" name="filter_release_to" class="form-control" value="<?php echo e($params['filter_release_to'] ?? ''); ?>" title="To Date">
                            </div>
                        </th>
                        <th class="filter-actions">
                            <button type="submit" class="btn btn-sm btn-primary" title="Apply Filters">&#128269;</button>
                            <a href="<?php echo url('/sku_versions'); ?>" class="btn btn-sm btn-secondary" title="Clear Filters">&#10006;</a>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($versions)): ?>
                        <?php foreach ($versions as $version): ?>
                        <tr>
                            <td data-label="SKU Name"><?php echo e($version['sku_name']); ?></td>
                            <td data-label="Version"><?php echo e($version['version_number']); ?></td>
                            <td data-label="Release Date"><?php echo e($version['release_date']); ?></td>
                            <td data-label="Actions" class="actions-cell">
                                <a href="<?php echo url('/sku_versions/' . $version['id'] . '/edit'); ?>" class="btn btn-sm btn-warning">Edit</a>
                                <button type="submit" 
                                        class="btn btn-sm btn-danger"
                                        formaction="<?php echo url('/sku_versions/' . $version['id'] . '/delete'); ?>"
                                        formmethod="POST"
                                        onclick="return confirm('Are you sure? This is only possible if the version is not used in any orders.');">
                                    Delete
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4">No SKU versions found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </form>
        <?php if (isset($pagination)) echo $pagination; ?>
    </div>
</div>

<style>
.date-range-filter {
    display: flex;
    flex-direction: column; /* Stacks the date fields vertically */
    gap: 0.5rem;
}
/* Add this new block to fix the border */
.filter-row th .date-range-filter {
    padding-bottom: 0.5rem;
}
</style>
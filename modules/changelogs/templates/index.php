<?php
// modules/changelogs/templates/index.php
?>

<div class="page-header">
    <h1><?php echo e($page_title); ?></h1>
    <a href="<?php echo url('/changelogs/create'); ?>" class="btn btn-primary">Add New Changelog</a>
</div>

<?php if (isset($_GET['success'])): ?>
    <div class="alert alert-success"><?php echo e(ucwords(str_replace('_', ' ', $_GET['success']))); ?></div>
<?php elseif (isset($_GET['error'])): ?>
    <div class="alert alert-danger"><?php echo e(ucwords(str_replace('_', ' ', $_GET['error']))); ?></div>
<?php endif; ?>

<div class="card">
    <div class="card-body">
        <form action="<?php echo url('/changelogs'); ?>" method="GET" id="inline-filter-form">
            <input type="hidden" name="sort" value="<?php echo e($params['sort'] ?? 'title'); ?>">
            <input type="hidden" name="order" value="<?php echo e($params['order'] ?? 'asc'); ?>">
            <table class="data-table">
                <thead>
                    <tr>
                        <th><?php echo generate_sortable_link('title', 'Title', $params, '/changelogs'); ?></th>
                        <th><?php echo generate_sortable_link('sku_name', 'Associated SKU', $params, '/changelogs'); ?></th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                    <tr class="filter-row">
                        <th><input type="text" name="filter_title" class="form-control" value="<?php echo e($params['filter_title'] ?? ''); ?>"></th>
                        <th><input type="text" name="filter_sku_name" class="form-control" value="<?php echo e($params['filter_sku_name'] ?? ''); ?>"></th>
                        <th>
                            <select name="filter_status" class="form-control">
                                <option value="">All</option>
                                <option value="1" <?php echo (isset($params['filter_status']) && $params['filter_status'] === '1') ? 'selected' : ''; ?>>Assigned</option>
                                <option value="0" <?php echo (isset($params['filter_status']) && $params['filter_status'] === '0') ? 'selected' : ''; ?>>Available</option>
                            </select>
                        </th>
                        <th class="filter-actions">
                            <button type="submit" class="btn btn-sm btn-primary" title="Apply Filters">&#128269;</button>
                            <a href="<?php echo url('/changelogs'); ?>" class="btn btn-sm btn-secondary" title="Clear Filters">&#10006;</a>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($changelogs)): ?>
                        <?php foreach ($changelogs as $changelog): ?>
                        <tr>
                            <td data-label="Title"><?php echo e($changelog['title']); ?></td>
                            <td data-label="SKU"><?php echo e($changelog['sku_name']); ?></td>
                            <td data-label="Status">
                                <?php if ($changelog['version_id']): ?>
                                    <span class="status-badge status-inactive">Assigned</span>
                                <?php else: ?>
                                    <span class="status-badge status-active">Available</span>
                                <?php endif; ?>
                            </td>
                            <td data-label="Actions" class="actions-cell">
                                <a href="<?php echo url('/changelogs/' . $changelog['id'] . '/edit'); ?>" class="btn btn-sm btn-warning">Edit</a>
                                <?php if (!$changelog['version_id']): ?>
                                <button type="submit"
                                        class="btn btn-sm btn-danger"
                                        formaction="<?php echo url('/changelogs/' . $changelog['id'] . '/delete'); ?>"
                                        formmethod="POST"
                                        onclick="return confirm('Are you sure you want to delete this changelog?');">
                                    Delete
                                </button>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4">No changelogs found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </form>
        <?php if (isset($pagination)) echo $pagination; ?>
    </div>
</div>
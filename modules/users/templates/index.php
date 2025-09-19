<?php
// modules/users/templates/index.php
?>

<div class="page-header">
    <h1><?php echo e($page_title); ?></h1>
    <?php if (has_permission('users', 'create')): ?>
        <a href="<?php echo url('/users/create'); ?>" class="btn btn-primary">Add New User</a>
    <?php endif; ?>
</div>

<?php if (isset($_GET['success'])): ?>
    <div class="alert alert-success"><?php echo e(ucwords(str_replace('_', ' ', $_GET['success']))); ?></div>
<?php elseif (isset($_GET['error'])): ?>
    <div class="alert alert-danger"><?php echo e(ucwords(str_replace('_', ' ', $_GET['error']))); ?></div>
<?php endif; ?>

<div class="card">
    <div class="card-body">
        <form action="<?php echo url('/users'); ?>" method="GET" id="inline-filter-form">
            <input type="hidden" name="sort" value="<?php echo e($params['sort'] ?? 'name'); ?>">
            <input type="hidden" name="order" value="<?php echo e($params['order'] ?? 'asc'); ?>">
            <table class="data-table">
                <thead>
                    <tr>
                        <th><?php echo generate_sortable_link('name', 'Name', $params, '/users'); ?></th>
                        <th><?php echo generate_sortable_link('email', 'Email', $params, '/users'); ?></th>
                        <th><?php echo generate_sortable_link('is_active', 'Status', $params, '/users'); ?></th>
                        <th>Actions</th>
                    </tr>
                    <tr class="filter-row">
                        <th><input type="text" name="filter_name" class="form-control" value="<?php echo e($params['filter_name'] ?? ''); ?>"></th>
                        <th><input type="text" name="filter_email" class="form-control" value="<?php echo e($params['filter_email'] ?? ''); ?>"></th>
                        <th>
                            <select name="filter_status" class="form-control">
                                <option value="">All</option>
                                <option value="1" <?php echo (isset($params['filter_status']) && $params['filter_status'] === '1') ? 'selected' : ''; ?>>Active</option>
                                <option value="0" <?php echo (isset($params['filter_status']) && $params['filter_status'] === '0') ? 'selected' : ''; ?>>Inactive</option>
                            </select>
                        </th>
                        <th class="filter-actions">
                            <button type="submit" class="btn btn-sm btn-primary" title="Apply Filters">&#128269;</button>
                            <a href="<?php echo url('/users'); ?>" class="btn btn-sm btn-secondary" title="Clear Filters">&#10006;</a>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($users)): ?>
                        <?php foreach ($users as $user): ?>
                        <tr>
                            <td data-label="Name"><?php echo e($user['name']); ?></td>
                            <td data-label="Email"><?php echo e($user['email']); ?></td>
                            <td data-label="Status">
                                <span class="status-badge <?php echo $user['is_active'] ? 'status-active' : 'status-inactive'; ?>">
                                    <?php echo $user['is_active'] ? 'Active' : 'Inactive'; ?>
                                </span>
                            </td>
                            <td data-label="Actions" class="actions-cell">
                                <?php if (has_permission('users', 'update')): ?>
                                    <a href="<?php echo url('/permissions/user/' . $user['id']); ?>" class="btn btn-sm btn-info">Permissions</a>
                                    <a href="<?php echo url('/users/' . $user['id'] . '/edit'); ?>" class="btn btn-sm btn-warning">Edit</a>
                                <?php endif; ?>
                                <?php if (has_permission('users', 'delete')): ?>
                                    <button type="submit"
                                            class="btn btn-sm btn-danger"
                                            formaction="<?php echo url('/users/' . $user['id'] . '/delete'); ?>"
                                            formmethod="POST"
                                            onclick="return confirm('Are you sure you want to delete this user?');">
                                        Delete
                                    </button>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4">No users found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </form>
        <?php if (isset($pagination)) echo $pagination; ?>
    </div>
</div>
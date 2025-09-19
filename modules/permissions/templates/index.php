<?php
// modules/permissions/templates/index.php
$is_admin = current_user()['role'] === 'admin';
?>

<div class="page-header">
    <h1><?php echo e($page_title); ?></h1>
</div>

<?php if (isset($_GET['success'])): ?>
    <div class="alert alert-success"><?php echo e(ucwords(str_replace('_', ' ', $_GET['success']))); ?></div>
<?php endif; ?>

<div class="card">
    <div class="card-body">
        <p>Select a user from the list below to manage their module permissions.</p>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Name / Company</th>
                    <th>Role / Contact</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($users_to_manage)): ?>
                    <?php foreach ($users_to_manage as $user): ?>
                    <tr>
                        <?php if ($user['role'] === 'dealer'): ?>
                            <td data-label="Company"><?php echo e($user['company_name']); ?></td>
                            <td data-label="Role"><?php echo e($user['name']); ?> (Dealer)</td>
                        <?php elseif ($user['role'] === 'internal_user'): ?>
                            <td data-label="Name"><?php echo e($user['name']); ?></td>
                            <td data-label="Role">Internal User</td>
                        <?php else: // Team member view for dealers ?>
                            <td data-label="Name"><?php echo e($user['name']); ?></td>
                            <td data-label="Email"><?php echo e($user['email']); ?></td>
                        <?php endif; ?>

                        <td data-label="Actions" class="actions-cell">
                            <a href="<?php echo url('/permissions/user/' . $user['id']); ?>" class="btn btn-sm btn-warning">Manage Permissions</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="3">No users available to manage.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php // modules/referrers/templates/index.php ?>
<div class="page-header">
    <h1>Manage Referrers</h1>
    <a href="<?php echo url('/referrers/create'); ?>" class="btn btn-primary">Add New Referrer</a>
</div>
<div class="card"><div class="card-body">
<table class="data-table">
<thead><tr><th>Name</th><th>Contact</th><th>Commission</th><th>Actions</th></tr></thead>
<tbody>
<?php if (empty($referrers)): ?>
    <tr><td colspan="4">No referrers found.</td></tr>
<?php else: foreach ($referrers as $ref): ?>
    <tr>
        <td data-label="Name"><?php echo e($ref['name']); ?></td>
        <td data-label="Contact"><?php echo e($ref['phone'] ?? ''); ?><br><?php echo e($ref['email'] ?? ''); ?></td>
        <td data-label="Commission"><?php echo e($ref['commission_rate']); ?>%</td>
        <td data-label="Actions" class="actions-cell">
            <a href="<?php echo url('/referrers/' . $ref['id'] . '/edit'); ?>" class="btn btn-sm btn-warning">Edit</a>
            <form action="<?php echo url('/referrers/' . $ref['id'] . '/delete'); ?>" method="POST" style="display:inline;" onsubmit="return confirm('Are you sure?');">
                <button type="submit" class="btn btn-sm btn-danger">Delete</button>
            </form>
        </td>
    </tr>
<?php endforeach; endif; ?>
</tbody></table>
</div></div>
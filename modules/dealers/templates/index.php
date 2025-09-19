<?php
// modules/dealers/templates/index.php
?>

<div class="page-header">
    <h1><?php echo e($page_title); ?></h1>
    <a href="<?php echo url('/dealers/create'); ?>" class="btn btn-primary">Add New Dealer</a>
</div>

<?php // Display success or error messages
if (isset($_GET['success'])): ?>
    <div class="alert alert-success"><?php echo e(ucwords(str_replace('_', ' ', $_GET['success']))); ?></div>
<?php elseif (isset($_GET['error'])): ?>
    <div class="alert alert-danger"><?php echo e($_GET['error']); ?></div>
<?php endif; ?>

<div class="card">
    <div class="card-body">
        
        <form action="<?php echo url('/dealers'); ?>" method="GET" id="inline-filter-form">
            <input type="hidden" name="sort" value="<?php echo e($params['sort'] ?? 'company_name'); ?>">
            <input type="hidden" name="order" value="<?php echo e($params['order'] ?? 'asc'); ?>">

            <table class="data-table">
                <thead>
                    <tr>
                        <th><?php echo generate_sortable_link('company_name', 'Company Name', $params, '/dealers'); ?></th>
                        <th><?php echo generate_sortable_link('contact_person', 'Contact Person', $params, '/dealers'); ?></th>
                        <th><?php echo generate_sortable_link('email', 'Email', $params, '/dealers'); ?></th>
                        <th><?php echo generate_sortable_link('phone', 'Phone', $params, '/dealers'); ?></th>
                        <th><?php echo generate_sortable_link('is_active', 'Status', $params, '/dealers'); ?></th>
                        <th style="width: 240px;">Actions</th>
                    </tr>
                    <tr class="filter-row">
                        <th><input type="text" name="filter_company" class="form-control" value="<?php echo e($params['filter_company'] ?? ''); ?>"></th>
                        <th><input type="text" name="filter_contact" class="form-control" value="<?php echo e($params['filter_contact'] ?? ''); ?>"></th>
                        <th><input type="text" name="filter_email" class="form-control" value="<?php echo e($params['filter_email'] ?? ''); ?>"></th>
                        <th><input type="text" name="filter_phone" class="form-control" value="<?php echo e($params['filter_phone'] ?? ''); ?>"></th>
                        <th>
                            <select name="filter_status" class="form-control">
                                <option value="">All</option>
                                <option value="1" <?php echo (isset($params['filter_status']) && $params['filter_status'] === '1') ? 'selected' : ''; ?>>Active</option>
                                <option value="0" <?php echo (isset($params['filter_status']) && $params['filter_status'] === '0') ? 'selected' : ''; ?>>Inactive</option>
                            </select>
                        </th>
                        <th class="filter-actions">
                            <button type="submit" class="btn btn-sm btn-primary" title="Apply Filters">&#128269;</button>
                            <a href="<?php echo url('/dealers'); ?>" class="btn btn-sm btn-secondary" title="Clear Filters">&#10006;</a>
                        </th>
                    </tr>
                </thead>
<tbody>
                    <?php if (!empty($dealers)): ?>
                        <?php foreach ($dealers as $dealer): ?>
                        <tr>
                            <td data-label="Company"><?php echo e($dealer['company_name']); ?></td>
                            <td data-label="Contact"><?php echo e($dealer['contact_person']); ?></td>
                            <td data-label="Email"><?php echo e($dealer['email']); ?></td>
                            <td data-label="Phone"><?php echo e($dealer['phone']); ?></td>
                            <td data-label="Status">
                                <span class="status-badge <?php echo $dealer['is_active'] ? 'status-active' : 'status-inactive'; ?>">
                                    <?php echo $dealer['is_active'] ? 'Active' : 'Inactive'; ?>
                                </span>
                            </td>
                            <td data-label="Actions" class="actions-cell">
                                <a href="<?php echo url('/permissions/user/' . $dealer['user_id']); ?>" class="btn btn-sm btn-info">Permissions</a>
                                <a href="<?php echo url('/dealers/' . $dealer['id'] . '/edit'); ?>" class="btn btn-sm btn-warning">Edit</a>
                                
                                <?php if ($dealer['is_active']): ?>
                                    <button type="submit" class="btn btn-sm btn-danger" formaction="<?php echo url('/dealers/' . $dealer['id'] . '/delete'); ?>" formmethod="POST" onclick="return confirm('Are you sure you want to DEACTIVATE this dealer?');">Deactivate</button>
                                <?php else: ?>
                                    <button type="submit" class="btn btn-sm btn-success" formaction="<?php echo url('/dealers/' . $dealer['id'] . '/activate'); ?>" formmethod="POST" onclick="return confirm('Are you sure you want to ACTIVATE this dealer?');">Activate</button>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6">No dealers found matching your criteria.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </form>

        <?php if (isset($pagination)) echo $pagination; ?>

    </div>
</div>
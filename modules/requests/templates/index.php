<?php
// modules/requests/templates/index.php
?>

<div class="page-header">
    <h1><?php echo e($page_title); ?></h1>
    <?php if (has_permission('requests', 'create')): ?>
        <div class="btn-group">
            <a href="<?php echo url('/requests/trial'); ?>" class="btn btn-primary">New Trial Request</a>
            <a href="<?php echo url('/requests/subscribe'); ?>" class="btn btn-info">New Subscription Request</a>
            <a href="<?php echo url('/requests/renew'); ?>" class="btn btn-success">New Renewal Request</a>
        </div>
    <?php endif; ?>
</div>

<?php if (isset($_GET['success'])): ?>
    <div class="alert alert-success"><?php echo e(ucwords(str_replace('_', ' ', $_GET['success']))); ?></div>
<?php endif; ?>

<div class="card">
    <div class="card-body">
        <form action="<?php echo url('/requests'); ?>" method="GET" id="inline-filter-form">
            <input type="hidden" name="sort" value="<?php echo e($params['sort'] ?? 'created_at'); ?>">
            <input type="hidden" name="order" value="<?php echo e($params['order'] ?? 'desc'); ?>">
            <table class="data-table">
                <thead>
                    <tr>
                        <th><?php echo generate_sortable_link('created_at', 'Date', $params, '/requests'); ?></th>
                        <th><?php echo generate_sortable_link('order_number', 'Order #', $params, '/requests'); ?></th>
                        <th><?php echo generate_sortable_link('customer_name', 'Customer', $params, '/requests'); ?></th>
                        <th><?php echo generate_sortable_link('sku_name', 'SKU', $params, '/requests'); ?></th>
                        <th><?php echo generate_sortable_link('type', 'Type', $params, '/requests'); ?></th>
                        <th><?php echo generate_sortable_link('status', 'Status', $params, '/requests'); ?></th>
                        <th>Actions</th>
                    </tr>
                    <tr class="filter-row">
                        <th>
                            <div class="date-range-filter">
                                <input type="date" name="filter_date_from" class="form-control" value="<?php echo e($params['filter_date_from'] ?? ''); ?>" title="From Date">
                                <input type="date" name="filter_date_to" class="form-control" value="<?php echo e($params['filter_date_to'] ?? ''); ?>" title="To Date">
                            </div>
                        </th>
                        <th><input type="text" name="filter_order_number" class="form-control" value="<?php echo e($params['filter_order_number'] ?? ''); ?>"></th>
                        <th><input type="text" name="filter_customer" class="form-control" value="<?php echo e($params['filter_customer'] ?? ''); ?>"></th>
                        <th><input type="text" name="filter_sku" class="form-control" value="<?php echo e($params['filter_sku'] ?? ''); ?>"></th>
                        <th>
                            <select name="filter_type" class="form-control">
                                <option value="">All</option>
                                <option value="trial" <?php echo (isset($params['filter_type']) && $params['filter_type'] === 'trial') ? 'selected' : ''; ?>>Trial</option>
                                <option value="subscribe" <?php echo (isset($params['filter_type']) && $params['filter_type'] === 'subscribe') ? 'selected' : ''; ?>>Subscribe</option>
                                <option value="renew" <?php echo (isset($params['filter_type']) && $params['filter_type'] === 'renew') ? 'selected' : ''; ?>>Renew</option>
                            </select>
                        </th>
                        <th>
                            <select name="filter_status" class="form-control">
                                <option value="">All</option>
                                <option value="pending" <?php echo (isset($params['filter_status']) && $params['filter_status'] === 'pending') ? 'selected' : ''; ?>>Pending</option>
                                <option value="approved" <?php echo (isset($params['filter_status']) && $params['filter_status'] === 'approved') ? 'selected' : ''; ?>>Approved</option>
                                <option value="rejected" <?php echo (isset($params['filter_status']) && $params['filter_status'] === 'rejected') ? 'selected' : ''; ?>>Rejected</option>
                            </select>
                        </th>
                        <th class="filter-actions">
                            <button type="submit" class="btn btn-sm btn-primary" title="Apply Filters">&#128269;</button>
                            <a href="<?php echo url('/requests'); ?>" class="btn btn-sm btn-secondary" title="Clear Filters">&#10006;</a>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($requests)): ?>
                        <?php foreach ($requests as $request): ?>
                        <tr>
                            <td data-label="Date"><?php echo e(date('Y-m-d', strtotime($request['created_at']))); ?></td>
                            <td data-label="Order #"><?php echo e($request['order_number']); ?></td>
                            <td data-label="Customer"><?php echo e($request['customer_name']); ?></td>
                            <td data-label="SKU"><?php echo e($request['sku_name']); ?></td>
                            <td data-label="Type"><span class="badge"><?php echo e(ucfirst(str_replace('_', ' ', $request['type']))); ?></span></td>
                            <td data-label="Status">
                                <span class="status-badge status-<?php echo e($request['status']); ?>"><?php echo e(ucfirst($request['status'])); ?></span>
                            </td>
                            <td data-label="Actions" class="actions-cell">
                                <?php if ($is_admin_view && $request['status'] === 'pending'): ?>
                                    <a href="<?php echo url('/requests/' . $request['id'] . '/review'); ?>" class="btn btn-sm btn-warning">Review</a>
                                <?php else: ?>
                                    <a href="<?php echo url('/requests/' . $request['id'] . '/view'); ?>" class="btn btn-sm btn-info">View</a>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6">No requests found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </form>
        <?php if (isset($pagination)) echo $pagination; ?>
    </div>
</div>
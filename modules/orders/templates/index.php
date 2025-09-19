<?php
// modules/orders/templates/index.php
?>

<div class="page-header">
    <h1><?php echo e($page_title); ?></h1>
    <?php if (has_permission('orders', 'create')): ?>
        <a href="<?php echo url('/orders/create'); ?>" class="btn btn-primary">Create New Order</a>
    <?php endif; ?>
</div>

<?php if (isset($_GET['success'])): ?>
    <div class="alert alert-success"><?php echo e(ucwords(str_replace('_', ' ', $_GET['success']))); ?></div>
<?php elseif (isset($_GET['error'])): ?>
    <div class="alert alert-danger"><?php echo e(ucwords(str_replace('_', ' ', $_GET['error']))); ?></div>
<?php endif; ?>

<div class="card">
    <div class="card-body">
        <form action="<?php echo url('/orders'); ?>" method="GET" id="inline-filter-form">
            <input type="hidden" name="sort" value="<?php echo e($params['sort'] ?? 'order_date'); ?>">
            <input type="hidden" name="order" value="<?php echo e($params['order'] ?? 'desc'); ?>">
            <table class="data-table">
                <thead>
                    <tr>
                        <th><?php echo generate_sortable_link('order_number', 'Order #', $params, '/orders'); ?></th>
                        <th><?php echo generate_sortable_link('order_date', 'Order Date', $params, '/orders'); ?></th>
                        <?php if ($is_admin_view): ?>
                        <th><?php echo generate_sortable_link('dealer_name', 'Dealer', $params, '/orders'); ?></th>
                        <?php endif; ?>
                        <th><?php echo generate_sortable_link('customer_name', 'Customer', $params, '/orders'); ?></th>
                        <th><?php echo generate_sortable_link('sku_name', 'SKU', $params, '/orders'); ?></th>
                        <th><?php echo generate_sortable_link('rate', 'Rate', $params, '/orders'); ?></th>
                        <th><?php echo generate_sortable_link('status', 'Status', $params, '/orders'); ?></th>
                        <th>Actions</th>
                    </tr>
                    <tr class="filter-row">
                        <th><input type="text" name="filter_order_number" class="form-control" value="<?php echo e($params['filter_order_number'] ?? ''); ?>"></th>
                        <th>
                            <div class="date-range-filter">
                                <input type="date" name="filter_date_from" class="form-control" value="<?php echo e($params['filter_date_from'] ?? ''); ?>" title="From Date">
                                <input type="date" name="filter_date_to" class="form-control" value="<?php echo e($params['filter_date_to'] ?? ''); ?>" title="To Date">
                            </div>
                        </th>
                        <?php if ($is_admin_view): ?>
                        <th><input type="text" name="filter_dealer" class="form-control" value="<?php echo e($params['filter_dealer'] ?? ''); ?>"></th>
                        <?php endif; ?>
                        <th><input type="text" name="filter_customer" class="form-control" value="<?php echo e($params['filter_customer'] ?? ''); ?>"></th>
                        <th><input type="text" name="filter_sku" class="form-control" value="<?php echo e($params['filter_sku'] ?? ''); ?>"></th>
                        <th></th> 
                        <th>
                            <select name="filter_status" class="form-control">
                                <option value="">All</option>
                                <option value="pending" <?php echo (isset($params['filter_status']) && $params['filter_status'] === 'pending') ? 'selected' : ''; ?>>Pending</option>
                                <option value="processed" <?php echo (isset($params['filter_status']) && $params['filter_status'] === 'processed') ? 'selected' : ''; ?>>Processed</option>
                                <option value="cancelled" <?php echo (isset($params['filter_status']) && $params['filter_status'] === 'cancelled') ? 'selected' : ''; ?>>Cancelled</option>
                            </select>
                        </th>
                        <th class="filter-actions">
                            <button type="submit" class="btn btn-sm btn-primary" title="Apply Filters">&#128269;</button>
                            <a href="<?php echo url('/orders'); ?>" class="btn btn-sm btn-secondary" title="Clear Filters">&#10006;</a>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($orders)): ?>
                        <?php foreach ($orders as $order): ?>
                        <tr>
                            <td data-label="Order #"><?php echo e($order['order_number']); ?></td>
                            <td data-label="Date"><?php echo e($order['order_date']); ?></td>
                            <?php if ($is_admin_view): ?>
                            <td data-label="Dealer"><?php echo e($order['dealer_name']); ?></td>
                            <?php endif; ?>
                            <td data-label="Customer"><?php echo e($order['customer_name']); ?></td>
                            <td data-label="SKU"><?php echo e($order['sku_name']); ?></td>
                            <td data-label="Rate"><?php echo e(number_format($order['rate'], 2)); ?></td>
                            <td data-label="Status">
                                <span class="status-badge status-<?php echo e($order['status']); ?>"><?php echo e(ucfirst($order['status'])); ?></span>
                            </td>
                            <td data-label="Actions" class="actions-cell">
                                <a href="<?php echo url('/orders/' . $order['id'] . '/view'); ?>" class="btn btn-sm btn-info">View</a>

                                <?php if ($order['status'] === 'pending' && has_permission('orders', 'update')): ?>
                                    <button type="submit" class="btn btn-sm btn-success"
                                            formaction="<?php echo url('/orders/' . $order['id'] . '/process'); ?>"
                                            formmethod="POST"
                                            onclick="return confirm('Are you sure you want to process this order?');">
                                        Process
                                    </button>
                                <?php endif; ?>

                                <?php if ($order['status'] !== 'cancelled' && has_permission('orders', 'update')): ?>
                                    <button type="submit" class="btn btn-sm btn-danger"
                                            formaction="<?php echo url('/orders/' . $order['id'] . '/cancel'); ?>"
                                            formmethod="POST"
                                            onclick="return confirm('Are you sure you want to cancel this order? This cannot be undone.');">
                                        Cancel
                                    </button>
                                <?php endif; ?>

                                <?php if ($order['status'] === 'cancelled'): ?>
                                    <span>—</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="<?php echo $is_admin_view ? '8' : '7'; ?>">No orders found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </form>
        <?php if (isset($pagination)) echo $pagination; ?>
    </div>
</div>
<?php
// modules/customers/templates/index.php
?>

<div class="page-header">
    <h1><?php echo e($page_title); ?></h1>
    <?php if (has_permission('customers', 'create')): ?>
        <a href="<?php echo url('/customers/create'); ?>" class="btn btn-primary">Add New Customer</a>
    <?php endif; ?>
</div>

<?php if (isset($_GET['success'])): ?>
    <div class="alert alert-success"><?php echo e(ucwords(str_replace('_', ' ', $_GET['success']))); ?></div>
<?php elseif (isset($_GET['error'])): ?>
    <div class="alert alert-danger"><?php echo e(ucwords(str_replace('_', ' ', $_GET['error']))); ?></div>
<?php endif; ?>

<div class="card">
    <div class="card-body">
        <form action="<?php echo url('/customers'); ?>" method="GET" id="inline-filter-form">
            <input type="hidden" name="sort" value="<?php echo e($params['sort'] ?? 'name'); ?>">
            <input type="hidden" name="order" value="<?php echo e($params['order'] ?? 'asc'); ?>">
            <table class="data-table">
                <thead>
                    <tr>
                        <th><?php echo generate_sortable_link('name', 'Name', $params, '/customers'); ?></th>
                        <th><?php echo generate_sortable_link('email', 'Email', $params, '/customers'); ?></th>
                        <th><?php echo generate_sortable_link('phone', 'Phone', $params, '/customers'); ?></th>
                        <?php if ($is_admin_view): ?>
                        <th><?php echo generate_sortable_link('company_name', 'Managed By (Dealer)', $params, '/customers'); ?></th>
                        <?php endif; ?>
                        <th>Actions</th>
                    </tr>
                    <tr class="filter-row">
                        <th><input type="text" name="filter_name" class="form-control" value="<?php echo e($params['filter_name'] ?? ''); ?>"></th>
                        <th><input type="text" name="filter_email" class="form-control" value="<?php echo e($params['filter_email'] ?? ''); ?>"></th>
                        <th><input type="text" name="filter_phone" class="form-control" value="<?php echo e($params['filter_phone'] ?? ''); ?>"></th>
                        <?php if ($is_admin_view): ?>
                        <th><input type="text" name="filter_dealer" class="form-control" value="<?php echo e($params['filter_dealer'] ?? ''); ?>"></th>
                        <?php endif; ?>
                        <th class="filter-actions">
                            <button type="submit" class="btn btn-sm btn-primary" title="Apply Filters">&#128269;</button>
                            <a href="<?php echo url('/customers'); ?>" class="btn btn-sm btn-secondary" title="Clear Filters">&#10006;</a>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($customers)): ?>
                        <?php foreach ($customers as $customer): ?>
                        <tr>
                            <td data-label="Name"><?php echo e($customer['name']); ?></td>
                            <td data-label="Email"><?php echo e($customer['email'] ?? 'N/A'); ?></td>
                            <td data-label="Phone"><?php echo e($customer['phone'] ?? 'N/A'); ?></td>
                            <?php if ($is_admin_view): ?>
                            <td data-label="Dealer"><?php echo e($customer['company_name']); ?></td>
                            <?php endif; ?>
                            <td data-label="Actions" class="actions-cell">
                                <?php
                                    $order_url_params = ['customer_id' => $customer['id']];
                                    if ($is_admin_view) {
                                        $order_url_params['dealer_id'] = $customer['dealer_id'];
                                    }
                                ?>
                                <?php if (has_permission('orders', 'create')): ?>
                                     <a href="<?php echo url('/orders/create?' . http_build_query($order_url_params)); ?>" class="btn btn-sm btn-success">Create Order</a>
                                <?php endif; ?>
                                <a href="<?php echo url('/serials/customer/' . $customer['id']); ?>" class="btn btn-sm btn-info">Serials</a>
                                <?php if (has_permission('customers', 'update')): ?>
                                    <a href="<?php echo url('/customers/' . $customer['id'] . '/edit'); ?>" class="btn btn-sm btn-warning">Edit</a>
                                <?php endif; ?>
                                <?php if (has_permission('customers', 'delete')): ?>
                                    <button type="submit"
                                            class="btn btn-sm btn-danger"
                                            formaction="<?php echo url('/customers/' . $customer['id'] . '/delete'); ?>"
                                            formmethod="POST"
                                            onclick="return confirm('Are you sure?');">
                                        Delete
                                    </button>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="<?php echo $is_admin_view ? '5' : '4'; ?>">No customers found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </form>
        <?php if (isset($pagination)) echo $pagination; ?>
    </div>
</div>
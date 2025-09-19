<div class="page-header">
    <h1><?php echo e($page_title); ?></h1>
    <a href="<?php echo url('/invoices/create'); ?>" class="btn btn-primary">Create New Invoice</a>
</div>

<?php if (isset($_GET['success'])): ?>
    <div class="alert alert-success"><?php echo e(ucwords(str_replace('_', ' ', $_GET['success']))); ?></div>
<?php endif; ?>

<div class="card">
    <div class="card-body">
        <form action="<?php echo url('/invoices'); ?>" method="GET" id="inline-filter-form">
            <input type="hidden" name="sort" value="<?php echo e($params['sort'] ?? 'issue_date'); ?>">
            <input type="hidden" name="order" value="<?php echo e($params['order'] ?? 'desc'); ?>">
            <table class="data-table">
                <thead>
                    <tr>
                        <th><?php echo generate_sortable_link('issue_date', 'Issue Date', $params, '/invoices'); ?></th>
                        <th><?php echo generate_sortable_link('invoice_number', 'Invoice #', $params, '/invoices'); ?></th>
                        <th><?php echo generate_sortable_link('company_name', 'Dealer', $params, '/invoices'); ?></th>
                        <th><?php echo generate_sortable_link('order_id', 'Order #', $params, '/invoices'); ?></th>
                        <th><?php echo generate_sortable_link('amount', 'Amount', $params, '/invoices'); ?></th>
                        <th><?php echo generate_sortable_link('status', 'Status', $params, '/invoices'); ?></th>
                    </tr>
                    <tr class="filter-row">
                        <th>
                            <div class="date-range-filter">
                                <input type="date" name="filter_date_from" class="form-control" value="<?php echo e($params['filter_date_from'] ?? ''); ?>" title="From Date">
                                <input type="date" name="filter_date_to" class="form-control" value="<?php echo e($params['filter_date_to'] ?? ''); ?>" title="To Date">
                            </div>
                        </th>
                        <th><input type="text" name="filter_invoice_number" class="form-control" value="<?php echo e($params['filter_invoice_number'] ?? ''); ?>"></th>
                        <th><input type="text" name="filter_dealer" class="form-control" value="<?php echo e($params['filter_dealer'] ?? ''); ?>"></th>
                        <th></th> <th></th> <th>
                            <select name="filter_status" class="form-control">
                                <option value="">All</option>
                                <option value="unpaid" <?php echo (isset($params['filter_status']) && $params['filter_status'] === 'unpaid') ? 'selected' : ''; ?>>Unpaid</option>
                                <option value="paid" <?php echo (isset($params['filter_status']) && $params['filter_status'] === 'paid') ? 'selected' : ''; ?>>Paid</option>
                                <option value="overdue" <?php echo (isset($params['filter_status']) && $params['filter_status'] === 'overdue') ? 'selected' : ''; ?>>Overdue</option>
                            </select>
                        </th>
                        <th class="filter-actions">
                            <button type="submit" class="btn btn-sm btn-primary" title="Apply Filters">&#128269;</button>
                            <a href="<?php echo url('/invoices'); ?>" class="btn btn-sm btn-secondary" title="Clear Filters">&#10006;</a>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($invoices)): ?>
                        <?php foreach ($invoices as $invoice): ?>
                        <tr>
                            <td data-label="Issue Date"><?php echo e($invoice['issue_date']); ?></td>
                            <td data-label="Invoice #"><?php echo e($invoice['invoice_number']); ?></td>
                            <td data-label="Dealer"><?php echo e($invoice['company_name']); ?></td>
                            <td data-label="Order #"><?php echo e($invoice['order_id']); ?></td>
                            <td data-label="Amount"><?php echo e(number_format($invoice['amount'], 2)); ?></td>
                            <td data-label="Status">
                                <span class="status-badge status-<?php echo e($invoice['status']); ?>"><?php echo e(ucfirst($invoice['status'])); ?></span>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="6">No invoices found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </form>
        <?php if (isset($pagination)) echo $pagination; ?>
    </div>
</div>
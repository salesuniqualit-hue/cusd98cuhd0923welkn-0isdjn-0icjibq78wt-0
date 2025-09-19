<div class="page-header">
    <h1><?php echo e($page_title); ?></h1>
</div>

<div class="card">
    <div class="card-body">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Invoice #</th>
                    <th>Order #</th>
                    <th>Issue Date</th>
                    <th>Due Date</th>
                    <th>Amount</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($invoices)): ?>
                    <?php foreach ($invoices as $invoice): ?>
                    <tr>
                        <td data-label="Invoice #"><?php echo e($invoice['invoice_number']); ?></td>
                        <td data-label="Order #"><?php echo e($invoice['order_id']); ?></td>
                        <td data-label="Issue Date"><?php echo e($invoice['issue_date']); ?></td>
                        <td data-label="Due Date"><?php echo e($invoice['due_date']); ?></td>
                        <td data-label="Amount"><?php echo e(number_format($invoice['amount'], 2)); ?></td>
                        <td data-label="Status">
                            <span class="status-badge status-<?php echo e($invoice['status']); ?>"><?php echo e(ucfirst($invoice['status'])); ?>">
                                <?php echo e(ucfirst($invoice['status'])); ?>
                            </span>
                        </td>
                        <td data-label="Actions">
                            <a href="#" class="btn btn-sm btn-info">View</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7">No invoices found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
        <?php if (isset($pagination)) echo $pagination; ?>
    </div>
</div>
<div class="page-header">
    <h1><?php echo e($page_title); ?></h1>
    <a href="<?php echo url('/invoices'); ?>" class="btn btn-secondary">Back to List</a>
</div>

<div class="card">
    <div class="card-body">
        <form action="<?php echo url('/invoices/store'); ?>" method="POST">
            <div class="form-group">
                <label for="order_id">Select Order</label>
                <select id="order_id" name="order_id" class="form-control" required>
                    <option value="">-- Select an Order to Invoice --</option>
                    <?php foreach($form_data as $order): ?>
                        <option value="<?php echo e($order['id']); ?>">
                            Order #<?php echo e($order['id']); ?> (<?php echo e($order['company_name']); ?> - <?php echo e($order['customer_name']); ?> - <?php echo e($order['sku_name']); ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="invoice_number">Invoice Number</label>
                <input type="text" id="invoice_number" name="invoice_number" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="amount">Amount</label>
                <input type="number" step="0.01" id="amount" name="amount" class="form-control" required>
            </div>
            <div class="row">
                <div class="col-md-6 form-group">
                    <label for="issue_date">Issue Date</label>
                    <input type="date" id="issue_date" name="issue_date" class="form-control" required>
                </div>
                <div class="col-md-6 form-group">
                    <label for="due_date">Due Date</label>
                    <input type="date" id="due_date" name="due_date" class="form-control" required>
                </div>
            </div>
            <div class="form-group">
                <label for="status">Status</label>
                <select id="status" name="status" class="form-control" required>
                    <option value="unpaid">Unpaid</option>
                    <option value="paid">Paid</option>
                    <option value="overdue">Overdue</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Create Invoice</button>
        </form>
    </div>
</div>
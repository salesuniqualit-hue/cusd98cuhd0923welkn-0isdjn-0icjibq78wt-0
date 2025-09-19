<?php
// modules/requests/templates/renew.php
?>

<div class="page-header">
    <h1><?php echo e($page_title); ?></h1>
    <a href="<?php echo url('/requests'); ?>" class="btn btn-secondary">Back to All Requests</a>
</div>
<p>Select an expiring or expired subscription to request a renewal.</p>

<?php if (empty($data)): ?>
    <div class="alert alert-info">There are no subscriptions eligible for renewal at this time.</div>
<?php else: ?>
<div class="card">
    <div class="card-body">
        <form action="<?php echo url('/requests/store_renew'); ?>" method="POST">
            <div class="form-group">
                <label for="order_id">Select Subscription to Renew</label>
                <select id="order_id" name="order_id" class="form-control" required>
                    <option value="">-- Select a Subscription --</option>
                    <?php foreach($data as $sub): ?>
                        <option value="<?php echo e($sub['order_id']); ?>" data-serial-id="<?php echo e($sub['customer_serial_id']); ?>">
                            <?php echo e($sub['sku_name']); ?> for <?php echo e($sub['customer_name']); ?> (Expired: <?php echo e($sub['end_date']); ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <input type="hidden" id="customer_serial_id" name="customer_serial_id" value="">

            <fieldset class="mt-4">
                <legend>Payment Details</legend>
                <div class="form-group">
                    <label for="payment_date">Payment Date</label>
                    <input type="date" id="payment_date" name="payment_date" class="form-control" value="<?php echo date('Y-m-d'); ?>" required>
                </div>
                <div class="form-group">
                    <label for="payment_reference">Payment Reference / UTR No.</label>
                    <input type="text" id="payment_reference" name="payment_reference" class="form-control" required>
                </div>
            </fieldset>
            <div class="form-group">
                <label for="remarks">Remarks</label>
                <textarea id="remarks" name="remarks" class="form-control"></textarea>
            </div>

            <div class="alert alert-info">
                <strong>Note:</strong> Once submitted, your request will be processed. It may take up to 24 hours for the system to reconcile the payment and activate your subscription.
            </div>
            <button type="submit" class="btn btn-primary">Submit Subscription Request</button>
        </form>
    </div>
</div>

<script>
document.getElementById('order_id').addEventListener('change', function() {
    const selectedOption = this.options[this.selectedIndex];
    const serialId = selectedOption.dataset.serialId;
    document.getElementById('customer_serial_id').value = serialId;
});
</script>
<?php endif; ?>
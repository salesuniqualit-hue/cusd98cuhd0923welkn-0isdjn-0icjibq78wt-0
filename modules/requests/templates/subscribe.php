<?php
// modules/requests/templates/subscribe.php
?>

<div class="page-header">
    <h1><?php echo e($page_title); ?></h1>
    <a href="<?php echo url('/orders/' . $data['order_id'] . '/view'); ?>" class="btn btn-secondary">Back to Order Details</a>
</div>
<p>You are requesting a full subscription for the following active trial. Please provide the payment details to proceed.</p>

<div class="card">
    <div class="card-body">
        <form action="<?php echo url('/requests/store_subscribe'); ?>" method="POST">
            <input type="hidden" name="order_id" value="<?php echo e($data['order_id']); ?>">
            <input type="hidden" name="customer_serial_id" value="<?php echo e($_GET['serial_id']); ?>">

            <fieldset disabled>
                <div class="form-group">
                    <label>Order Number</label>
                    <input type="text" class="form-control" value="<?php echo e($data['order_number']); ?>">
                </div>
                <div class="form-group">
                    <label>Customer & Serial</label>
                    <input type="text" class="form-control" value="<?php echo e($data['customer_name'] . ' / ' . $data['serial_number']); ?>">
                </div>
                <div class="form-group">
                    <label>Product</label>
                    <input type="text" class="form-control" value="<?php echo e($data['sku_name']); ?>">
                </div>
            </fieldset>

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
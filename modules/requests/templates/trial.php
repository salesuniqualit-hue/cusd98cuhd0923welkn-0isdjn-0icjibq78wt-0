<?php
// modules/requests/templates/trial.php

// Pre-fill logic for when linking from the order view page or returning from an error
$prefill_order_id = $_GET['order_id'] ?? ($form_data['order_id'] ?? null);
$prefill_serial_id = $_GET['serial_id'] ?? ($form_data['customer_serial_id'] ?? null);

$customer_details = [];
$prefill_order_number = $prefill_order_id; // Default to ID if lookup fails

// Re-fetch details if there was an old order ID from a form submission error or URL parameter
if ($prefill_order_id) {
    // Use get_order_details() to correctly fetch the order_number for display
    $order_details_for_prefill = get_order_details($prefill_order_id, current_user());
    if ($order_details_for_prefill) {
        $prefill_order_number = $order_details_for_prefill['order_number'];
    }
    
    // This function remains correct for getting the customer name and serials for the dropdowns
    $customer_details = get_order_details_for_trial_request($prefill_order_id, current_user());
}

?>

<div class="page-header">
    <h1><?php echo e($page_title); ?></h1>
    <a href="<?php echo url('/requests'); ?>" class="btn btn-secondary">Back to All Requests</a>
</div>
<p>Select an order to request a trial. Only orders that have not yet had a trial or subscription are shown.</p>

<?php if (isset($error)): ?>
    <div class="alert alert-danger"><?php echo e($error); ?></div>
<?php endif; ?>

<div class="card">
    <div class="card-body">
        <form action="<?php echo url('/requests/store_trial'); ?>" method="POST">
            <div class="form-group">
                <label for="order_id">Select Eligible Order</label>
                <select id="order_id" name="order_id" class="form-control" required <?php if($prefill_order_id) echo 'readonly'; ?>>
                    <?php if ($prefill_order_id): ?>
                        <option value="<?php echo e($prefill_order_id); ?>" selected>Order #<?php echo e($prefill_order_number); ?></option>
                    <?php else: ?>
                        <option value="">-- Select an Order --</option>
                        <?php foreach($data as $order): ?>
                            <option value="<?php echo e($order['id']); ?>" <?php echo ($prefill_order_id == $order['id']) ? 'selected' : ''; ?>>
                                Order #<?php echo e($order['order_number']); ?> - <?php echo e($order['sku_name']); ?>
                            </option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label>Customer</label>
                <input type="text" id="customer_name_display" class="form-control" readonly value="<?php echo e($customer_details['customer_name'] ?? '-- Select an order --'); ?>">
            </div>

            <div class="form-group">
                <label for="customer_serial_id">Customer Serial Number</label>
                <select id="customer_serial_id" name="customer_serial_id" class="form-control" required <?php if($prefill_serial_id) echo 'readonly'; ?>>
                    <?php if (!empty($customer_details['serials'])): ?>
                        <option value="">-- Select a Serial --</option>
                        <?php foreach($customer_details['serials'] as $serial): ?>
                            <option value="<?php echo e($serial['id']); ?>" <?php echo ($prefill_serial_id == $serial['id']) ? 'selected' : ''; ?>>
                                <?php echo e($serial['serial_number']); ?>
                            </option>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <option value="">-- Select an order --</option>
                    <?php endif; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="validity_days">Trial Validity (in days)</label>
                <input type="number" id="validity_days" name="validity_days" class="form-control" value="<?php echo e($form_data['validity_days'] ?? '30'); ?>" required>
            </div>

            <div class="form-group">
                <label for="remarks">Remarks</label>
                <textarea id="remarks" name="remarks" class="form-control"><?php echo e($form_data['remarks'] ?? ''); ?></textarea>
            </div>

            <button type="submit" class="btn btn-primary">Submit Trial Request</button>
        </form>
    </div>
</div>
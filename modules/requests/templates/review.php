<?php
// modules/requests/templates/review.php
?>

<div class="page-header">
    <h1><?php echo e($page_title); ?> #<?php echo e($request['id']); ?></h1>
    <a href="<?php echo url('/requests'); ?>" class="btn btn-secondary">Back to All Requests</a>
</div>

<?php if (isset($_SESSION['flash_error'])): ?>
    <div class="alert alert-danger">
        <?php echo e($_SESSION['flash_error']); ?>
    </div>
    <?php unset($_SESSION['flash_error']); // Clear the message after displaying it ?>
<?php endif; ?>

<div class="card">
    <div class="card-header">
        Request Details
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <p><strong>Dealer:</strong> <?php echo e($request['dealer_name']); ?></p>
                <p><strong>Customer:</strong> <?php echo e($request['customer_name']); ?></p>
                <p><strong>Serial Number:</strong> <?php echo e($request['serial_number']); ?></p>
                <p><strong>Requested By:</strong> <?php echo e($request['requested_by']); ?></p>
            </div>
            <div class="col-md-6">
                <p><strong>Request Type:</strong> <span class="badge"><?php echo e(ucfirst(str_replace('_', ' ', $request['type']))); ?></span></p>
                <p><strong>Related Order:</strong> #<?php echo e($request['order_number']); ?></p>
                <p><strong>Related Order ID:</strong> #<?php echo e($request['order_id']); ?></p>
                <p><strong>Product:</strong> <?php echo e($request['sku_name']); ?></p>
                <?php if($request['type'] === 'trial'): ?>
                    <p><strong>Requested Trial Period:</strong> <?php echo e($request['validity_days']); ?> days</p>
                <?php else: ?>
                    <p><strong>Payment Date:</strong> <?php echo e($request['payment_date'] ? date('F j, Y', strtotime($request['payment_date'])) : 'N/A'); ?></p>
                    <p><strong>Payment Reference:</strong> <?php echo e($request['payment_reference'] ?? 'N/A'); ?></p>
                <?php endif; ?>
            </div>
        </div>
        <hr>
        <p><strong>Dealer's Remarks:</strong></p>
        <p class="text-muted"><?php echo e($request['remarks'] ? nl2br(e($request['remarks'])) : 'No remarks provided.'); ?></p>
    </div>
</div>

<div class="card mt-4">
    <div class="card-header">
        Process Request
    </div>
    <div class="card-body">
        <form action="<?php echo url('/requests/' . $request['id'] . '/process'); ?>" method="POST">
            <div class="form-group">
                <label>Action</label>
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="status" id="status_approved" value="approved" checked>
                    <label class="form-check-label" for="status_approved">Approve</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="status" id="status_rejected" value="rejected">
                    <label class="form-check-label" for="status_rejected">Reject</label>
                </div>
            </div>
            <div class="form-group">
                <label for="remarks">Admin Remarks</label>
                <textarea id="remarks" name="remarks" class="form-control"></textarea>
            </div>
            <button type="submit" class="btn btn-primary">Process Request</button>
        </form>
    </div>
</div>
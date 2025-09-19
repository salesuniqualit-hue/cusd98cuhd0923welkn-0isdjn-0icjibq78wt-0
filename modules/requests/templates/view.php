<?php
// modules/requests/templates/view.php
?>

<div class="page-header">
    <h1>View Request Details</h1>
    <a href="<?php echo url('/requests'); ?>" class="btn btn-secondary">Back to All Requests</a>
</div>

<div class="card">
    <div class="card-header">
        <h4>Request #<?php echo e($request['id']); ?></h4>
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
        <h5>Status & Remarks</h5>
        <p><strong>Status:</strong> <span class="status-badge status-<?php echo e($request['status']); ?>"><?php echo e(ucfirst($request['status'])); ?></span></p>
        
        <p><strong>Dealer's Remarks:</strong></p>
        <blockquote class="blockquote">
            <p class="mb-0"><?php echo e($request['remarks'] ? nl2br(e($request['remarks'])) : 'No remarks provided.'); ?></p>
        </blockquote>
        
        <?php if ($request['processed_by']): ?>
        <hr>
        <h5>Processing Information</h5>
        <p><strong>Processed By:</strong> <?php echo e($request['processed_by_name'] ?? 'N/A'); ?></p>
        <p><strong>Processed At:</strong> <?php echo e(date('Y-m-d H:i', strtotime($request['processed_at']))); ?></p>
        
        <p><strong>Admin's Remarks:</strong></p>
        <blockquote class="blockquote">
            <p class="mb-0"><?php echo e($request['admin_remarks'] ? nl2br(e($request['admin_remarks'])) : 'No remarks provided.'); ?></p>
        </blockquote>
        <?php endif; ?>
    </div>
</div>
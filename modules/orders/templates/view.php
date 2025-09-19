<?php
// modules/orders/templates/view.php
?>

<div class="page-header">
    <h1><?php echo e($page_title); ?></h1>
    <a href="<?php echo url('/orders'); ?>" class="btn btn-secondary">Back to Orders List</a>
</div>

<div class="row">

    <div class="col-lg-8">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4>Order Summary</h4>
                <div class="btn-group">
                    <?php
                    // Define conditions for each button
                    $sub_info = $order['subscription_info'];
                    $has_permission = has_permission('requests', 'create');
                    $base_params = 'order_id=' . $order['id'] . '&serial_id=' . $order['customer_serial_id'];

                    $can_request_trial = !$sub_info && $order['status'] === 'processed' && $has_permission;
                    
                    if (!$sub_info)
                    {
                        $can_extend_trial = $sub_info && $sub_info['type'] === 'trial' && $sub_info['is_expired'] && $sub_info['remaining_trial_days'] > 0 && $has_permission;
                    }
                    else
                    {
                        $endDate = new DateTime($sub_info['end_date']); // Create DateTime object for the stored date
                        $today = new DateTime(); // Create DateTime object for today
                        $endDate->setTime(0, 0, 0);
                        $today->setTime(0, 0, 0);
                        $diffDays = $today->diff($endDate)->days; // Calculate the difference
                        $can_extend_trial = $sub_info && $sub_info['type'] === 'trial' && $diffDays <= 2 && $sub_info['remaining_trial_days'] > 0 && $has_permission;
                    }
                    $can_subscribe = $sub_info && $sub_info['type'] === 'trial' && $has_permission;
                    $can_renew = $sub_info && $sub_info['type'] === 'paid' && ($sub_info['is_expired'] || $sub_info['is_expiring_soon']) && $has_permission;
                    ?>
                    <a href="<?php echo $can_request_trial ? url('/requests/trial?' . $base_params) : 'javascript:void(0)'; ?>" 
                       class="btn <?php echo $can_request_trial ? 'btn-primary' : 'btn-secondary disabled'; ?>" 
                       <?php if (!$can_request_trial) echo 'title="A trial has already been requested for this order." disabled'; ?>>
                        Request Trial
                    </a>
                    <a href="<?php echo $can_extend_trial ? url('/requests/trial?' . $base_params) : 'javascript:void(0)'; ?>" 
                       class="btn <?php echo $can_extend_trial ? 'btn-info' : 'btn-secondary disabled'; ?>" 
                       <?php if (!$can_extend_trial) echo 'title="No more trial days available or trial has not expired." disabled'; ?>>
                        Extend Trial
                    </a>
                    <a href="<?php echo $can_subscribe ? url('/requests/subscribe?' . $base_params) : 'javascript:void(0)'; ?>" 
                       class="btn <?php echo $can_subscribe ? 'btn-success' : 'btn-secondary disabled'; ?>" 
                       <?php if (!$can_subscribe) echo 'title="A trial must be created before you can subscribe." disabled'; ?>>
                        Subscribe
                    </a>
                    <a href="<?php echo $can_renew ? url('/requests/renew?' . $base_params) : 'javascript:void(0)'; ?>" 
                       class="btn <?php echo $can_renew ? 'btn-warning' : 'btn-secondary disabled'; ?>" 
                       <?php if (!$can_renew) echo 'title="Subscription is not yet eligible for renewal." disabled'; ?>>
                        Renew
                    </a>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>Order Number:</strong> <?php echo e($order['order_number']); ?></p>
                        <p><strong>Order Date:</strong> <?php echo e(date('F j, Y', strtotime($order['order_date']))); ?></p>
                        <p><strong>Status:</strong> <span class="status-badge status-<?php echo e($order['status']); ?>"><?php echo e(ucfirst($order['status'])); ?></span></p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Dealer:</strong> <?php echo e($order['dealer_name']); ?></p>
                        <p><strong>Customer:</strong> <?php echo e($order['customer_name']); ?></p>
                        <p><strong>Referred By:</strong> <?php echo e($order['referrer_name'] ?? 'N/A'); ?></p>
                    </div>
                </div>
                <hr>
                <h4>Product Details</h4>
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>SKU:</strong> <?php echo e($order['sku_name']); ?></p>
                        <p><strong>Version:</strong> <?php echo e($order['sku_version']); ?></p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Unit of Measure (UoM):</strong> <?php echo e(ucfirst($order['uom'])); ?></p>
                        <p><strong>Rate:</strong> <?php echo e(number_format($order['rate'], 2)); ?></p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h4>Activity History</h4>
            </div>
            <div class="card-body">
                <?php if (empty($order['history'])): ?>
                    <p class="text-muted">No trial or subscription history found.</p>
                <?php else: ?>
                    <ul class="timeline">
                        <?php foreach ($order['history'] as $item): ?>
                            <li>
                                <div class="timeline-badge"></div>
                                <div class="timeline-panel">
                                    <div class="timeline-heading">
                                        <h5 class="timeline-title"><?php echo e(ucfirst($item['type'])); ?> Activated</h5>
                                        <p><small class="text-muted"><?php echo e(date('M j, Y', strtotime($item['start_date']))); ?> to <?php echo e($item['end_date'] ? date('M j, Y', strtotime($item['end_date'])) : 'Perpetual'); ?></small></p>
                                    </div>
                                    <div class="timeline-body-hover">
                                        <hr>
                                        <p>
                                            <strong>Requested By:</strong> <?php echo e($item['requested_by'] ?? 'N/A'); ?><br>
                                            <strong>Dealer Remarks:</strong> <?php echo e($item['remarks'] ?: 'None'); ?><br>
                                            <strong>Admin Remarks:</strong> <?php echo e($item['admin_remarks'] ?: 'None'); ?>
                                        </p>
                                    </div>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        </div>
    </div>

</div>
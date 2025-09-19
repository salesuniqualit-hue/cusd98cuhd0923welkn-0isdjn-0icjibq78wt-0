<?php
// modules/billing/templates/index.php
// The $data variable is passed from the router.
$billing = $data['current_billing'] ?? [];
$payments = $data['payment_methods'] ?? [];
?>

<div class="page-header">
    <h1><?php echo e($page_title); ?></h1>
</div>

<?php if (isset($_GET['success'])): ?>
    <div class="alert alert-success"><?php echo e(ucwords(str_replace('_', ' ', $_GET['success']))); ?></div>
<?php elseif (isset($_GET['error'])): ?>
    <div class="alert alert-danger"><?php echo e($_GET['error']); ?></div>
<?php endif; ?>

<div class="card">
    <div class="card-header">
        <ul class="nav nav-tabs card-header-tabs" id="billing-tabs">
            <li class="nav-item">
                <a class="nav-link active" href="#billing-details" data-toggle="tab">Billing Details</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="#payment-methods" data-toggle="tab">Payment Methods</a>
            </li>
        </ul>
    </div>
    <div class="card-body">
        <div class="tab-content">
            <div class="tab-pane active" id="billing-details">
                <p>This information will be used for all invoices. A new revision is saved each time you update.</p>

                <form action="<?php echo url('/billing/update_info'); ?>" method="POST">
                    <div class="form-group">
                        <label for="billing_name">Billing Name</label>
                        <input type="text" id="billing_name" name="billing_name" class="form-control" value="<?php echo e($billing['billing_name'] ?? ''); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="address">Address</label>
                        <textarea id="address" name="address" class="form-control" required><?php echo e($billing['address'] ?? ''); ?></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="gstin">GSTIN</label>
                                <input type="text" id="gstin" name="gstin" class="form-control" value="<?php echo e($billing['gstin'] ?? ''); ?>">
                            </div>
                            <div class="form-group">
                                <label for="pan">PAN</label>
                                <input type="text" id="pan" name="pan" class="form-control" value="<?php echo e($billing['pan'] ?? ''); ?>">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="state">State</label>
                                <input type="text" id="state" name="state" class="form-control" value="<?php echo e($billing['state'] ?? ''); ?>">
                            </div>
                            <div class="form-group">
                                <label for="cin">CIN</label>
                                <input type="text" id="cin" name="cin" class="form-control" value="<?php echo e($billing['cin'] ?? ''); ?>">
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="email">Billing Email ID</label>
                        <input type="email" id="email" name="email" class="form-control" value="<?php echo e($billing['email'] ?? ''); ?>">
                    </div>
                    <button type="submit" class="btn btn-primary">Save / Revise Billing Details</button>
                </form>
            </div>

            <div class="tab-pane" id="payment-methods">
                <h5>Your Saved Payment Methods</h5>
                <p>Click on a card to edit its details below. Click "Add New" to clear the form.</p>
                
                <div class="payment-card-grid">
                    <?php foreach($payments as $payment): ?>
                    <div class="payment-card <?php echo $payment['is_preferred'] ? 'preferred' : ''; ?>" 
                         data-id="<?php echo e($payment['id']); ?>"
                         data-payment-bank="<?php echo e($payment['payment_bank']); ?>"
                         data-account-no="<?php echo e($payment['account_no']); ?>"
                         data-branch="<?php echo e($payment['branch']); ?>"
                         data-ifsc-code="<?php echo e($payment['ifsc_code']); ?>"
                         data-upi-vpa="<?php echo e($payment['upi_vpa']); ?>"
                         data-preferred="<?php echo e($payment['is_preferred']); ?>">
                        
                        <div class="card-bank-name"><?php echo e($payment['payment_bank'] ?: 'UPI'); ?></div>
                        <div class="card-account-no">
                            <?php if (!empty($payment['account_no'])): ?>
                                A/C: ...<?php echo e(substr($payment['account_no'], -4)); ?>
                            <?php else: ?>
                                <?php echo e($payment['upi_vpa']); ?>
                            <?php endif; ?>
                        </div>
                        <?php if ($payment['is_preferred']): ?>
                            <div class="card-preferred-badge">Preferred</div>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                    

                    <div class="payment-card add-new" id="add-new-payment-card">
                        <div class="card-add-icon">+</div>
                        <div class="card-add-text">Add New</div>
                    </div>
                </div>

                <hr>

                <h5 id="payment-form-title">Add New Payment Method</h5>
                <form action="<?php echo url('/billing/save_payment'); ?>" method="POST" id="payment-method-form">
                    <input type="hidden" name="payment_id" id="payment_id" value="">
                     <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="payment_bank">Bank Name</label>
                                <input type="text" id="payment_bank" name="payment_bank" class="form-control">
                            </div>
                            <div class="form-group">
                                <label for="account_no">Account No.</label>
                                <input type="text" id="account_no" name="account_no" class="form-control">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="branch">Branch</label>
                                <input type="text" id="branch" name="branch" class="form-control">
                            </div>
                            <div class="form-group">
                                <label for="ifsc_code">IFSC Code</label>
                                <input type="text" id="ifsc_code" name="ifsc_code" class="form-control">
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="upi_vpa">UPI VPA Address</label>
                        <input type="text" id="upi_vpa" name="upi_vpa" class="form-control">
                    </div>
                    <div class="form-check">
                        <input type="checkbox" id="is_preferred" name="is_preferred" class="form-check-input" value="1">
                        <label for="is_preferred" class="form-check-label">Mark as preferred payment method</label>
                    </div>
                    
                    <div class="payment-form-actions">
                        <button type="submit" class="btn btn-primary mt-3">Save Payment Method</button>
                        <button type="button" id="delete-payment-btn" class="btn btn-danger mt-3" style="display: none;">Delete</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>


<style>
.payment-method-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.75rem;
    border: 1px solid #ddd;
    border-radius: 0.25rem;
    margin-bottom: 0.5rem;
}
</style>
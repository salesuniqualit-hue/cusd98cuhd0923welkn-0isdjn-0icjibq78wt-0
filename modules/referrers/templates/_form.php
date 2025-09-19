<?php // modules/referrers/templates/_form.php ?>
<div class="form-group">
    <label for="name">Referrer Name</label>
    <input type="text" id="name" name="name" class="form-control" value="<?php echo e($referrer['name'] ?? ''); ?>" required>
</div>
<div class="form-group">
    <label for="phone">Phone Number</label>
    <input type="tel" id="phone" name="phone" class="form-control" value="<?php echo e($referrer['phone'] ?? ''); ?>">
</div>
<div class="form-group">
    <label for="email">Email Address</label>
    <input type="email" id="email" name="email" class="form-control" value="<?php echo e($referrer['email'] ?? ''); ?>">
</div>
<div class="form-group">
    <label for="address">Address</label>
    <textarea id="address" name="address" class="form-control"><?php echo e($referrer['address'] ?? ''); ?></textarea>
</div>
<div class="form-group">
    <label for="commission_rate">Commission Rate (%)</label>
    <input type="number" step="0.01" id="commission_rate" name="commission_rate" class="form-control" value="<?php echo e($referrer['commission_rate'] ?? '0.00'); ?>">
</div>
<div class="form-group">
    <label for="remarks">Remarks</label>
    <textarea id="remarks" name="remarks" class="form-control"><?php echo e($referrer['remarks'] ?? ''); ?></textarea>
</div>
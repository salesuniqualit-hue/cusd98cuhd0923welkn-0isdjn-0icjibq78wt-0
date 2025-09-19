<?php
// modules/tickets/templates/create.php
?>
<div class="page-header">
    <h1><?php echo e($page_title); ?></h1>
    <a href="<?php echo url('/tickets'); ?>" class="btn btn-secondary">Back to List</a>
</div>

<div class="card">
    <div class="card-body">
        <form action="<?php echo url('/tickets/store'); ?>" method="POST">
            <div class="form-group">
                <label for="title">Subject / Title</label>
                <input type="text" id="title" name="title" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="type">Ticket Type</label>
                <select id="type" name="type" class="form-control" required>
                    <option value="general">General Inquiry</option>
                    <option value="bug">Bug Report</option>
                    <option value="feature_request">Feature Request</option>
                </select>
            </div>
            <div class="form-group">
                <label for="sku_id">Related SKU</label>
                <select id="sku_id" name="sku_id" class="form-control" required>
                    <option value="">-- Select SKU --</option>
                    <?php foreach($form_data['skus'] as $sku): ?>
                        <option value="<?php echo e($sku['id']); ?>"><?php echo e($sku['name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="sku_version_display">Select SKU Version</label>
                <div class="input-group">
                    <input type="text" id="sku_version_display" class="form-control" readonly placeholder="-- Select a SKU First --">
                    <input type="hidden" id="sku_version_id" name="sku_version_id">
                    <div class="input-group-append">
                        <button type="button" id="select_version_btn" class="btn btn-secondary">Select Version</button>
                    </div>
                </div>
                <small>Not required for 'Feature Request' tickets.</small>
            </div>

            <div class="form-group">
                <label for="description">Description</label>
                <textarea id="description" name="description" class="form-control" rows="8" required></textarea>
            </div>
            <button type="submit" class="btn btn-primary">Submit Ticket</button>
        </form>
    </div>
</div>
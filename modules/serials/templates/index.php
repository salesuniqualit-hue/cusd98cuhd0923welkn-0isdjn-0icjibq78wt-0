<div class="page-header">
    <h1><?php echo e($page_title); ?></h1>
    <a href="<?php echo url('/customers'); ?>" class="btn btn-secondary">Back to Customers List</a>
</div>

<?php if (isset($_GET['success'])): ?>
    <div class="alert alert-success"><?php echo e(ucwords(str_replace('_', ' ', $_GET['success']))); ?></div>
<?php elseif (isset($_GET['error'])): ?>
    <div class="alert alert-danger"><?php echo e($_GET['error']); ?></div>
<?php endif; ?>

<div class="row">
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">Add New Serial</div>
            <div class="card-body">
                <form action="<?php echo url('/serials/customer/' . $customer_id . '/store'); ?>" method="POST">
                    <div class="form-group">
                        <label for="serial_number">Serial Number</label>
                        <input type="text" id="serial_number" name="serial_number" class="form-control" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Add Serial</button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-md-8">
        <div class="card">
            <div class="card-header">Existing Serials</div>
            <div class="card-body">
                <?php if (empty($serials)): ?>
                    <p>No serial numbers have been added for this customer yet.</p>
                <?php else: ?>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Serial Number</th>
                            <th>Date Added</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($serials as $serial): ?>
                        <tr>
                            <td data-label="Serial Number"><?php echo e($serial['serial_number']); ?></td>
                            <td data-label="Date Added"><?php echo e(date('Y-m-d', strtotime($serial['created_at']))); ?></td>
                            <td data-label="Actions">
                                <form action="<?php echo url('/serials/' . $serial['id'] . '/delete/customer/' . $customer_id); ?>" method="POST" onsubmit="return confirm('Are you sure?');">
                                    <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
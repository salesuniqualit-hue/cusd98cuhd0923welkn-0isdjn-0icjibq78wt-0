<?php
// modules/attendance/templates/holidays.php
?>
<div class="page-header">
    <h1>Manage Holidays</h1>
</div>

<div class="card">
    <div class="card-body">
        <form action="<?php echo url('/attendance/holidays/add'); ?>" method="POST">
            <div class="form-group">
                <label for="holiday_date">Date</label>
                <input type="date" name="holiday_date" id="holiday_date" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="description">Description</label>
                <input type="text" name="description" id="description" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary">Add Holiday</button>
        </form>
    </div>
</div>

<div class="card mt-4">
    <div class="card-body">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Description</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($holidays as $holiday): ?>
                    <tr>
                        <td><?php echo e($holiday['holiday_date']); ?></td>
                        <td><?php echo e($holiday['description']); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
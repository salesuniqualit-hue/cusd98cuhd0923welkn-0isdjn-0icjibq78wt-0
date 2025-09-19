<?php // modules/attendance/templates/report.php 
$is_manager = is_attendance_manager(current_user());
?>
<div class="page-header">
    <h1>Attendance Report</h1>
</div>

<div class="card">
    <div class="card-body">
        <form method="GET" action="<?php echo url('/attendance/report'); ?>">
            <div class="filter-grid">
                
                <?php if ($is_manager): // --- FIX: Only show user selection to managers ?>
                <div class="form-group">
                    <label for="user_ids">Select Users</label>
                    <select name="user_ids[]" id="user_ids" class="form-control" multiple required>
                        <?php foreach ($report_users as $user): ?>
                            <option value="<?php echo e($user['id']); ?>" <?php echo in_array($user['id'], $_GET['user_ids'] ?? []) ? 'selected' : ''; ?>>
                                <?php echo e($user['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <?php endif; ?>

                <div class="form-group">
                    <label for="from_date">From</label>
                    <input type="date" name="from_date" id="from_date" class="form-control" value="<?php echo e($_GET['from_date'] ?? date('Y-m-01')); ?>" required>
                </div>
                <div class="form-group">
                    <label for="to_date">To</label>
                    <input type="date" name="to_date" id="to_date" class="form-control" value="<?php echo e($_GET['to_date'] ?? date('Y-m-t')); ?>" required>
                </div>
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Generate Report</button>
                </div>
            </div>
        </form>
    </div>
</div>

<?php if (!empty($report_data)): ?>
<div class="card mt-4">
    <div class="card-body">
        <table class="data-table">
            <thead>
                <tr>
                    <?php if ($is_manager): ?><th>User</th><?php endif; ?>
                    <th>Date</th>
                    <th>Punch In</th>
                    <th>Punch Out</th>
                    <th>Worked Hours</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($report_data as $entry): ?>
                <tr>
                    <?php if ($is_manager): ?><td data-label="User"><?php echo e($entry['user_name']); ?></td><?php endif; ?>
                    <td data-label="Date"><?php echo e(date('Y-m-d', strtotime($entry['punch_in_time']))); ?></td>
                    <td data-label="Punch In"><?php echo e(date('h:i:s A', strtotime($entry['punch_in_time']))); ?></td>
                    <td data-label="Punch Out"><?php echo e($entry['punch_out_time'] ? date('h:i:s A', strtotime($entry['punch_out_time'])) : 'N/A'); ?></td>
                    <td data-label="Worked Hours"><?php echo e($entry['worked_hours'] ?? 'N/A'); ?></td>
                    <td data-label="Status"><span class="status-badge status-active">P</span></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>
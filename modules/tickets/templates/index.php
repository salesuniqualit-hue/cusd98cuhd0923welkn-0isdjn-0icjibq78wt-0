<?php
// modules/tickets/templates/index.php
?>

<div class="page-header">
    <h1><?php echo e($page_title); ?></h1>
    <?php if (has_permission('tickets', 'create')): ?>
        <a href="<?php echo url('/tickets/create'); ?>" class="btn btn-primary">Create New Ticket</a>
    <?php endif; ?>
</div>

<?php if (isset($_GET['success'])): ?>
    <div class="alert alert-success"><?php echo e(ucwords(str_replace('_', ' ', $_GET['success']))); ?></div>
<?php endif; ?>

<div class="card">
    <div class="card-body">
        <form action="<?php echo url('/tickets'); ?>" method="GET" id="inline-filter-form">
            <input type="hidden" name="sort" value="<?php echo e($params['sort'] ?? 'updated_at'); ?>">
            <input type="hidden" name="order" value="<?php echo e($params['order'] ?? 'desc'); ?>">
            <table class="data-table">
                <thead>
                    <tr>
                        <th><?php echo generate_sortable_link('ticket_number', 'Ticket #', $params, '/tickets'); ?></th>
                        <th><?php echo generate_sortable_link('title', 'Title', $params, '/tickets'); ?></th>
                        <th><?php echo generate_sortable_link('status', 'Status', $params, '/tickets'); ?></th>
                        <th><?php echo generate_sortable_link('updated_at', 'Last Updated', $params, '/tickets'); ?></th>
                        <?php if (in_array(current_user()['role'], ['dealer', 'internal_user'])): ?>
                            <th><?php echo generate_sortable_link('reporter_name', 'Reported By', $params, '/tickets'); ?></th>
                        <?php endif; ?>
                        <th>Actions</th>
                    </tr>
                    <tr class="filter-row">
                        <th><input type="text" name="filter_ticket_number" class="form-control" value="<?php echo e($params['filter_ticket_number'] ?? ''); ?>"></th>
                        <th><input type="text" name="filter_title" class="form-control" value="<?php echo e($params['filter_title'] ?? ''); ?>"></th>
                        <th>
                            <select name="filter_status" class="form-control">
                                <option value="">All</option>
                                <option value="open" <?php echo (isset($params['filter_status']) && $params['filter_status'] === 'open') ? 'selected' : ''; ?>>Open</option>
                                <option value="in_progress" <?php echo (isset($params['filter_status']) && $params['filter_status'] === 'in_progress') ? 'selected' : ''; ?>>In Progress</option>
                                <option value="resolved" <?php echo (isset($params['filter_status']) && $params['filter_status'] === 'resolved') ? 'selected' : ''; ?>>Resolved</option>
                                <option value="closed" <?php echo (isset($params['filter_status']) && $params['filter_status'] === 'closed') ? 'selected' : ''; ?>>Closed</option>
                            </select>
                        </th>
                        <th>
                            <div class="date-range-filter">
                                <input type="date" name="filter_updated_from" class="form-control" value="<?php echo e($params['filter_updated_from'] ?? ''); ?>" title="From Date">
                                <input type="date" name="filter_updated_to" class="form-control" value="<?php echo e($params['filter_updated_to'] ?? ''); ?>" title="To Date">
                            </div>
                        </th>
                        <?php if (in_array(current_user()['role'], ['dealer', 'internal_user'])): ?>
                            <th><input type="text" name="filter_reporter_name" class="form-control" value="<?php echo e($params['filter_reporter_name'] ?? ''); ?>"></th>
                        <?php endif; ?>
                        <th class="filter-actions">
                            <button type="submit" class="btn btn-sm btn-primary" title="Apply Filters">&#128269;</button>
                            <a href="<?php echo url('/tickets'); ?>" class="btn btn-sm btn-secondary" title="Clear Filters">&#10006;</a>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($tickets)): ?>
                        <?php foreach ($tickets as $ticket): ?>
                        <tr>
                            <td data-label="Ticket #"><?php echo e($ticket['ticket_number']); ?></td>
                            <td data-label="Title"><?php echo e($ticket['title']); ?></td>
                            <td data-label="Status">
                                <span class="status-badge status-<?php echo e($ticket['status']); ?>"><?php echo e(ucfirst($ticket['status'])); ?></span>
                            </td>
                            <td data-label="Last Updated"><?php echo e(date('Y-m-d H:i', strtotime($ticket['updated_at']))); ?></td>
                            <?php if (in_array(current_user()['role'], ['dealer', 'internal_user'])): ?>
                                <td data-label="Reported By"><?php echo e($ticket['reporter_name']); ?></td>
                            <?php endif; ?>
                            <td data-label="Actions" class="actions-cell">
                                <a href="<?php echo url('/tickets/' . $ticket['id']); ?>" class="btn btn-sm btn-info">View</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6">No tickets found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </form>
        <?php if (isset($pagination)) echo $pagination; ?>
    </div>
</div>
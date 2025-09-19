<?php
// modules/tickets/templates/view.php
$is_admin = current_user()['role'] === 'admin';
?>
<div class="page-header">
    <h1><?php echo e($page_title); ?></h1>
    <a href="<?php echo url('/tickets'); ?>" class="btn btn-secondary">Back to List</a>
</div>

<div class="ticket-view">
    <div class="card mb-4">
        <div class="card-header">
            <h4><?php echo e($ticket['title']); ?></h4>
        </div>
        <div class="card-body">
            <strong>Product:</strong> <?php echo e($ticket['sku_name']); ?><br>
            <strong>Version:</strong> <?php echo e($ticket['version_number']); ?><br>
            <strong>Status:</strong> <span class="status-badge status-<?php echo e($ticket['status']); ?>"><?php echo e(ucfirst($ticket['status'])); ?></span><br>
            <?php
            $created_at = new DateTime($ticket['created_at']);
            $now = new DateTime();
            $age = $now->diff($created_at);
            ?>
            <strong>Age:</strong> <?php echo $age->format('%a days, %h hours'); ?>
        </div>
    </div>

    <div class="conversation-thread">
        <?php foreach($replies as $reply): ?>
            <div class="reply-card <?php echo ($reply['author_role'] === 'admin' || $reply['author_role'] === 'internal_user') ? 'reply-admin' : 'reply-user'; ?>">
                <div class="reply-header">
                    <strong><?php echo e($reply['author']); ?></strong>
                    <span class="text-muted"><?php echo e(date('Y-m-d H:i', strtotime($reply['created_at']))); ?></span>
                </div>
                <div class="reply-body">
                    <?php echo nl2br(e($reply['reply_text'])); ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <?php if ($ticket['status'] !== 'closed'): ?>
        <div class="card mt-4">
            <div class="card-header">
                <h4>Add a Reply</h4>
            </div>
            <div class="card-body">
                <form action="<?php echo url('/tickets/' . $ticket['id'] . '/reply'); ?>" method="POST">
                    <div class="form-group">
                        <textarea name="reply_text" class="form-control" rows="5" required placeholder="Type your reply here..."></textarea>
                    </div>
                    <?php if ($is_admin): ?>
                    <div class="form-group">
                        <label for="status">Update Status</label>
                        <select name="status" id="status" class="form-control">
                            <option value="open" <?php echo $ticket['status'] === 'open' ? 'selected' : ''; ?>>Open</option>
                            <option value="in_progress" <?php echo $ticket['status'] === 'in_progress' ? 'selected' : ''; ?>>In Progress</option>
                            <option value="resolved" <?php echo $ticket['status'] === 'resolved' ? 'selected' : ''; ?>>Resolved</option>
                            <option value="closed" <?php echo $ticket['status'] === 'closed' ? 'selected' : ''; ?>>Closed</option>
                        </select>
                    </div>
                    <?php else: ?>
                        <?php
                        $user = current_user();
                        $is_reporter = ($user['id'] === $ticket['user_id']);
                        $is_dealer_admin = ($user['role'] === 'dealer' && $user['dealer_id'] === $ticket['dealer_id']);
                        ?>
                        <?php if ($is_reporter || $is_dealer_admin): ?>
                            <div class="form-group">
                                <label for="status">Update Status</label>
                                <select name="status" id="status" class="form-control">
                                    <option value="open" <?php echo $ticket['status'] === 'open' ? 'selected' : ''; ?>>Open</option>
                                    <option value="closed" <?php echo $ticket['status'] === 'closed' ? 'selected' : ''; ?>>Closed</option>
                                </select>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>
                    <button type="submit" class="btn btn-primary">Post Reply</button>
                </form>
            </div>
        </div>
    <?php endif; ?>
</div>
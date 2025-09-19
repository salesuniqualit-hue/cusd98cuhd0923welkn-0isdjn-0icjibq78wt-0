<?php
// modules/changelogs/templates/create.php
?>

<div class="page-header">
    <h1><?php echo e($page_title); ?></h1>
    <a href="<?php echo url('/changelogs'); ?>" class="btn btn-secondary">Back to List</a>
</div>

<div class="card">
    <div class="card-body">
        <form action="<?php echo url('/changelogs/store'); ?>" method="POST">
            <div class="form-group">
                <label for="sku_id">Associated SKU</label>
                <select id="sku_id" name="sku_id" class="form-control" required>
                    <option value="">-- Select SKU --</option>
                    <?php foreach ($skus as $sku): ?>
                        <option value="<?php echo e($sku['id']); ?>"><?php echo e($sku['name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="title">Title</label>
                <input type="text" id="title" name="title" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="changes">Changes (Paragraph)</label>
                <textarea id="changes" name="changes" class="form-control" rows="10"></textarea>
            </div>
            <button type="submit" class="btn btn-primary">Create Changelog</button>
        </form>
    </div>
</div>
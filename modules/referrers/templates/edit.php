<?php // modules/referrers/templates/edit.php ?>
<div class="page-header"><h1>Edit Referrer</h1></div>
<div class="card"><div class="card-body">
<form action="<?php echo url('/referrers/' . $referrer['id'] . '/update'); ?>" method="POST">
    <?php include '_form.php'; ?>
    <button type="submit" class="btn btn-primary">Update Referrer</button>
    <a href="<?php echo url('/referrers'); ?>" class="btn btn-secondary">Cancel</a>
</form>
</div></div>
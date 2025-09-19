<?php // modules/referrers/templates/create.php ?>
<div class="page-header"><h1>Add New Referrer</h1></div>
<div class="card"><div class="card-body">
<form action="<?php echo url('/referrers/store'); ?>" method="POST">
    <?php include '_form.php'; ?>
    <button type="submit" class="btn btn-primary">Create Referrer</button>
    <a href="<?php echo url('/referrers'); ?>" class="btn btn-secondary">Cancel</a>
</form>
</div></div>
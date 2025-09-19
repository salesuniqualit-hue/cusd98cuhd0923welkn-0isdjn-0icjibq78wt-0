<?php
// modules/reports/templates/view.php
// Variables like $page_title and $report_html_content are passed from the router.
?>

<div class="page-header">
    <h1><?php echo e($page_title); ?></h1>
    <a href="<?php echo url('/reports'); ?>" class="btn btn-secondary">Back to Reports List</a>
</div>

<div class="card">
    <div class="card-body">
        <?php echo $report_html_content; ?>
    </div>
</div>
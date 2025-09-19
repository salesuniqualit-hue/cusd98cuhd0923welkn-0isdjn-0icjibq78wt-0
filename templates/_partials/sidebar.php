<?php
// templates/_partials/sidebar.php
$dealer_logo = $_SESSION['dealer_logo_path'] ?? null;
$dealer_company_name = $_SESSION['dealer_company_name'] ?? null;
?>
<aside class="sidebar" id="sidebar">
    <div class="sidebar-header">
        <a href="<?php echo url('/'); ?>" class="sidebar-brand-container">
            <?php if ($dealer_logo): ?>
                <img src="<?php echo url($dealer_logo); ?>" alt="Dealer Logo" class="sidebar-logo">
            <?php endif; ?>
            <span class="sidebar-brand"><?php echo e(APP_NAME); ?></span>
        </a>
        <?php if ($dealer_company_name): ?>
            <div class="sidebar-company-name"><?php echo e($dealer_company_name); ?></div>
        <?php endif; ?>
    </div>
    <nav class="sidebar-nav">
        <ul>
            <li><a href="<?php echo url('/'); ?>">Dashboard</a></li>

            <?php if (current_user()['role'] === 'admin'): ?>
                <li><a href="<?php echo url('/dealers'); ?>">Dealers</a></li>
                <li><a href="<?php echo url('/users'); ?>">Internal Users</a></li>
                <li><a href="<?php echo url('/referrers'); ?>">Referrers</a></li>
                <li><a href="<?php echo url('/sku_categories'); ?>">SKU Categories</a></li>
                <li><a href="<?php echo url('/skus'); ?>">SKUs</a></li>
                <li><a href="<?php echo url('/sku_versions'); ?>">SKU Versions</a></li>
                <li><a href="<?php echo url('/changelogs'); ?>">Change Logs</a></li>
                <li><a href="<?php echo url('/pricing'); ?>">Price Lists</a></li>
            <?php endif; ?>

            <?php if (current_user()['role'] !== 'admin'): ?>
                <li><a href="<?php echo url('/referrers'); ?>">Referrers</a></li>
            <?php endif; ?>
            <li><a href="<?php echo url('/customers'); ?>">Customers</a></li>
            <li><a href="<?php echo url('/orders'); ?>">Orders</a></li>
            <li><a href="<?php echo url('/requests'); ?>">Requests</a></li>

            <?php if (current_user()['role'] === 'dealer'): ?>
                <li><a href="<?php echo url('/invoices'); ?>">Invoices</a></li>                
            <?php endif; ?>

            <?php if (current_user()['role'] === 'admin'): ?>
                <li><a href="<?php echo url('/invoices'); ?>">Invoices</a></li>
            <?php endif; ?>

            <li><a href="<?php echo url('/tickets'); ?>">Tickets</a></li>
            <li><a href="<?php echo url('/attendance'); ?>">Attendance</a></li> <li>
            <li><a href="<?php echo url('/reports'); ?>">Reports</a></li>

            <?php if (current_user()['role'] === 'dealer'): ?>
                <li><a href="<?php echo url('/users'); ?>">Team Members</a></li>
                <li><a href="<?php echo url('/invoices'); ?>">Invoices</a></li>
            <?php endif; ?>
            
        </ul>
    </nav>
</aside>
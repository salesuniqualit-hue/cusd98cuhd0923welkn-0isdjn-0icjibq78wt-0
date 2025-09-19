<?php
// templates/_partials/header.php
$user = current_user();
$user_name = $_SESSION['user_name'] ?? 'User';
$dealer_logo = $_SESSION['dealer_logo_path'] ?? null;
?>
<header class="header">
    <div class="header-left">
        <button class="sidebar-toggle" id="sidebarToggle" aria-label="Toggle navigation">
            &#9776; 
        </button>
        <?php if ($dealer_logo): ?>
            <img src="<?php echo url($dealer_logo); ?>" alt="Dealer Logo" class="header-logo-mobile">
        <?php endif; ?>
    </div>
    <div class="header-right">
        <div class="user-menu">
            <div class="user-menu-toggle">
                <span>Welcome, <?php echo e($user_name); ?></span> &#9662;
            </div>
            <div class="user-menu-dropdown">
                <a href="<?php echo url('/profile'); ?>">Profile</a>
                <?php if ($user && $user['role'] === 'dealer'): ?>
                    <a href="<?php echo url('/billing'); ?>">Billing Information</a>
                <?php endif; ?>
                <a href="<?php echo url('/logout'); ?>">Logout</a>
            </div>
        </div>
    </div>
</header>
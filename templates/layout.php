<?php
// templates/layout.php

// This file is the main page structure.

// Ensure the content file to be included is set.
if (!isset($content_view)) {
    die("Error: Content view not specified for the layout.");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <title><?php echo e(isset($page_title) ? $page_title . ' | ' : '') . e(APP_NAME); ?></title>
    
    <link rel="stylesheet" href="<?php echo url('/assets/css/style.css'); ?>">
    
    <script>
        const BASE_URL = '<?php echo rtrim(BASE_PATH, '/'); ?>';
    </script>
    </head>
<body>

<div class="app-container">
    
    <?php // Include the sidebar navigation.
    require_once __DIR__ . '/_partials/sidebar.php'; ?>

    <main class="main-content">
        
        <?php // Include the header.
        require_once __DIR__ . '/_partials/header.php'; ?>

        <div class="content-wrapper">
            <?php require_once $content_view; ?>
        </div>
    </main>
</div>

<div id="sku-version-modal" class="modal">
    <div class="modal-content">
        <span class="close">&times;</span>
        <h2>Select SKU Version</h2>
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Version</th>
                        <th>Release Date</th>
                        <th>Description</th>
                        <th>Tally Compatibility</th>
                        <th>Select</th>
                    </tr>
                </thead>
                <tbody id="sku-version-list">
                    </tbody>
            </table>
        </div>
    </div>
</div>

<script src="<?php echo url('assets/js/Chart.min.js'); ?>"></script>
<script src="<?php echo url('assets/js/main.js'); ?>"></script>
</body>
</html>
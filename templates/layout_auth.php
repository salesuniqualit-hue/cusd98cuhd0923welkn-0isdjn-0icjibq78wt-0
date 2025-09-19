<?php
// templates/layout_auth.php
// A simple layout for pages outside the main application (like login).

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
</head>
<body>

    <?php require_once $content_view; ?>

</body>
</html>
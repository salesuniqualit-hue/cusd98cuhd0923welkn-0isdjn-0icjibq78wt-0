<?php
// modules/dashboard/routes.php

// The dashboard is a protected area, so we require the user to be logged in.
require_login();

// Load the logic file for this module.
require_once __DIR__ . '/actions.php';

$route = get_route();

// This router handles the root URL ('/').
if ($route === '/') {
    // Get the current user's information.
    $user = current_user();
    
    // Fetch the data needed for the dashboard display.
    $dashboard_data = get_dashboard_data($user);
    
    // Prepare variables for the view.
    $page_title = 'Dashboard';
    $content_view = __DIR__ . '/templates/index.php';
    
    // Load the main layout, which will include the dashboard content.
    require_once __DIR__ . '/../../templates/layout.php';
}

?>
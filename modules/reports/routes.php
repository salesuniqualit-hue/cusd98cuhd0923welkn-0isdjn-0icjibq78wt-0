<?php
// modules/reports/routes.php

require_login();
require_once __DIR__ . '/actions.php';

$route = get_route();
$current_user = current_user();

// The main reports page, which shows a list of available reports.
if ($route === '/reports') {
    // Permission to view the main reports page itself can be handled here if needed.
    // For now, if a user can log in, they can see the list. Access to individual reports is handled later.
    $page_title = 'Available Reports';
    $reports = get_available_reports($current_user); // Fetches reports the user is allowed to see.
    $content_view = __DIR__ . '/templates/index.php';
    require_once __DIR__ . '/../../templates/layout.php';
}

// Handles viewing a specific report, e.g., /reports/subscription_summary
elseif (preg_match('/^\/reports\/([a-zA-Z0-9_]+)$/', $route, $matches)) {
    $report_slug = $matches[1];
    
    // The action function will contain the permission check and data generation.
    $report_data = generate_report($report_slug, $current_user, $_GET); // Pass any filters from URL
    
    if (!$report_data) {
        http_response_code(403);
        exit('Access Denied or Report not found.');
    }

    $page_title = e($report_data['title']);
    $report_html_content = $report_data['html'];
    
    // Use a generic wrapper template to display the report.
    $content_view = __DIR__ . '/templates/view.php';
    require_once __DIR__ . '/../../templates/layout.php';
}
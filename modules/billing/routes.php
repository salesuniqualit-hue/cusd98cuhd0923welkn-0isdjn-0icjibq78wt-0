<?php
// modules/billing/routes.php

require_login();
require_once __DIR__ . '/actions.php';

$current_user = current_user();

// Security: Only allow the primary dealer account to access this page.
if ($current_user['role'] !== 'dealer') {
    http_response_code(403);
    exit('Access Denied.');
}

$route = get_route();
$dealer_id = $current_user['dealer_id'];

// --- POST/ACTION ROUTES ---

if ($route === '/billing/update_info' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    handle_save_billing_info($_POST, $dealer_id);
    redirect('/billing?success=billing_info_updated');
}
elseif ($route === '/billing/save_payment' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    handle_save_payment_method($_POST, $dealer_id);
}
elseif (preg_match('/^\/billing\/delete_payment\/(\d+)$/', $route, $matches) && $_SERVER['REQUEST_METHOD'] === 'POST') {
    handle_delete_payment_method((int)$matches[1], $dealer_id);
}


// --- GET/PAGE ROUTE ---

elseif ($route === '/billing') {
    $page_title = 'Billing Information';
    $data = get_billing_page_data($dealer_id);
    $content_view = __DIR__ . '/templates/index.php';
    require_once __DIR__ . '/../../templates/layout.php';
}
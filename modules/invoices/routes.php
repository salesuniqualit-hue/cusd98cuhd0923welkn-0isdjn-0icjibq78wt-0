<?php
// modules/invoices/routes.php

require_login();
require_once __DIR__ . '/actions.php';

$current_user = current_user();
$route = get_route();

// --- POST/ACTION ROUTES ---
if ($route === '/invoices/store' && $_SERVER['REQUEST_METHOD'] === 'POST' && $current_user['role'] === 'admin') {
    handle_create_invoice($_POST);
}

// --- GET/PAGE ROUTES ---
elseif ($route === '/invoices') {
    if ($current_user['role'] === 'admin') {
        $page_title = 'Manage All Invoices';
        $data = get_all_invoices(); // New function for admins
        $invoices = $data['invoices'];
        $pagination = $data['pagination'];
        $params = $data['params'];
        // Admins will use a different view from dealers
        $content_view = __DIR__ . '/templates/index_admin.php';
        require_once __DIR__ . '/../../templates/layout.php';

    } elseif ($current_user['role'] === 'dealer') {
        $page_title = 'My Invoices';
        $data = get_dealer_invoices($current_user['dealer_id']);
        $invoices = $data['invoices'];
        $pagination = $data['pagination'];
        // Dealers use the original view
        $content_view = __DIR__ . '/templates/index.php';
        require_once __DIR__ . '/../../templates/layout.php';

    } else {
        http_response_code(403);
        exit('Access Denied.');
    }
}
elseif ($route === '/invoices/create' && $current_user['role'] === 'admin') {
    $page_title = 'Create New Invoice';
    $form_data = get_invoice_form_data(); // New function for form
    $content_view = __DIR__ . '/templates/create.php';
    require_once __DIR__ . '/../../templates/layout.php';
}
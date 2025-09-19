<?php
// modules/serials/routes.php

require_login();
require_once __DIR__ . '/actions.php';

$current_user = current_user();
// For now, we assume any logged-in user with customer access can manage serials.
// You could add more specific permission checks here if needed.

$route = get_route();

// --- POST/ACTION ROUTES ---

if (preg_match('/^\/serials\/customer\/(\d+)\/store$/', $route, $matches) && $_SERVER['REQUEST_METHOD'] === 'POST') {
    handle_add_serial($_POST, (int)$matches[1]);
}
elseif (preg_match('/^\/serials\/(\d+)\/delete\/customer\/(\d+)$/', $route, $matches) && $_SERVER['REQUEST_METHOD'] === 'POST') {
    handle_delete_serial((int)$matches[1], (int)$matches[2]);
}


// --- GET/PAGE ROUTE ---

elseif (preg_match('/^\/serials\/customer\/(\d+)$/', $route, $matches)) {
    $customer_id = (int)$matches[1];
    $data = get_customer_serials_data($customer_id);

    if (!$data) {
        http_response_code(404);
        exit('Customer not found.');
    }

    $page_title = 'Manage Serials for ' . e($data['customer']['name']);
    $customer = $data['customer'];
    $serials = $data['serials'];
    $content_view = __DIR__ . '/templates/index.php';
    require_once __DIR__ . '/../../templates/layout.php';
}
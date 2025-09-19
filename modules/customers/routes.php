<?php
// modules/customers/routes.php

require_login();
require_once __DIR__ . '/../dealers/actions.php'; // Add this line
require_once __DIR__ . '/actions.php';

$route = get_route();
$current_user = current_user();

// --- POST/ACTION ROUTES ---

if ($route === '/customers/store' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!has_permission('customers', 'create')) redirect('/customers?error=permission_denied');
    handle_create_customer($_POST, $current_user);
}
elseif (preg_match('/^\/customers\/(\d+)\/update$/', $route, $matches) && $_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!has_permission('customers', 'update')) redirect('/customers?error=permission_denied');
    handle_update_customer((int)$matches[1], $_POST, $current_user);
}
elseif (preg_match('/^\/customers\/(\d+)\/delete$/', $route, $matches) && $_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!has_permission('customers', 'delete')) redirect('/customers?error=permission_denied');
    handle_delete_customer((int)$matches[1], $current_user);
}


// --- GET/PAGE ROUTES ---

elseif ($route === '/customers') {
    if (!has_permission('customers', 'view')) { http_response_code(403); exit('Access Denied'); }
    $data = get_customers_list_data($current_user);
    $page_title = 'Manage Customers';
    $customers = $data['customers'];
    $pagination = $data['pagination'];
    $params = $data['params'];
    $is_admin_view = $data['is_admin_view'];
    $content_view = __DIR__ . '/templates/index.php';
    require_once __DIR__ . '/../../templates/layout.php';
}

elseif ($route === '/customers/create') {
    if (!has_permission('customers', 'create')) { http_response_code(403); exit('Access Denied'); }
    $page_title = 'Add New Customer';
    $dealers = ($current_user['role'] === 'admin') ? get_all_dealers_for_dropdown() : [];
    $content_view = __DIR__ . '/templates/create.php';
    require_once __DIR__ . '/../../templates/layout.php';
}

elseif (preg_match('/^\/customers\/(\d+)\/edit$/', $route, $matches)) {
    if (!has_permission('customers', 'update')) { http_response_code(403); exit('Access Denied'); }
    $customer_id = (int)$matches[1];
    $customer = get_customer_by_id($customer_id, $current_user);
    
    if (!$customer) { http_response_code(404); exit("Customer not found"); }
    
    $page_title = 'Edit Customer';
    $dealers = ($current_user['role'] === 'admin') ? get_all_dealers_for_dropdown() : [];
    $content_view = __DIR__ . '/templates/edit.php';
    require_once __DIR__ . '/../../templates/layout.php';
}
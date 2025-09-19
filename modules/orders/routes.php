<?php
// modules/orders/routes.php

require_login();
require_once __DIR__ . '/actions.php';

$route = get_route();
$current_user = current_user();

// --- API ROUTES ---
// This new block will handle the API calls for fetching customers and versions.
if (preg_match('/^\/orders\/api\/customers\/(\d+)$/', $route, $matches)) {
    $dealer_id = (int)$matches[1];
    
    // Security check: A dealer can only fetch their own customers
    if ($current_user['role'] !== 'admin' && $dealer_id !== $current_user['dealer_id']) {
        json_response(403, ['error' => 'Permission Denied']);
    }
    
    $conn = get_db_connection();
    $customers = [];
    $stmt = $conn->prepare("SELECT id, name FROM customers WHERE dealer_id = ? ORDER BY name ASC");
    $stmt->bind_param('i', $dealer_id);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $customers[] = $row;
    }
    $stmt->close();
    json_response(200, $customers);
    return; // Stop further execution
}
elseif (preg_match('/^\/orders\/api\/versions\/(\d+)$/', $route, $matches)) {
    $sku_id = (int)$matches[1];
    
    $conn = get_db_connection();
    $versions = [];
    $stmt = $conn->prepare(
        "SELECT id, version_number, release_date, description, tally_compat_from
         FROM sku_versions 
         WHERE sku_id = ? 
         ORDER BY release_date DESC"
    );
    $stmt->bind_param('i', $sku_id);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $versions[] = $row;
    }
    $stmt->close();
    json_response(200, $versions);
    return; // Stop further execution
}

// --- API ROUTES ---
// ... (existing API routes for customers and versions) ...
elseif (preg_match('/^\/orders\/api\/details\/(\d+)$/', $route, $matches)) {
    $order_id = (int)$matches[1];
    $details = get_order_details_for_trial_request($order_id, $current_user);
    if (!$details) {
        json_response(404, ['error' => 'Order or Customer not found, or permission denied.']);
    }
    json_response(200, $details);
    return; // Stop further execution
}


// --- POST/ACTION ROUTES ---

if ($route === '/orders/store' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!has_permission('orders', 'create')) redirect('/orders?error=permission_denied');
    handle_create_order($_POST, $current_user);
}
elseif (preg_match('/^\/orders\/(\d+)\/process$/', $route, $matches) && $_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!has_permission('orders', 'update')) {
        redirect('/orders?error=permission_denied');
    }
    handle_process_order((int)$matches[1]);
}
elseif (preg_match('/^\/orders\/(\d+)\/cancel$/', $route, $matches) && $_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!has_permission('orders', 'update')) { // Using 'update' permission for cancellation
        redirect('/orders?error=permission_denied');
    }
    handle_cancel_order((int)$matches[1]);
}
// --- GET/PAGE ROUTES ---
elseif ($route === '/orders') {
    if (!has_permission('orders', 'view')) { http_response_code(403); exit('Access Denied'); }
    $page_title = 'Manage Orders';
    $data = get_orders_list_data($current_user);
    $orders = $data['orders'];
    $pagination = $data['pagination'];
    $params = $data['params'];
    $is_admin_view = $data['is_admin_view'];
    $content_view = __DIR__ . '/templates/index.php';
    require_once __DIR__ . '/../../templates/layout.php';
}
elseif ($route === '/orders/create') {
    if (!has_permission('orders', 'create')) { http_response_code(403); exit('Access Denied'); }
    $page_title = 'Create New Order';
    $form_data = get_order_form_initial_data($current_user);
    $content_view = __DIR__ . '/templates/create.php';
    require_once __DIR__ . '/../../templates/layout.php';
}
elseif (preg_match('/^\/orders\/(\d+)\/view$/', $route, $matches)) {
    $order_id = (int)$matches[1];
    // Permission to view is handled within the get_order_details function
    if (!has_permission('orders', 'view')) {
        http_response_code(403);
        exit('Access Denied');
    }
    
    $order = get_order_details($order_id, $current_user);
    
    if (!$order) {
        http_response_code(404);
        exit('Order not found or permission denied.');
    }
    
    $page_title = 'Order Details #' . e($order['order_number']);
    $content_view = __DIR__ . '/templates/view.php';
    require_once __DIR__ . '/../../templates/layout.php';
}
<?<?php
// modules/requests/routes.php

require_login();
require_once __DIR__ . '/actions.php';
require_once __DIR__ . '/../orders/actions.php'; 

$route = get_route();
$current_user = current_user();

// --- POST/ACTION ROUTES ---
if ($route === '/requests/store_trial' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!has_permission('requests', 'create')) redirect('/requests?error=permission_denied');
    handle_create_request($_POST, $current_user, 'trial');
}
elseif ($route === '/requests/store_subscribe' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!has_permission('requests', 'create')) redirect('/requests?error=permission_denied');
    handle_create_request($_POST, $current_user, 'subscribe');
}
elseif ($route === '/requests/store_renew' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!has_permission('requests', 'create')) redirect('/requests?error=permission_denied');
    handle_create_request($_POST, $current_user, 'renew');
}
elseif (preg_match('/^\/requests\/(\d+)\/process$/', $route, $matches) && $_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($current_user['role'] !== 'admin') { http_response_code(403); exit('Forbidden'); }
    // This is the corrected line: We pass all three required arguments.
    handle_process_request((int)$matches[1], $_POST, $current_user);
}

// --- GET/PAGE ROUTES ---

elseif ($route === '/requests') {
    if (!has_permission('requests', 'view')) { http_response_code(403); exit('Access Denied'); }
    $page_title = 'Manage Requests';
    $data = get_all_requests($current_user);
    $requests = $data['requests'];
    $pagination = $data['pagination'];
    $params = $data['params'];
    $is_admin_view = $data['is_admin_view'];
    $content_view = __DIR__ . '/templates/index.php';
    require_once __DIR__ . '/../../templates/layout.php';
}

elseif ($route === '/requests/trial') {
    if (!has_permission('requests', 'create')) { http_response_code(403); exit('Access Denied'); }
    
    $form_data = $_SESSION['flash_form_data'] ?? [];
    $error = $_SESSION['flash_error'] ?? null;
    unset($_SESSION['flash_form_data'], $_SESSION['flash_error']);

    $page_title = 'Request New Trial';
    $data = get_data_for_trial_request($current_user);
    $content_view = __DIR__ . '/templates/trial.php';
    require_once __DIR__ . '/../../templates/layout.php';
}
elseif ($route === '/requests/subscribe') {
    if (!has_permission('requests', 'create')) { http_response_code(403); exit('Access Denied'); }
    
    // --- THIS LOGIC IS REPLACED ---
    $order_id = $_GET['order_id'] ?? null;
    $serial_id = $_GET['serial_id'] ?? null;

    if (!$order_id || !$serial_id) {
        redirect('/requests?error=invalid_request_parameters');
    }
    
    $page_title = 'Request Subscription';
    $data = get_data_for_subscribe_request((int)$order_id, (int)$serial_id, $current_user);
    
    if (!$data) {
        redirect('/orders/' . $order_id . '/view?error=subscription_already_exists_or_invalid');
    }
    
    $content_view = __DIR__ . '/templates/subscribe.php';
    require_once __DIR__ . '/../../templates/layout.php';
}

elseif ($route === '/requests/renew') {
    if (!has_permission('requests', 'create')) { http_response_code(403); exit('Access Denied'); }
    $page_title = 'Request Renewal';
    $data = get_data_for_renew_request($current_user);
    $content_view = __DIR__ . '/templates/renew.php';
    require_once __DIR__ . '/../../templates/layout.php';
}

elseif (preg_match('/^\/requests\/(\d+)\/review$/', $route, $matches)) {
    if ($current_user['role'] !== 'admin') { http_response_code(403); exit('Forbidden'); }
    $request_id = (int)$matches[1];
    $request = get_request_by_id($request_id, $current_user);
    if (!$request) { http_response_code(404); exit('Request Not Found'); }
    
    $page_title = 'Review Request';
    $content_view = __DIR__ . '/templates/review.php';
    require_once __DIR__ . '/../../templates/layout.php';
}
elseif (preg_match('/^\/requests\/(\d+)\/view$/', $route, $matches)) {
    $request_id = (int)$matches[1];
    $request = get_request_by_id($request_id, $current_user);
    if (!$request) { 
        http_response_code(404); 
        exit("Request Not Found or Permission Denied."); 
    }
    
    $page_title = 'View Request #' . e($request['id']);
    $content_view = __DIR__ . '/templates/view.php';
    require_once __DIR__ . '/../../templates/layout.php';
}
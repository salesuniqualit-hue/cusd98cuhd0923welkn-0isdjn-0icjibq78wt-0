<?php
// modules/permissions/routes.php

require_login();
require_once __DIR__ . '/actions.php';

$route = get_route();
$current_user = current_user();

// Security: Only admins and dealers can access this module.
if ($current_user['role'] !== 'admin' && $current_user['role'] !== 'dealer') {
    http_response_code(404);
    echo "<h1>404 Not Found</h1>";
    exit();
}

// --- POST/ACTION ROUTES ---

// Handles saving the permissions form
if ($route === '/permissions/store' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    handle_save_permissions($_POST, $current_user);
}


// --- GET/PAGE ROUTES ---

// Main permissions page - lists users to manage
elseif ($route === '/permissions') {
    $data = get_permissions_page_data($current_user);
    $page_title = $data['page_title'];
    $users_to_manage = $data['users'];
    $content_view = __DIR__ . '/templates/index.php';
    require_once __DIR__ . '/../../templates/layout.php';
}

// Shows the permissions editor for a specific user
elseif (preg_match('/^\/permissions\/user\/(\d+)$/', $route, $matches)) {
    $user_id_to_manage = (int)$matches[1];
    
    $data = get_user_permissions_data($user_id_to_manage, $current_user);
    if (!$data) {
        http_response_code(403);
        exit("Permission Denied.");
    }
    
    $page_title = 'Set Permissions for ' . e($data['user_to_manage']['name']);
    $user_to_manage = $data['user_to_manage'];
    $modules = $data['modules'];
    $current_permissions = $data['current_permissions'];
    
    $content_view = __DIR__ . '/templates/edit.php';
    require_once __DIR__ . '/../../templates/layout.php';
}
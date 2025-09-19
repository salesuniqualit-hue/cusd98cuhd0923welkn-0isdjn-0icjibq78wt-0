<?php
// modules/users/routes.php

require_login();
require_once __DIR__ . '/actions.php';

$route = get_route();
$user = current_user();

// The main list view requires 'view' permission, but dealers have implicit access.
if ($route === '/users' && !has_permission('users', 'view') && $user['role'] !== 'dealer') {
    http_response_code(403); exit('Access Denied');
}

// --- POST/ACTION ROUTES (no HTML output) ---

if ($route === '/users/store' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!has_permission('users', 'create')) redirect('/users?error=permission_denied');
    handle_create_user($_POST);
}
elseif (preg_match('/^\/users\/(\d+)\/update$/', $route, $matches) && $_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!has_permission('users', 'update')) redirect('/users?error=permission_denied');
    $user_id = (int)$matches[1];
    handle_update_user($user_id, $_POST);
}
elseif (preg_match('/^\/users\/(\d+)\/delete$/', $route, $matches) && $_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!has_permission('users', 'delete')) redirect('/users?error=permission_denied');
    $user_id = (int)$matches[1];
    handle_delete_user($user_id);
}


// --- GET/PAGE ROUTES (renders HTML) ---

elseif ($route === '/users') {
    // Permission for viewing the list is already checked at the top.
    $data = get_users_list_data($user);
    $page_title = $data['page_title'];
    $users = $data['users'];
    $pagination = $data['pagination'];
    $params = $data['params'];
    $content_view = __DIR__ . '/templates/index.php';
    require_once __DIR__ . '/../../templates/layout.php';
}

elseif ($route === '/users/create') {
    if (!has_permission('users', 'create')) { http_response_code(403); exit('Access Denied'); }
    $page_title = 'Add New User';
    $form_data = ['name' => '', 'email' => '', 'role' => '', 'is_active' => 1];
    $errors = [];
    $content_view = __DIR__ . '/templates/create.php';
    require_once __DIR__ . '/../../templates/layout.php';
}

elseif (preg_match('/^\/users\/(\d+)\/edit$/', $route, $matches)) {
    if (!has_permission('users', 'update')) { http_response_code(403); exit('Access Denied'); }
    $user_id = (int)$matches[1];
    $user_to_edit = get_user_by_id($user_id);

    if (!$user_to_edit) { http_response_code(404); exit("User not found."); }
    
    $page_title = 'Edit User: ' . e($user_to_edit['name']);
    $form_data = $user_to_edit;
    $errors = [];
    $content_view = __DIR__ . '/templates/edit.php';
    require_once __DIR__ . '/../../templates/layout.php';
}
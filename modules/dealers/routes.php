<?php
// modules/dealers/routes.php

// The require_login() ensures a user is logged in.
require_login();
// This line is ESSENTIAL. It loads all functions like get_dealer_by_id().
require_once __DIR__ . '/actions.php';

// Security: Only allow admins to access this entire module.
$current_user = current_user();
if ($current_user['role'] !== 'admin') {
    // If not an admin, show a generic 404 to prevent revealing the page exists.
    http_response_code(404);
    echo "<h1>404 Not Found</h1>";
    exit();
}

$route = get_route();

// --- POST/ACTION ROUTES ---

if ($route === '/dealers/store' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    handle_create_dealer($_POST);
}
elseif (preg_match('/^\/dealers\/(\d+)\/update$/', $route, $matches) && $_SERVER['REQUEST_METHOD'] === 'POST') {
    handle_update_dealer((int)$matches[1], $_POST);
}
elseif (preg_match('/^\/dealers\/(\d+)\/delete$/', $route, $matches) && $_SERVER['REQUEST_METHOD'] === 'POST') {
    handle_delete_dealer((int)$matches[1]);
}
// --- ADD THIS NEW BLOCK ---
elseif (preg_match('/^\/dealers\/(\d+)\/activate$/', $route, $matches) && $_SERVER['REQUEST_METHOD'] === 'POST') {
    handle_activate_dealer((int)$matches[1]);
}

// --- GET/PAGE ROUTES ---
elseif ($route === '/dealers') {
    $page_title = 'Manage Dealers';
    $data = get_all_dealers();
    $dealers = $data['dealers'];
    $pagination = $data['pagination'];
    $params = $data['params'];
    $content_view = __DIR__ . '/templates/index.php';
    require_once __DIR__ . '/../../templates/layout.php';
}

elseif ($route === '/dealers/create') {
    $page_title = 'Add New Dealer';
    $content_view = __DIR__ . '/templates/create.php';
    require_once __DIR__ . '/../../templates/layout.php';
}

elseif (preg_match('/^\/dealers\/(\d+)\/edit$/', $route, $matches)) {
    $dealer_id = (int)$matches[1];
    // This call will now succeed because actions.php was loaded
    $dealer = get_dealer_by_id($dealer_id);
    if (!$dealer) {
        http_response_code(404);
        echo "Dealer not found.";
        exit();
    }
    $page_title = 'Edit Dealer';
    $content_view = __DIR__ . '/templates/edit.php';
    require_once __DIR__ . '/../../templates/layout.php';
}
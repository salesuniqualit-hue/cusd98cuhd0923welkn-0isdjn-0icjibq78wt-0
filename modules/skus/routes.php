<?php
// modules/skus/routes.php

require_login();
require_once __DIR__ . '/actions.php';

// Security: This is an admin-only module.
if (current_user()['role'] !== 'admin') {
    http_response_code(404);
    echo "<h1>404 Not Found</h1>";
    exit();
}

$route = get_route();

// --- POST/ACTION ROUTES ---

if ($route === '/skus/store' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    handle_create_sku($_POST);
}
elseif (preg_match('/^\/skus\/(\d+)\/update$/', $route, $matches) && $_SERVER['REQUEST_METHOD'] === 'POST') {
    handle_update_sku((int)$matches[1], $_POST);
}
elseif (preg_match('/^\/skus\/(\d+)\/delete$/', $route, $matches) && $_SERVER['REQUEST_METHOD'] === 'POST') {
    handle_delete_sku((int)$matches[1]);
}


// --- GET/PAGE ROUTES ---

elseif ($route === '/skus') {
    $page_title = 'Manage SKUs';
    $data = get_all_skus();
    $skus = $data['skus'];
    $pagination = $data['pagination'];
    $params = $data['params'];
    $content_view = __DIR__ . '/templates/index.php';
    require_once __DIR__ . '/../../templates/layout.php';
}

elseif ($route === '/skus/create') {
    $page_title = 'Add New SKU';
    // We need the list of categories for the form dropdown.
    $categories = get_all_sku_categories();
    $content_view = __DIR__ . '/templates/create.php';
    require_once __DIR__ . '/../../templates/layout.php';
}

elseif (preg_match('/^\/skus\/(\d+)\/edit$/', $route, $matches)) {
    $sku_id = (int)$matches[1];
    $sku = get_sku_by_id($sku_id);
    if (!$sku) {
        http_response_code(404);
        echo "SKU not found.";
        exit();
    }
    
    $page_title = 'Edit SKU';
    $categories = get_all_sku_categories();
    $content_view = __DIR__ . '/templates/edit.php';
    require_once __DIR__ . '/../../templates/layout.php';
}
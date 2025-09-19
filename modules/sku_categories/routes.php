<?php
// modules/sku_categories/routes.php

require_login();
require_once __DIR__ . '/actions.php';

// Security: Only allow admins to access this module.
if (current_user()['role'] !== 'admin') {
    http_response_code(404);
    echo "<h1>404 Not Found</h1>";
    exit();
}

$route = get_route();

// --- POST/ACTION ROUTES ---

if ($route === '/sku_categories/store' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    handle_create_category($_POST);
}
elseif (preg_match('/^\/sku_categories\/(\d+)\/update$/', $route, $matches) && $_SERVER['REQUEST_METHOD'] === 'POST') {
    handle_update_category((int)$matches[1], $_POST);
}
elseif (preg_match('/^\/sku_categories\/(\d+)\/delete$/', $route, $matches) && $_SERVER['REQUEST_METHOD'] === 'POST') {
    handle_delete_category((int)$matches[1]);
}


// --- GET/PAGE ROUTES ---

elseif ($route === '/sku_categories') {
    $page_title = 'Manage SKU Categories';
    $data = get_all_categories();
    $categories = $data['categories'];
    $pagination = $data['pagination'];
    $params = $data['params'];
    $content_view = __DIR__ . '/templates/index.php';
    require_once __DIR__ . '/../../templates/layout.php';
}

elseif ($route === '/sku_categories/create') {
    $page_title = 'Add New SKU Category';
    // Get all existing categories to populate the "Parent Category" dropdown.
    $parent_categories = get_all_categories();
    $content_view = __DIR__ . '/templates/create.php';
    require_once __DIR__ . '/../../templates/layout.php';
}

elseif (preg_match('/^\/sku_categories\/(\d+)\/edit$/', $route, $matches)) {
    $category_id = (int)$matches[1];
    $category = get_category_by_id($category_id);
    if (!$category) {
        http_response_code(404);
        echo "Category not found.";
        exit();
    }
    
    $page_title = 'Edit SKU Category';
    // Also get categories for the parent dropdown in the edit form.
    $parent_categories = get_all_categories();
    $content_view = __DIR__ . '/templates/edit.php';
    require_once __DIR__ . '/../../templates/layout.php';
}
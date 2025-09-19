<?php
// modules/sku_versions/routes.php

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

if ($route === '/sku_versions/store' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    handle_create_sku_version($_POST);
}
elseif (preg_match('/^\/sku_versions\/(\d+)\/update$/', $route, $matches) && $_SERVER['REQUEST_METHOD'] === 'POST') {
    handle_update_sku_version((int)$matches[1], $_POST);
}
elseif (preg_match('/^\/sku_versions\/(\d+)\/delete$/', $route, $matches) && $_SERVER['REQUEST_METHOD'] === 'POST') {
    handle_delete_sku_version((int)$matches[1]);
}


// --- GET/PAGE ROUTES ---

elseif ($route === '/sku_versions') {
    $page_title = 'Manage SKU Versions';
    $data = get_all_sku_versions();
    $versions = $data['versions'];
    $pagination = $data['pagination'];
    $params = $data['params'];
    $content_view = __DIR__ . '/templates/index.php';
    require_once __DIR__ . '/../../templates/layout.php';
}

elseif ($route === '/sku_versions/create') {
    $page_title = 'Add New SKU Version';
    // We need lists of SKUs and available changelogs for the form dropdowns.
    $skus = get_all_skus_for_dropdown();
    $changelogs = get_available_changelogs();
    $content_view = __DIR__ . '/templates/create.php';
    require_once __DIR__ . '/../../templates/layout.php';
}

elseif (preg_match('/^\/sku_versions\/(\d+)\/edit$/', $route, $matches)) {
    $version_id = (int)$matches[1];
    $version = get_sku_version_by_id($version_id);
    if (!$version) {
        http_response_code(404);
        echo "SKU Version not found.";
        exit();
    }
    
    $page_title = 'Edit SKU Version';
    $skus = get_all_skus_for_dropdown();
    // For editing, we need the currently linked changelog plus any other available ones.
    $changelogs = get_available_changelogs($version['changelog_id']);
    $content_view = __DIR__ . '/templates/edit.php';
    require_once __DIR__ . '/../../templates/layout.php';
}
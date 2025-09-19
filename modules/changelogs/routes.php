<?php
// modules/changelogs/routes.php

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

if ($route === '/changelogs/store' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    handle_create_changelog($_POST);
}
elseif (preg_match('/^\/changelogs\/(\d+)\/update$/', $route, $matches) && $_SERVER['REQUEST_METHOD'] === 'POST') {
    handle_update_changelog((int)$matches[1], $_POST);
}
elseif (preg_match('/^\/changelogs\/(\d+)\/delete$/', $route, $matches) && $_SERVER['REQUEST_METHOD'] === 'POST') {
    handle_delete_changelog((int)$matches[1]);
}


// --- GET/PAGE ROUTES ---

elseif ($route === '/changelogs') {
    $page_title = 'Manage Changelogs';
    $data = get_all_changelogs();
    $changelogs = $data['changelogs'];
    $pagination = $data['pagination'];
    $params = $data['params'];
    $content_view = __DIR__ . '/templates/index.php';
    require_once __DIR__ . '/../../templates/layout.php';
}

elseif ($route === '/changelogs/create') {
    $page_title = 'Add New Changelog';
    // Get a list of SKUs for the dropdown.
    $skus = get_all_skus_for_dropdown();
    $content_view = __DIR__ . '/templates/create.php';
    require_once __DIR__ . '/../../templates/layout.php';
}

elseif (preg_match('/^\/changelogs\/(\d+)\/edit$/', $route, $matches)) {
    $changelog_id = (int)$matches[1];
    $changelog = get_changelog_by_id($changelog_id);
    if (!$changelog) {
        http_response_code(404);
        echo "Changelog not found.";
        exit();
    }
    
    $page_title = 'Edit Changelog';
    $skus = get_all_skus_for_dropdown();
    $content_view = __DIR__ . '/templates/edit.php';
    require_once __DIR__ . '/../../templates/layout.php';
}
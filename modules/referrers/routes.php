<?php
// modules/referrers/routes.php
require_login();
require_once __DIR__ . '/actions.php';

$route = get_route();
$current_user = current_user();

if (!has_referrer_permission($current_user)) {
    redirect('/?error=permission_denied');
}

// --- POST/ACTION ROUTES ---
if ($route === '/referrers/store' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    handle_create_referrer($_POST, $current_user);
}
elseif (preg_match('/^\/referrers\/(\d+)\/update$/', $route, $matches) && $_SERVER['REQUEST_METHOD'] === 'POST') {
    handle_update_referrer((int)$matches[1], $_POST, $current_user);
}
elseif (preg_match('/^\/referrers\/(\d+)\/delete$/', $route, $matches) && $_SERVER['REQUEST_METHOD'] === 'POST') {
    handle_delete_referrer((int)$matches[1], $current_user);
}

// --- GET/PAGE ROUTES ---
elseif ($route === '/referrers') {
    $page_title = 'Manage Referrers';
    $referrers = get_all_referrers($current_user);
    $content_view = __DIR__ . '/templates/index.php';
    require_once __DIR__ . '/../../templates/layout.php';
}
elseif ($route === '/referrers/create') {
    $page_title = 'Add New Referrer';
    $content_view = __DIR__ . '/templates/create.php';
    require_once __DIR__ . '/../../templates/layout.php';
}
elseif (preg_match('/^\/referrers\/(\d+)\/edit$/', $route, $matches)) {
    $referrer = get_referrer_by_id((int)$matches[1], $current_user);
    if (!$referrer) {
        redirect('/referrers?error=not_found');
    }
    $page_title = 'Edit Referrer';
    $content_view = __DIR__ . '/templates/edit.php';
    require_once __DIR__ . '/../../templates/layout.php';
}
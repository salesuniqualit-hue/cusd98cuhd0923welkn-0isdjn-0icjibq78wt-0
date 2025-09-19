<?php
// modules/profile/routes.php

require_login();
require_once __DIR__ . '/actions.php';

$route = get_route();
$current_user = current_user();

// --- POST/ACTION ROUTE ---

if ($route === '/profile/update' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    handle_update_profile($_POST, $current_user);
}

// --- GET/PAGE ROUTE ---

elseif ($route === '/profile') {
    $page_title = 'My Profile';
    $profile_data = get_profile_data($current_user);
    $content_view = __DIR__ . '/templates/index.php';
    require_once __DIR__ . '/../../templates/layout.php';
}
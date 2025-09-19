<?php
// modules/pricing/routes.php

require_login();
require_once __DIR__ . '/../dealers/actions.php'; // Add this line
require_once __DIR__ . '/../skus/actions.php'; // Add this line
require_once __DIR__ . '/actions.php';

// Security: This is an admin-only module.
if (current_user()['role'] !== 'admin') {
    http_response_code(404);
    echo "<h1>404 Not Found</h1>";
    exit();
}

$route = get_route();

// --- POST/ACTION ROUTES ---

if ($route === '/pricing/store_standard' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    handle_update_standard_prices($_POST);
}
elseif ($route === '/pricing/store_dealer' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    handle_update_dealer_prices($_POST);
}
elseif ($route === '/pricing/process_revision' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    handle_price_revision($_POST);
}


// --- GET/PAGE ROUTES ---

elseif ($route === '/pricing') {
    $page_title = 'Manage Pricing';
    $skus = get_all_skus_with_prices();
    $dealers = get_all_dealers_for_dropdown();
    $content_view = __DIR__ . '/templates/index.php';
    require_once __DIR__ . '/../../templates/layout.php';
}

elseif ($route === '/pricing/revise') {
    $page_title = 'Revise Price Lists';
    $skus = get_all_skus_for_dropdown();
    $dealers = get_all_dealers_for_dropdown();
    $content_view = __DIR__ . '/templates/revise.php';
    require_once __DIR__ . '/../../templates/layout.php';
}

// This handles loading a specific dealer's price list via a GET request (e.g., /pricing?dealer_id=5)
// It reuses the main index view but will have data for a specific dealer.
elseif (isset($_GET['dealer_id'])) {
    $page_title = 'Manage Pricing';
    $dealer_id = (int)$_GET['dealer_id'];
    $skus = get_dealer_price_list($dealer_id);
    $dealers = get_all_dealers_for_dropdown();
    $selected_dealer = get_dealer_by_id($dealer_id); // To show the dealer's name
    $content_view = __DIR__ . '/templates/index.php';
    require_once __DIR__ . '/../../templates/layout.php';
}
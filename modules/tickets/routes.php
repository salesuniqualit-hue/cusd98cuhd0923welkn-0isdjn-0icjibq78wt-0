<?php
// modules/tickets/routes.php

require_login();
require_once __DIR__ . '/actions.php';

$route = get_route();
$current_user = current_user();

// --- POST/ACTION ROUTES ---

if ($route === '/tickets/store' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!has_permission('tickets', 'create')) redirect('/tickets?error=permission_denied');
    handle_create_ticket($_POST, $current_user);
}
elseif (preg_match('/^\/tickets\/(\d+)\/reply$/', $route, $matches) && $_SERVER['REQUEST_METHOD'] === 'POST') {
    // Both admin and dealer can reply, the check is inside get_ticket_by_id
    handle_add_reply((int)$matches[1], $_POST, $current_user);
}

// --- GET/PAGE ROUTES ---

elseif ($route === '/tickets') {
    if (!has_permission('tickets', 'view')) { http_response_code(403); exit('Access Denied'); }
    $page_title = 'Support Tickets';
    $data = get_all_tickets($current_user);
    $tickets = $data['tickets'];
    $pagination = $data['pagination'];
    $params = $data['params'];
    $content_view = __DIR__ . '/templates/index.php';
    require_once __DIR__ . '/../../templates/layout.php';
}

elseif ($route === '/tickets/create') {
    if (!has_permission('tickets', 'create')) { http_response_code(403); exit('Access Denied'); }
    $page_title = 'Create New Ticket';
    $form_data = get_ticket_form_data($current_user);
    $content_view = __DIR__ . '/templates/create.php';
    require_once __DIR__ . '/../../templates/layout.php';
}

elseif (preg_match('/^\/tickets\/(\d+)$/', $route, $matches)) {
    // Permission to view is checked inside get_ticket_by_id
    $ticket_id = (int)$matches[1];
    $ticket_data = get_ticket_by_id($ticket_id, $current_user);
    
    if (!$ticket_data) { http_response_code(404); exit("Ticket not found or permission denied."); }
    
    $page_title = 'View Ticket #' . e($ticket_data['ticket']['ticket_number']);
    $ticket = $ticket_data['ticket'];
    $replies = $ticket_data['replies'];
    $content_view = __DIR__ . '/templates/view.php';
    require_once __DIR__ . '/../../templates/layout.php';
}
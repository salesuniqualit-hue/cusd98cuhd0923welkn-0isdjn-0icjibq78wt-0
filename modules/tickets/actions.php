<?php
// modules/tickets/actions.php

require_once __DIR__ . '/../skus/actions.php'; // Add this line

/**
 * Fetches the necessary data (SKUs) for the "Create Ticket" form.
 */
function get_ticket_form_data($current_user) {
    // In a real scenario, this might be limited to SKUs the dealer has purchased.
    // For now, we get all SKUs.
    return ['skus' => get_all_skus_for_dropdown()];
}

/**
 * Fetches a paginated, sortable, and filterable list of tickets.
 *
 * @param array $current_user The logged-in user's session data.
 * @return array A list of ticket records and pagination data.
 */
function get_all_tickets($current_user) {
    $conn = get_db_connection();
    $params = $_GET;
    $current_page = isset($params['page']) ? (int)$params['page'] : 1;
    $offset = ($current_page - 1) * ITEMS_PER_PAGE;

    // Whitelist for sortable columns
    $sortable_columns = ['ticket_number', 'title', 'status', 'updated_at', 'reporter_name'];
    $sort = isset($params['sort']) && in_array($params['sort'], $sortable_columns) ? $params['sort'] : 'updated_at';
    $order = isset($params['order']) && in_array(strtolower($params['order']), ['asc', 'desc']) ? strtolower($params['order']) : 'desc';

    // Base query
    $base_sql = "FROM tickets t JOIN users u ON t.user_id = u.id ";
    $where_clauses = [];
    $bind_params = [];
    $types = '';

    // Role-based initial filter
    if ($current_user['role'] !== 'admin') {
        $where_clauses[] = "u.dealer_id = ?";
        $bind_params[] = $current_user['dealer_id'];
        $types .= 'i';
    }

    if (in_array($current_user['role'], ['dealer', 'internal_user']) && !empty($params['filter_reporter_name'])) {
        $where_clauses[] = "u.name LIKE ?";
        $bind_params[] = '%' . $params['filter_reporter_name'] . '%';
        $types .= 's';
    }


    // Add search filters
    if (!empty($params['filter_ticket_number'])) {
        $where_clauses[] = "t.ticket_number LIKE ?";
        $bind_params[] = '%' . $params['filter_ticket_number'] . '%';
        $types .= 's';
    }
    if (!empty($params['filter_title'])) {
        $where_clauses[] = "t.title LIKE ?";
        $bind_params[] = '%' . $params['filter_title'] . '%';
        $types .= 's';
    }
    if (!empty($params['filter_status'])) {
        $where_clauses[] = "t.status = ?";
        $bind_params[] = $params['filter_status'];
        $types .= 's';
    }
    if (!empty($params['filter_updated_from'])) {
        $where_clauses[] = "t.updated_at >= ?";
        $bind_params[] = $params['filter_updated_from'];
        $types .= 's';
    }
    if (!empty($params['filter_updated_to'])) {
        $where_clauses[] = "t.updated_at <= ?";
        $bind_params[] = $params['filter_updated_to'];
        $types .= 's';
    }


    $where_sql = count($where_clauses) > 0 ? 'WHERE ' . implode(' AND ', $where_clauses) : '';
    $full_base_sql = $base_sql . $where_sql;

    // Count total matching tickets
    $count_stmt = $conn->prepare("SELECT COUNT(t.id) " . $full_base_sql);
    if (count($bind_params) > 0) $count_stmt->bind_param($types, ...$bind_params);
    $count_stmt->execute();
    $total_tickets = $count_stmt->get_result()->fetch_row()[0];

    // Fetch data for the current page
    $tickets = [];
    $data_sql = "SELECT t.id, t.ticket_number, t.title, t.status, t.updated_at, u.name as reporter_name
                 " . $full_base_sql . " ORDER BY $sort $order LIMIT ? OFFSET ?";

    $stmt = $conn->prepare($data_sql);
    $all_bind_params = array_merge($bind_params, [ITEMS_PER_PAGE, $offset]);
    $all_types = $types . 'ii';
    if (count($all_bind_params) > 0) $stmt->bind_param($all_types, ...$all_bind_params);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $tickets[] = $row;
    }

    $pagination_html = generate_pagination_links($total_tickets, $current_page, '/tickets', $params);

    return [
        'tickets' => $tickets,
        'pagination' => $pagination_html,
        'params' => $params
    ];
}

/**
 * Fetches a single ticket and all its replies, with permission checks.
 */
function get_ticket_by_id($ticket_id, $current_user) {
    $conn = get_db_connection();
    
    // Get main ticket info
    $stmt = $conn->prepare("SELECT t.*, s.name as sku_name, sv.version_number FROM tickets t JOIN skus s ON t.sku_id = s.id JOIN sku_versions sv ON t.sku_version_id = sv.id WHERE t.id = ?");
    $stmt->bind_param('i', $ticket_id);
    $stmt->execute();
    $ticket = $stmt->get_result()->fetch_assoc();

    if (!$ticket) return null;

    // Permission Check
    $creator_user = $conn->query("SELECT dealer_id FROM users WHERE id = " . $ticket['user_id'])->fetch_assoc();
    if ($current_user['role'] !== 'admin' && $creator_user['dealer_id'] != $current_user['dealer_id']) {
        return null; // Deny access
    }

    // Get replies
    $replies = [];
    $stmt = $conn->prepare("SELECT tr.*, u.name as author, u.role as author_role FROM ticket_replies tr JOIN users u ON tr.user_id = u.id WHERE tr.ticket_id = ? ORDER BY tr.created_at ASC");
    $stmt->bind_param('i', $ticket_id);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $replies[] = $row;
    }

    return ['ticket' => $ticket, 'replies' => $replies];
}

/**
 * Handles the creation of a new ticket.
 */
function handle_create_ticket($data, $current_user) {
    $conn = get_db_connection();
    $conn->begin_transaction();

    try {
        // Generate a unique ticket number: mmyy-#####
        $prefix = date('my');
        $sql_count = "SELECT COUNT(*) as count FROM tickets WHERE ticket_number LIKE '{$prefix}-%'";
        $count_result = $conn->query($sql_count)->fetch_assoc()['count'];
        $next_id = str_pad($count_result + 1, 5, '0', STR_PAD_LEFT);
        $ticket_number = "{$prefix}-{$next_id}";
        
        // --- MODIFIED: Handle optional sku_version_id ---
        $sku_version_id = null;
        if ($data['type'] !== 'feature_request') {
            if (empty($data['sku_version_id'])) {
                throw new Exception("SKU Version is required for this ticket type.");
            }
            $sku_version_id = (int)$data['sku_version_id'];
        } else {
            $sku_version_id = !empty($data['sku_version_id']) ? (int)$data['sku_version_id'] : null;
        }

        // Insert the main ticket record
        $stmt = $conn->prepare("INSERT INTO tickets (ticket_number, user_id, sku_id, sku_version_id, type, title) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param('siiiss', $ticket_number, $current_user['id'], $data['sku_id'], $sku_version_id, $data['type'], $data['title']);
        $stmt->execute();
        $ticket_id = $conn->insert_id;
        
        if (!$ticket_id) throw new Exception("Failed to create ticket record.");

        // Insert the description as the first reply
        $stmt_reply = $conn->prepare("INSERT INTO ticket_replies (ticket_id, user_id, reply_text) VALUES (?, ?, ?)");
        $stmt_reply->bind_param('iis', $ticket_id, $current_user['id'], $data['description']);
        $stmt_reply->execute();

        $conn->commit();
        redirect('/tickets?success=ticket_created');

    } catch (Exception $e) {
        $conn->rollback();
        redirect('/tickets/create?error=' . urlencode($e->getMessage()));
    }
}

/**
 * Handles adding a reply to an existing ticket.
 */
function handle_add_reply($ticket_id, $data, $current_user) {
    // Permission check is implicit here
    $ticket_data = get_ticket_by_id($ticket_id, $current_user);
    if (!$ticket_data) {
        redirect('/tickets?error=permission_denied');
    }

    $conn = get_db_connection();
    $conn->begin_transaction();

    try {
        // Step 1: Insert the new reply
        $stmt = $conn->prepare("INSERT INTO ticket_replies (ticket_id, user_id, reply_text) VALUES (?, ?, ?)");
        $stmt->bind_param('iis', $ticket_id, $current_user['id'], $data['reply_text']);
        $stmt->execute();

        // Step 2: Update the ticket's status and last update time
        $new_status = $data['status'] ?? ($current_user['role'] === 'admin' ? 'in_progress' : 'open');
        $stmt_update = $conn->prepare("UPDATE tickets SET status = ?, updated_at = NOW() WHERE id = ?");
        $stmt_update->bind_param('si', $new_status, $ticket_id);
        $stmt_update->execute();
        
        $conn->commit();
        redirect("/tickets/{$ticket_id}?success=reply_posted");

    } catch (Exception $e) {
        $conn->rollback();
        redirect("/tickets/{$ticket_id}?error=" . urlencode($e->getMessage()));
    }
}
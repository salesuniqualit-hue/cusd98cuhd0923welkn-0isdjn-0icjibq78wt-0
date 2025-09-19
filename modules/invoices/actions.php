<?php
// modules/invoices/actions.php

/**
 * Fetches data needed for the "Create Invoice" form (all processable orders).
 */
function get_invoice_form_data() {
    $conn = get_db_connection();
    // Fetches processed orders that do not yet have an invoice
    $sql = "SELECT o.id, c.name as customer_name, s.name as sku_name, d.company_name
            FROM orders o
            JOIN customers c ON o.customer_id = c.id
            JOIN skus s ON o.sku_id = s.id
            JOIN dealers d ON o.dealer_id = d.id
            LEFT JOIN invoices i ON o.id = i.order_id
            WHERE o.status = 'processed' AND i.id IS NULL
            ORDER BY d.company_name, c.name";
    return $conn->query($sql)->fetch_all(MYSQLI_ASSOC);
}

/**
 * Handles creating a new invoice.
 */
function handle_create_invoice($data) {
    $conn = get_db_connection();
    // Further validation should be added here
    $stmt = $conn->prepare(
        "INSERT INTO invoices (order_id, invoice_number, amount, issue_date, due_date, status) VALUES (?, ?, ?, ?, ?, ?)"
    );
    $stmt->bind_param(
        'isssss',
        $data['order_id'],
        $data['invoice_number'],
        $data['amount'],
        $data['issue_date'],
        $data['due_date'],
        $data['status']
    );
    if ($stmt->execute()) {
        redirect('/invoices?success=invoice_created');
    } else {
        redirect('/invoices/create?error=' . urlencode($stmt->error));
    }
}

/**
 * Fetches a paginated, sortable, and filterable list of ALL invoices for the admin view.
 */
function get_all_invoices() {
    $conn = get_db_connection();
    $params = $_GET;
    $current_page = isset($params['page']) ? (int)$params['page'] : 1;
    $offset = ($current_page - 1) * ITEMS_PER_PAGE;

    // Whitelist for sortable columns
    $sortable_columns = ['invoice_number', 'company_name', 'order_id', 'issue_date', 'amount', 'status'];
    $sort = isset($params['sort']) && in_array($params['sort'], $sortable_columns) ? $params['sort'] : 'issue_date';
    $order = isset($params['order']) && in_array(strtolower($params['order']), ['asc', 'desc']) ? strtolower($params['order']) : 'desc';

    // Base query
    $base_sql = "FROM invoices i 
                 JOIN orders o ON i.order_id = o.id 
                 JOIN dealers d ON o.dealer_id = d.id ";
    
    $where_clauses = [];
    $bind_params = [];
    $types = '';

    // Add filters
    if (!empty($params['filter_invoice_number'])) {
        $where_clauses[] = "i.invoice_number LIKE ?";
        $bind_params[] = '%' . $params['filter_invoice_number'] . '%';
        $types .= 's';
    }
    if (!empty($params['filter_dealer'])) {
        $where_clauses[] = "d.company_name LIKE ?";
        $bind_params[] = '%' . $params['filter_dealer'] . '%';
        $types .= 's';
    }
    if (!empty($params['filter_status'])) {
        $where_clauses[] = "i.status = ?";
        $bind_params[] = $params['filter_status'];
        $types .= 's';
    }
    if (!empty($params['filter_date_from'])) {
        $where_clauses[] = "i.issue_date >= ?";
        $bind_params[] = $params['filter_date_from'];
        $types .= 's';
    }
    if (!empty($params['filter_date_to'])) {
        $where_clauses[] = "i.issue_date <= ?";
        $bind_params[] = $params['filter_date_to'];
        $types .= 's';
    }

    $where_sql = count($where_clauses) > 0 ? 'WHERE ' . implode(' AND ', $where_clauses) : '';
    $full_base_sql = $base_sql . $where_sql;

    // Count total matching invoices
    $count_stmt = $conn->prepare("SELECT COUNT(i.id) " . $full_base_sql);
    if (count($bind_params) > 0) $count_stmt->bind_param($types, ...$bind_params);
    $count_stmt->execute();
    $total_invoices = $count_stmt->get_result()->fetch_row()[0];

    // Fetch data for the current page
    $invoices = [];
    $data_sql = "SELECT i.id, i.invoice_number, i.amount, i.issue_date, i.due_date, i.status, o.id as order_id, d.company_name 
                 " . $full_base_sql . " ORDER BY $sort $order LIMIT ? OFFSET ?";
    
    $stmt = $conn->prepare($data_sql);
    $all_bind_params = array_merge($bind_params, [ITEMS_PER_PAGE, $offset]);
    $all_types = $types . 'ii';
    if (count($all_bind_params) > 0) $stmt->bind_param($all_types, ...$all_bind_params);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $invoices[] = $row;
    }

    $pagination_html = generate_pagination_links($total_invoices, $current_page, '/invoices', $params);

    return [
        'invoices' => $invoices,
        'pagination' => $pagination_html,
        'params' => $params
    ];
}

/**
 * Fetches a paginated list of invoices for the current dealer.
 *
 * @param int $dealer_id The ID of the currently logged-in dealer.
 * @return array An array containing the list of invoices and pagination HTML.
 */
function get_dealer_invoices($dealer_id) {
    $conn = get_db_connection();
    $params = $_GET;
    $current_page = isset($params['page']) ? (int)$params['page'] : 1;
    $offset = ($current_page - 1) * ITEMS_PER_PAGE;

    // Base query to join invoices with their related orders
    $base_sql = "FROM invoices i JOIN orders o ON i.order_id = o.id WHERE o.dealer_id = ?";

    // Get total count for pagination
    $count_stmt = $conn->prepare("SELECT COUNT(i.id) " . $base_sql);
    $count_stmt->bind_param('i', $dealer_id);
    $count_stmt->execute();
    $total_invoices = $count_stmt->get_result()->fetch_row()[0];

    // Fetch the invoices for the current page
    $invoices = [];
    $sql = "SELECT i.id, i.invoice_number, i.amount, i.issue_date, i.due_date, i.status, o.id as order_id 
            " . $base_sql . " ORDER BY i.issue_date DESC LIMIT ? OFFSET ?";

    $stmt = $conn->prepare($sql);
    $items_per_page = ITEMS_PER_PAGE;
    $stmt->bind_param('iii', $dealer_id, $items_per_page, $offset);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $invoices[] = $row;
    }

    // Generate pagination links
    $pagination_html = generate_pagination_links($total_invoices, $current_page, '/invoices', $params);

    return [
        'invoices' => $invoices,
        'pagination' => $pagination_html
    ];
}
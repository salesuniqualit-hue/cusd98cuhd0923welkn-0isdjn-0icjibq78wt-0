<?php
// modules/customers/actions.php

/**
 * Fetches a paginated, sortable, and filterable list of customers.
 *
 * @param array $current_user The logged-in user's session data.
 * @return array An array containing the list of customers and view data.
 */
function get_customers_list_data($current_user) {
    $conn = get_db_connection();
    $params = $_GET;
    $current_page = isset($params['page']) ? (int)$params['page'] : 1;
    $offset = ($current_page - 1) * ITEMS_PER_PAGE;

    $is_admin_view = ($current_user['role'] === 'admin');

    // Whitelist for sortable columns
    $sortable_columns = ['name', 'email', 'phone', 'company_name'];
    $sort = isset($params['sort']) && in_array($params['sort'], $sortable_columns) ? $params['sort'] : 'name';
    $order = isset($params['order']) && in_array(strtolower($params['order']), ['asc', 'desc']) ? strtolower($params['order']) : 'asc';

    // Base query
    $base_sql = "FROM customers c ";
    if ($is_admin_view) {
        $base_sql .= "JOIN dealers d ON c.dealer_id = d.id ";
    }
    
    $where_clauses = [];
    $bind_params = [];
    $types = '';

    // Role-based initial filter
    if (!$is_admin_view) {
        $where_clauses[] = "c.dealer_id = ?";
        $bind_params[] = $current_user['dealer_id'];
        $types .= 'i';
    }

    // Add search filters
    if (!empty($params['filter_name'])) {
        $where_clauses[] = "c.name LIKE ?";
        $bind_params[] = '%' . $params['filter_name'] . '%';
        $types .= 's';
    }
    if (!empty($params['filter_email'])) {
        $where_clauses[] = "c.email LIKE ?";
        $bind_params[] = '%' . $params['filter_email'] . '%';
        $types .= 's';
    }
    if (!empty($params['filter_phone'])) {
        $where_clauses[] = "c.phone LIKE ?";
        $bind_params[] = '%' . $params['filter_phone'] . '%';
        $types .= 's';
    }
    if ($is_admin_view && !empty($params['filter_dealer'])) {
        $where_clauses[] = "d.company_name LIKE ?";
        $bind_params[] = '%' . $params['filter_dealer'] . '%';
        $types .= 's';
    }

    $where_sql = count($where_clauses) > 0 ? 'WHERE ' . implode(' AND ', $where_clauses) : '';
    $full_base_sql = $base_sql . $where_sql;

    // Count total matching customers
    $count_stmt = $conn->prepare("SELECT COUNT(c.id) " . $full_base_sql);
    if (count($bind_params) > 0) $count_stmt->bind_param($types, ...$bind_params);
    $count_stmt->execute();
    $total_customers = $count_stmt->get_result()->fetch_row()[0];

    // Fetch data for the current page
    $customers = [];
    $select_cols = $is_admin_view ? "c.id, c.name, c.email, c.phone, c.dealer_id, d.company_name" : "c.id, c.name, c.email, c.phone";
    $data_sql = "SELECT " . $select_cols . " " . $full_base_sql . " ORDER BY $sort $order LIMIT ? OFFSET ?";
    
    $stmt = $conn->prepare($data_sql);
    $all_bind_params = array_merge($bind_params, [ITEMS_PER_PAGE, $offset]);
    $all_types = $types . 'ii';
    if (count($all_bind_params) > 0) $stmt->bind_param($all_types, ...$all_bind_params);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $customers[] = $row;
    }

    $pagination_html = generate_pagination_links($total_customers, $current_page, '/customers', $params);

    return [
        'customers' => $customers,
        'pagination' => $pagination_html,
        'params' => $params,
        'is_admin_view' => $is_admin_view
    ];
}

/**
 * Fetches a single customer by ID, with a permission check.
 *
 * @param int $customer_id The ID of the customer to fetch.
 * @param array $current_user The logged-in user's session data.
 * @return array|null Customer data or null if not found or permission is denied.
 */
function get_customer_by_id($customer_id, $current_user) {
    $conn = get_db_connection();
    $sql = "SELECT id, name, email, phone, dealer_id FROM customers WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $customer_id);
    $stmt->execute();
    $customer = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$customer) {
        return null; // Not found
    }

    // Permission Check: Allow if admin, or if dealer_id matches the logged-in dealer.
    if ($current_user['role'] === 'admin' || $customer['dealer_id'] == $current_user['dealer_id']) {
        return $customer;
    }

    return null; // Permission denied
}

/**
 * Handles the creation of a new customer.
 *
 * @param array $data The $_POST data from the form.
 * @param array $current_user The logged-in user's session data.
 */
function handle_create_customer($data, $current_user) {
    $conn = get_db_connection();
    $name = trim($data['name']);
    $email = trim($data['email']);
    $phone = trim($data['phone']);

    // Determine the dealer_id based on the user's role.
    if ($current_user['role'] === 'admin') {
        $dealer_id = (int)$data['dealer_id'];
    } else {
        // If the user is a dealer, securely use their own dealer_id.
        $dealer_id = $current_user['dealer_id'];
    }

    if (empty($name) || empty($dealer_id)) {
        redirect('/customers/create?error=missing_fields');
        return;
    }

    $stmt = $conn->prepare("INSERT INTO customers (name, email, phone, dealer_id) VALUES (?, ?, ?, ?)");
    $stmt->bind_param('sssi', $name, $email, $phone, $dealer_id);

    if ($stmt->execute()) {
        redirect('/customers?success=customer_created');
    } else {
        redirect('/customers/create?error=' . urlencode($stmt->error));
    }
    $stmt->close();
}

/**
 * Handles updating an existing customer.
 *
 * @param int $customer_id The ID of the customer to update.
 * @param array $data The $_POST data from the form.
 * @param array $current_user The logged-in user's session data.
 */
function handle_update_customer($customer_id, $data, $current_user) {
    // get_customer_by_id includes a permission check.
    $customer = get_customer_by_id($customer_id, $current_user);
    if (!$customer) {
        redirect('/customers?error=permission_denied');
        return;
    }
    
    $conn = get_db_connection();
    $name = trim($data['name']);
    $email = trim($data['email']);
    $phone = trim($data['phone']);

    // Admin can change the dealer assignment.
    if ($current_user['role'] === 'admin') {
        $dealer_id = (int)$data['dealer_id'];
        $stmt = $conn->prepare("UPDATE customers SET name = ?, email = ?, phone = ?, dealer_id = ? WHERE id = ?");
        $stmt->bind_param('sssii', $name, $email, $phone, $dealer_id, $customer_id);
    } else {
        // Dealers cannot change the dealer assignment.
        $stmt = $conn->prepare("UPDATE customers SET name = ?, email = ?, phone = ? WHERE id = ?");
        $stmt->bind_param('sssi', $name, $email, $phone, $customer_id);
    }

    if ($stmt->execute()) {
        redirect('/customers?success=customer_updated');
    } else {
        redirect("/customers/{$customer_id}/edit?error=" . urlencode($stmt->error));
    }
    $stmt->close();
}

/**
 * Handles deleting a customer.
 *
 * @param int $customer_id The ID of the customer to delete.
 * @param array $current_user The logged-in user's session data.
 */
function handle_delete_customer($customer_id, $current_user) {
    // Permission check is implicit in this function call.
    $customer = get_customer_by_id($customer_id, $current_user);
    if (!$customer) {
        redirect('/customers?error=permission_denied');
        return;
    }

    $conn = get_db_connection();
    $stmt = $conn->prepare("DELETE FROM customers WHERE id = ?");
    $stmt->bind_param('i', $customer_id);

    if ($stmt->execute()) {
        redirect('/customers?success=customer_deleted');
    } else {
        redirect('/customers?error=' . urlencode($stmt->error));
    }
    $stmt->close();
}
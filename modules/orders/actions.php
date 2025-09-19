<?php
// modules/orders/actions.php

// --- ADD THESE TWO LINES ---
require_once __DIR__ . '/../dealers/actions.php';
require_once __DIR__ . '/../skus/actions.php';
// --- END OF FIX ---
require_once __DIR__ . '/../referrers/actions.php';

/**
 * [Private] Generates a unique, formatted order number.
 * Format: YY<order_id>MM<daily_incremental_number>DD
 *
 * @param mysqli $conn The database connection.
 * @param int $order_id The newly inserted order ID.
 * @return string The generated order number.
 */
function _generate_order_number($conn, $order_id) {
    // Get the count of orders created today
    $today = date('Y-m-d');
    $stmt = $conn->prepare("SELECT COUNT(id) as daily_count FROM orders WHERE DATE(order_date) = ? AND id <= ?");
    $stmt->bind_param('si', $today, $order_id);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    $daily_count = $result ? (int)$result['daily_count'] : 1;
    $stmt->close();

    // Format the components
    $year = date('y');
    $month = str_pad(date('m'), 2, '0', STR_PAD_LEFT);
    $day = str_pad(date('d'), 2, '0', STR_PAD_LEFT);
    $incremental = str_pad($daily_count, 4, '0', STR_PAD_LEFT);
    $order_id_pad = str_pad($order_id, 10, '0', STR_PAD_LEFT);

    return "{$year}{$order_id_pad}{$month}{$incremental}{$day}";
}

/**
 * Fetches the initial data needed to populate the "Create Order" form.
 *
 * @param array $current_user The logged-in user's session data.
 * @return array Data for the form's dropdown menus.
 */
function get_order_form_initial_data($current_user) {
    $data = [
        'dealers' => [],
        'customers' => [],
        'skus' => [],
        'referrers' => [] // Add referrers key
    ];

    if ($current_user['role'] === 'admin') {
        // Admins get a list of all dealers.
        $data['dealers'] = get_all_dealers_for_dropdown();
    } else {
        // Dealers get a list of their own customers.
        $conn = get_db_connection();
        $stmt = $conn->prepare("SELECT id, name FROM customers WHERE dealer_id = ? ORDER BY name ASC");
        $stmt->bind_param('i', $current_user['dealer_id']);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $data['customers'][] = $row;
        }
        $stmt->close();
    }
    
    // Everyone gets a list of all SKUs.
    $data['skus'] = get_all_skus_for_dropdown();
    
    // Everyone gets a list of their available referrers.
    $data['referrers'] = get_all_referrers_for_dropdown($current_user);

    return $data;
}


/**
 * Fetches the correct price for a given SKU and Dealer.
 * It prioritizes the dealer-specific price list over the standard price.
 *
 * @param int $dealer_id
 * @param int $sku_id
 * @param string $uom 'yearly' or 'perpetual'
 * @return float|null The calculated price or null if not found.
 */
function get_order_price($dealer_id, $sku_id, $uom) {
    $conn = get_db_connection();
    $price = null;
    $price_column = ($uom === 'yearly') ? 'price_yearly' : 'price_perpetual';

    // 1. Check for a dealer-specific price first.
    $sql_dealer = "SELECT {$price_column} FROM dealer_price_lists 
                   WHERE dealer_id = ? AND sku_id = ? AND applicable_date <= CURDATE() 
                   ORDER BY applicable_date DESC LIMIT 1";
    $stmt = $conn->prepare($sql_dealer);
    $stmt->bind_param('ii', $dealer_id, $sku_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $price = $result->fetch_assoc()[$price_column];
    }
    $stmt->close();

    // 2. If no dealer price is found, fall back to the standard price.
    if ($price === null) {
        $sql_standard = "SELECT {$price_column} FROM sku_standard_prices 
                         WHERE sku_id = ? AND applicable_date <= CURDATE() 
                         ORDER BY applicable_date DESC LIMIT 1";
        $stmt = $conn->prepare($sql_standard);
        $stmt->bind_param('i', $sku_id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $price = $result->fetch_assoc()[$price_column];
        }
        $stmt->close();
    }

    return $price;
}

/**
 * Handles the creation of a new order.
 */
function handle_create_order($data, $current_user) {
    $conn = get_db_connection();
    $conn->begin_transaction();

    try {
        $dealer_id = ($current_user['role'] === 'admin') ? (int)$data['dealer_id'] : $current_user['dealer_id'];
        $customer_id = (int)$data['customer_id'];
        $sku_id = (int)$data['sku_id'];
        $sku_version_id = (int)$data['sku_version_id'];
        $uom = $data['uom'];

        $referrer_id = !empty($data['referrer_id']) ? (int)$data['referrer_id'] : null;

        $rate = get_order_price($dealer_id, $sku_id, $uom);

        if ($rate === null) {
            throw new Exception('price_not_found');
        }

        $order_date = date('Y-m-d');
        $status = AUTO_PROCESS_ORDERS ? 'processed' : 'pending';

        // --- UPDATE SQL AND BIND PARAMS ---
        $stmt = $conn->prepare("INSERT INTO orders (dealer_id, customer_id, sku_id, sku_version_id, uom, rate, order_date, status, referrer_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param('iiiisdssi', $dealer_id, $customer_id, $sku_id, $sku_version_id, $uom, $rate, $order_date, $status, $referrer_id);
        
        if (!$stmt->execute()) {
            throw new Exception($stmt->error);
        }
        
        $order_id = $conn->insert_id;
        
        // Generate and save the new order number
        $order_number = _generate_order_number($conn, $order_id);
        $stmt_update = $conn->prepare("UPDATE orders SET order_number = ? WHERE id = ?");
        $stmt_update->bind_param('si', $order_number, $order_id);
        
        if (!$stmt_update->execute()) {
            throw new Exception($stmt_update->error);
        }

        $conn->commit();
        redirect('/orders?success=order_created');

    } catch (Exception $e) {
        $conn->rollback();
        redirect('/orders/create?error=' . urlencode($e->getMessage()));
    }
}

/**
 * Handles processing an order (changing status from pending to processed).
 *
 * @param int $order_id The ID of the order to process.
 */
function handle_process_order($order_id) {
    $conn = get_db_connection();

    // Prepare the statement to update the order status to 'processed'
    // It also ensures we only update orders that are currently 'pending'
    $stmt = $conn->prepare("UPDATE orders SET status = 'processed' WHERE id = ? AND status = 'pending'");
    $stmt->bind_param('i', $order_id);

    if ($stmt->execute()) {
        // Check if any row was actually updated to prevent incorrect success messages
        if ($stmt->affected_rows > 0) {
            redirect('/orders?success=order_processed_successfully');
        } else {
            redirect('/orders?error=order_was_not_pending_or_not_found');
        }
    } else {
        // Handle potential SQL errors
        redirect('/orders?error=' . urlencode($stmt->error));
    }
    $stmt->close();
}

/**
 * Fetches a paginated, sortable, and filterable list of orders.
 *
 * @param array $current_user The logged-in user's session data.
 * @return array An array containing the list of orders and view data.
 */
function get_orders_list_data($current_user) {
    $conn = get_db_connection();
    $params = $_GET;
    $current_page = isset($params['page']) ? (int)$params['page'] : 1;
    $offset = ($current_page - 1) * ITEMS_PER_PAGE;

    $is_admin_view = ($current_user['role'] === 'admin');

    $sortable_columns = ['order_number', 'order_date', 'dealer_name', 'customer_name', 'sku_name', 'rate', 'status'];
    $sort = isset($params['sort']) && in_array($params['sort'], $sortable_columns) ? $params['sort'] : 'order_date';
    $order = isset($params['order']) && in_array(strtolower($params['order']), ['asc', 'desc']) ? strtolower($params['order']) : 'desc';

    $base_sql = "FROM orders o
                 JOIN customers c ON o.customer_id = c.id
                 JOIN skus s ON o.sku_id = s.id
                 JOIN dealers d ON o.dealer_id = d.id ";

    $where_clauses = [];
    $bind_params = [];
    $types = '';

    if (!$is_admin_view) {
        $where_clauses[] = "o.dealer_id = ?";
        $bind_params[] = $current_user['dealer_id'];
        $types .= 'i';
    }

    // --- ADD THIS FILTER LOGIC ---
    if (!empty($params['filter_order_number'])) {
        $where_clauses[] = "o.order_number LIKE ?";
        $bind_params[] = '%' . $params['filter_order_number'] . '%';
        $types .= 's';
    }
    // --- END OF ADDITION ---
    
    if ($is_admin_view && !empty($params['filter_dealer'])) {
        $where_clauses[] = "d.company_name LIKE ?";
        $bind_params[] = '%' . $params['filter_dealer'] . '%';
        $types .= 's';
    }

    // Add search filters
    if ($is_admin_view && !empty($params['filter_dealer'])) {
        $where_clauses[] = "d.company_name LIKE ?";
        $bind_params[] = '%' . $params['filter_dealer'] . '%';
        $types .= 's';
    }
    if (!empty($params['filter_customer'])) {
        $where_clauses[] = "c.name LIKE ?";
        $bind_params[] = '%' . $params['filter_customer'] . '%';
        $types .= 's';
    }
    if (!empty($params['filter_sku'])) {
        $where_clauses[] = "s.name LIKE ?";
        $bind_params[] = '%' . $params['filter_sku'] . '%';
        $types .= 's';
    }
    if (isset($params['filter_status']) && $params['filter_status'] !== '') {
        $where_clauses[] = "o.status = ?";
        $bind_params[] = $params['filter_status'];
        $types .= 's';
    }
    // Date range filter
    if (!empty($params['filter_date_from'])) {
        $where_clauses[] = "o.order_date >= ?";
        $bind_params[] = $params['filter_date_from'];
        $types .= 's';
    }
    if (!empty($params['filter_date_to'])) {
        $where_clauses[] = "o.order_date <= ?";
        $bind_params[] = $params['filter_date_to'];
        $types .= 's';
    }
    // --- NEW: Filter by remarks ---
    if (!empty($params['filter_remarks'])) {
        $where_clauses[] = "o.remarks LIKE ?";
        $bind_params[] = '%' . $params['filter_remarks'] . '%';
        $types .= 's';
    }

    $where_sql = count($where_clauses) > 0 ? 'WHERE ' . implode(' AND ', $where_clauses) : '';
    $full_base_sql = $base_sql . $where_sql;

    // Count total matching orders
    $count_stmt = $conn->prepare("SELECT COUNT(o.id) " . $full_base_sql);
    if (count($bind_params) > 0) $count_stmt->bind_param($types, ...$bind_params);
    $count_stmt->execute();
    $total_orders = $count_stmt->get_result()->fetch_row()[0];

    // Fetch data for the current page
    $orders = [];
    // --- THIS IS THE CORRECTED QUERY ---
    $data_sql = "SELECT o.id, o.order_number, o.order_date, o.rate, o.status, c.name as customer_name, s.name as sku_name, d.company_name as dealer_name
                 " . $full_base_sql . " ORDER BY $sort $order LIMIT ? OFFSET ?";

    $stmt = $conn->prepare($data_sql);
    $all_bind_params = array_merge($bind_params, [ITEMS_PER_PAGE, $offset]);
    $all_types = $types . 'ii';
    if (count($all_bind_params) > 0) $stmt->bind_param($all_types, ...$all_bind_params);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $orders[] = $row;
    }

    $pagination_html = generate_pagination_links($total_orders, $current_page, '/orders', $params);

    return [
        'orders' => $orders,
        'pagination' => $pagination_html,
        'params' => $params,
        'is_admin_view' => $is_admin_view
    ];
}

/**
 * Handles cancelling an order.
 * An order can only be cancelled if no trial or subscription has been activated for it.
 *
 * @param int $order_id The ID of the order to cancel.
 */
function handle_cancel_order($order_id) {
    $conn = get_db_connection();
    $conn->begin_transaction();

    try {
        // Safety Check: See if a subscription or trial already exists for this order.
        $stmt_check = $conn->prepare("SELECT id FROM subscriptions WHERE order_id = ?");
        $stmt_check->bind_param('i', $order_id);
        $stmt_check->execute();
        $subscription_exists = $stmt_check->get_result()->num_rows > 0;
        $stmt_check->close();

        if ($subscription_exists) {
            throw new Exception('Cannot cancel an order that already has an active trial or subscription.');
        }

        // Proceed with cancellation
        $stmt_cancel = $conn->prepare("UPDATE orders SET status = 'cancelled' WHERE id = ? AND status != 'cancelled'");
        $stmt_cancel->bind_param('i', $order_id);
        $stmt_cancel->execute();

        if ($stmt_cancel->affected_rows > 0) {
            $conn->commit();
            redirect('/orders?success=order_cancelled');
        } else {
            // This case handles if the order was already cancelled or not found
            $conn->rollback();
            redirect('/orders?error=order_could_not_be_cancelled');
        }
        $stmt_cancel->close();

    } catch (Exception $e) {
        $conn->rollback();
        redirect('/orders?error=' . urlencode($e->getMessage()));
    }
}

/**
 * Fetches customer details and their serial numbers for a given order.
 *
 * @param int $order_id The ID of the order.
 * @param array $current_user The logged-in user for permission checks.
 * @return array|null The customer details or null if not found/permitted.
 */
function get_order_details_for_trial_request($order_id, $current_user) {
    $conn = get_db_connection();

    // First, get the customer_id from the order, ensuring the dealer owns it.
    $order_sql = "SELECT customer_id FROM orders WHERE id = ?";
    if ($current_user['role'] !== 'admin') {
        $order_sql .= " AND dealer_id = " . (int)$current_user['dealer_id'];
    }
    $stmt_order = $conn->prepare($order_sql);
    $stmt_order->bind_param('i', $order_id);
    $stmt_order->execute();
    $order_result = $stmt_order->get_result()->fetch_assoc();

    if (!$order_result) {
        return null; // Order not found or permission denied
    }
    $customer_id = $order_result['customer_id'];

    // Now, get customer name and their serials
    $customer_stmt = $conn->prepare("SELECT name FROM customers WHERE id = ?");
    $customer_stmt->bind_param('i', $customer_id);
    $customer_stmt->execute();
    $customer_name = $customer_stmt->get_result()->fetch_assoc()['name'];

    $serials_stmt = $conn->prepare("SELECT id, serial_number FROM customer_serials WHERE customer_id = ? ORDER BY serial_number ASC");
    $serials_stmt->bind_param('i', $customer_id);
    $serials_stmt->execute();
    $serials = $serials_stmt->get_result()->fetch_all(MYSQLI_ASSOC);

    return [
        'customer_name' => $customer_name,
        'serials' => $serials
    ];
}

/**
 * Fetches all details for a single order with permission checks.
 *
 * @param int $order_id The ID of the order to fetch.
 * @param array $current_user The currently logged-in user.
 * @return array|null The order details or null if not found or permission denied.
 */
function get_order_details($order_id, $current_user) {
    $conn = get_db_connection();

    // The main query remains largely the same
    $sql = "SELECT 
                o.id, o.order_number, o.order_date, o.uom, o.rate, o.status, o.dealer_id, o.customer_id, o.sku_id,
                c.name AS customer_name,
                d.company_name AS dealer_name,
                s.name AS sku_name, s.trial_period AS max_trial_days,
                sv.version_number AS sku_version,
                ref.name AS referrer_name,
                cs.id as customer_serial_id, cs.serial_number
            FROM orders o
            JOIN customers c ON o.customer_id = c.id
            JOIN dealers d ON o.dealer_id = d.id
            JOIN skus s ON o.sku_id = s.id
            JOIN sku_versions sv ON o.sku_version_id = sv.id
            LEFT JOIN customer_serials cs ON c.id = cs.customer_id
            LEFT JOIN referrers ref ON o.referrer_id = ref.id
            WHERE o.id = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $order_id);
    $stmt->execute();
    $order = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$order) { return null; }
    if ($current_user['role'] !== 'admin' && $order['dealer_id'] != $current_user['dealer_id']) { return null; }

    // --- FIX IS HERE: Refined History Query with GROUP BY ---
    $order['history'] = [];
    if ($order['customer_serial_id']) {
        $history_sql = "SELECT 
                            s.id, s.type, s.start_date, s.end_date, 
                            MAX(r.remarks) as remarks, 
                            MAX(r.admin_remarks) as admin_remarks, 
                            MAX(u.name) as requested_by
                        FROM subscriptions s
                        LEFT JOIN requests r ON s.order_id = r.order_id 
                           AND s.customer_serial_id = r.customer_serial_id
                           AND (
                               (s.type = 'trial' AND r.type = 'trial') OR
                               (s.type = 'paid' AND r.type IN ('subscribe', 'renew'))
                           )
                        LEFT JOIN users u ON r.user_id = u.id
                        WHERE s.customer_serial_id = ? AND s.sku_id = ?
                        GROUP BY s.id
                        ORDER BY s.start_date DESC";
        $history_stmt = $conn->prepare($history_sql);
        $history_stmt->bind_param('ii', $order['customer_serial_id'], $order['sku_id']);
        $history_stmt->execute();
        $order['history'] = $history_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
    // --- END OF FIX ---

    // The existing subscription_info logic now correctly gets the LATEST event
    $order['subscription_info'] = $order['history'][0] ?? null;
    
    if ($order['subscription_info']) {
        $sub_info = &$order['subscription_info'];
        $sub_info['is_expired'] = $sub_info['end_date'] < date('Y-m-d');
        $renewal_date = date('Y-m-d', strtotime('-' . SUBSCRIPTION_RENEWAL_WINDOW_DAYS . ' days', strtotime($sub_info['end_date'])));
        $sub_info['is_expiring_soon'] = !$sub_info['is_expired'] && date('Y-m-d') >= $renewal_date;

        if ($sub_info['type'] === 'trial') {
            $used_days = 0;
            foreach($order['history'] as $record) {
                if ($record['type'] === 'trial') {
                    $start = new DateTime($record['start_date']);
                    $end = new DateTime($record['end_date']);
                    $used_days += $end->diff($start)->days;
                }
            }
            $sub_info['remaining_trial_days'] = max(0, (int)$order['max_trial_days'] - $used_days);
        }
    }

    return $order;
}
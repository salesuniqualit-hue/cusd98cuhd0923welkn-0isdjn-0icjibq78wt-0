<?php
// modules/requests/actions.php

/**
 * Fetches a paginated, sortable, and filterable list of all requests.
 *
 * @param array $current_user The logged-in user's session data.
 * @return array A list of request records and pagination data.
 */
function get_all_requests($current_user) {
    $conn = get_db_connection();
    $params = $_GET;
    $current_page = isset($params['page']) ? (int)$params['page'] : 1;
    $offset = ($current_page - 1) * ITEMS_PER_PAGE;

    $is_admin_view = ($current_user['role'] === 'admin');

    // Whitelist for sortable columns
    $sortable_columns = ['created_at', 'order_number', 'customer_name', 'sku_name', 'type', 'status'];
    $sort = isset($params['sort']) && in_array($params['sort'], $sortable_columns) ? $params['sort'] : 'created_at';
    $order = isset($params['order']) && in_array(strtolower($params['order']), ['asc', 'desc']) ? strtolower($params['order']) : 'desc';

    // Base query
    $base_sql = "FROM requests r
                 JOIN users u ON r.user_id = u.id
                 JOIN customer_serials cs ON r.customer_serial_id = cs.id
                 JOIN customers c ON cs.customer_id = c.id
                 JOIN orders o ON r.order_id = o.id
                 JOIN skus s ON o.sku_id = s.id ";

    $where_clauses = [];
    $bind_params = [];
    $types = '';

    // Role-based initial filter
    if (!$is_admin_view) {
        $where_clauses[] = "u.dealer_id = ?";
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

    // Add search filters
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
    if (!empty($params['filter_type'])) {
        $where_clauses[] = "r.type = ?";
        $bind_params[] = $params['filter_type'];
        $types .= 's';
    }
    if (!empty($params['filter_status'])) {
        $where_clauses[] = "r.status = ?";
        $bind_params[] = $params['filter_status'];
        $types .= 's';
    }
    if (!empty($params['filter_date_from'])) {
        $where_clauses[] = "r.created_at >= ?";
        $bind_params[] = $params['filter_date_from'];
        $types .= 's';
    }
    if (!empty($params['filter_date_to'])) {
        $where_clauses[] = "r.created_at <= ?";
        $bind_params[] = $params['filter_date_to'];
        $types .= 's';
    }

    $where_sql = count($where_clauses) > 0 ? 'WHERE ' . implode(' AND ', $where_clauses) : '';
    $full_base_sql = $base_sql . $where_sql;

    // Count total matching requests
    $count_stmt = $conn->prepare("SELECT COUNT(r.id) " . $full_base_sql);
    if (count($bind_params) > 0) $count_stmt->bind_param($types, ...$bind_params);
    $count_stmt->execute();
    $total_requests = $count_stmt->get_result()->fetch_row()[0];

    // Fetch data for the current page
    $requests = [];
    $data_sql = "SELECT r.id, r.created_at, r.type, r.status, c.name as customer_name, s.name as sku_name, o.order_number
                 " . $full_base_sql . " ORDER BY $sort $order LIMIT ? OFFSET ?";

    $stmt = $conn->prepare($data_sql);
    $all_bind_params = array_merge($bind_params, [ITEMS_PER_PAGE, $offset]);
    $all_types = $types . 'ii';
    if (count($all_bind_params) > 0) $stmt->bind_param($all_types, ...$all_bind_params);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $requests[] = $row;
    }

    $pagination_html = generate_pagination_links($total_requests, $current_page, '/requests', $params);

    return [
        'requests' => $requests,
        'pagination' => $pagination_html,
        'params' => $params,
        'is_admin_view' => $is_admin_view
    ];
}

/**
 * Fetches a single request's details with permission checks.
 */
function get_request_by_id($request_id, $current_user) {
    $conn = get_db_connection();
    
    $sql = "SELECT r.*, u.name as requested_by, d.company_name as dealer_name, c.name as customer_name, 
                   cs.serial_number, s.name as sku_name, o.id as order_id, o.order_number, o.dealer_id, o.sku_id,
                   admin.name as processed_by_name
            FROM requests r
            JOIN users u ON r.user_id = u.id
            JOIN dealers d ON u.dealer_id = d.id
            JOIN customer_serials cs ON r.customer_serial_id = cs.id
            JOIN customers c ON cs.customer_id = c.id
            JOIN orders o ON r.order_id = o.id
            JOIN skus s ON o.sku_id = s.id
            LEFT JOIN users admin ON r.processed_by = admin.id
            WHERE r.id = ?";
            
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $request_id);
    $stmt->execute();
    $request = $stmt->get_result()->fetch_assoc();

    if (!$request) {
        return null; // Not found
    }

    // Security Check
    if ($current_user['role'] !== 'admin' && $request['dealer_id'] != $current_user['dealer_id']) {
        return null; // Permission Denied
    }
    
    // --- THIS LOGIC IS UPDATED ---
    // Logic to display "System" as the processor for auto-approved requests.
    if ($request['processed_by'] == 1 && $request['admin_remarks'] === 'Checked & approved by system') {
        $request['processed_by_name'] = 'System';
    }
    // --- END OF UPDATE ---

    return $request;
}

/**
 * Fetches data for the "New Trial Request" page.
 * It finds orders that do not yet have an associated subscription.
 */
function get_data_for_trial_request($current_user) {
    $conn = get_db_connection();
    $dealer_id = $current_user['dealer_id'];
    $sql = "SELECT o.id, o.order_number, o.order_date, c.name as customer_name, s.name as sku_name
            FROM orders o
            JOIN customers c ON o.customer_id = c.id
            JOIN skus s ON o.sku_id = s.id
            LEFT JOIN subscriptions sub ON o.id = sub.order_id
            WHERE o.dealer_id = ? AND sub.id IS NULL AND o.status = 'processed'
            ORDER BY o.order_date DESC";
            
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $dealer_id);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

/**
 * Fetches data for the "New Subscription Request" page.
 * It finds active 'trial' subscriptions.
 */
function get_data_for_subscribe_request($order_id, $serial_id, $current_user) {
    $conn = get_db_connection();
    
    // Security and validation query
    $sql = "SELECT 
                o.id as order_id, o.order_number, c.name as customer_name, s.name as sku_name, cs.serial_number
            FROM orders o
            JOIN customers c ON o.customer_id = c.id
            JOIN skus s ON o.sku_id = s.id
            JOIN customer_serials cs ON c.id = cs.customer_id
            WHERE o.id = ? AND cs.id = ? AND o.dealer_id = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('iii', $order_id, $serial_id, $current_user['dealer_id']);
    $stmt->execute();
    $data = $stmt->get_result()->fetch_assoc();

    if (!$data) {
        return null; // The order/serial doesn't exist or doesn't belong to the dealer
    }

    // Check if a 'paid' subscription already exists for this serial/SKU combination
    $stmt_check = $conn->prepare("SELECT id FROM subscriptions WHERE customer_serial_id = ? AND sku_id = ? AND type = 'paid'");
    $sku_id = $conn->query("SELECT sku_id FROM orders WHERE id = $order_id")->fetch_assoc()['sku_id'];
    $stmt_check->bind_param('ii', $serial_id, $sku_id);
    $stmt_check->execute();
    if ($stmt_check->get_result()->num_rows > 0) {
        return null; // Already subscribed
    }

    return $data;
}

/**
 * Fetches data for the "New Renewal Request" page.
 * It finds 'paid' subscriptions that have expired or are expiring soon.
 */
function get_data_for_renew_request($current_user) {
    $conn = get_db_connection();
    $dealer_id = $current_user['dealer_id'];
    $sql = "SELECT o.id as order_id, o.order_number, sub.customer_serial_id, c.name as customer_name, s.name as sku_name, sub.end_date
            FROM subscriptions sub
            JOIN orders o ON sub.order_id = o.id
            JOIN customers c ON o.customer_id = c.id
            JOIN skus s ON o.sku_id = s.id
            WHERE o.dealer_id = ? AND sub.type = 'paid' AND sub.end_date <= DATE_ADD(CURDATE(), INTERVAL 30 DAY)
            ORDER BY sub.end_date ASC";
            
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $dealer_id);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}


/**
 * [Private] Activates a trial subscription for a given request.
 * This is a helper function to avoid code duplication.
 *
 * @param mysqli $conn The database connection.
 * @param array $request_details An array containing request data (order_id, customer_serial_id, validity_days, sku_id).
 * @return bool True on success, false on failure.
 * @throws Exception
 */
function _activate_trial_subscription($conn, $request_details) {
    $start_date = date('Y-m-d');
    $end_date = date('Y-m-d', strtotime("+" . $request_details['validity_days'] . " days"));
    $sub_type = 'trial';

    $stmt_sub = $conn->prepare(
        "INSERT INTO subscriptions (customer_serial_id, order_id, sku_id, start_date, end_date, type) VALUES (?, ?, ?, ?, ?, ?)"
    );
    $stmt_sub->bind_param(
        'iiisss',
        $request_details['customer_serial_id'],
        $request_details['order_id'],
        $request_details['sku_id'],
        $start_date,
        $end_date,
        $sub_type
    );

    $success = $stmt_sub->execute();
    $stmt_sub->close();
    
    if (!$success) {
        throw new Exception("Failed to create the subscription record.");
    }
    return true;
}

/**
 * Handles the creation of a new request (trial, subscribe, or renew).
 */
function handle_create_request($data, $current_user, $type) {
    $conn = get_db_connection();
    
    $redirect_with_error = function($error_message, $input_data) use ($type) {
        $_SESSION['flash_error'] = $error_message;
        $_SESSION['flash_form_data'] = $input_data;
        redirect('/requests/' . $type);
    };

    $order_id = (int)$data['order_id'];
    $customer_serial_id = (int)$data['customer_serial_id'];
    $validity_days = isset($data['validity_days']) ? (int)$data['validity_days'] : null;
    $remarks = $data['remarks'];

    // --- ADDED: Capture payment details ---
    $payment_date = $data['payment_date'] ?? null;
    $payment_reference = $data['payment_reference'] ?? null;

    // --- NEW VALIDATION: Check for duplicate requests ---
    if ($type !== 'trial') { // Only check for duplicates on subscribe/renew
        $stmt_check_duplicate = $conn->prepare(
            "SELECT id FROM requests WHERE order_id = ? AND customer_serial_id = ? AND type = ?"
        );
        $stmt_check_duplicate->bind_param('iis', $order_id, $customer_serial_id, $type);
        $stmt_check_duplicate->execute();
        $duplicate_exists = $stmt_check_duplicate->get_result()->num_rows > 0;
        $stmt_check_duplicate->close();

        if ($duplicate_exists) {
            $redirect_with_error("A '{$type}' request has already been submitted for this order and serial number.", $data);
            return;
        }
    }


    if ($type === 'trial') {
        if (empty($validity_days) || (int)$validity_days <= 0) {
            $redirect_with_error('Trial validity must be a positive number.', $data);
            return;
        }

        $stmt_sku = $conn->prepare("SELECT o.sku_id, s.trial_period FROM orders o JOIN skus s ON o.sku_id = s.id WHERE o.id = ?");
        $stmt_sku->bind_param('i', $order_id);
        $stmt_sku->execute();
        $order_and_sku_data = $stmt_sku->get_result()->fetch_assoc();
        $stmt_sku->close();

        if (!$order_and_sku_data) {
            $redirect_with_error('Invalid order selected.', $data);
            return;
        }
        $sku_id = $order_and_sku_data['sku_id'];
        $max_trial_days = (int)$order_and_sku_data['trial_period'];

        $stmt_used = $conn->prepare(
            "SELECT SUM(total_days) as total_used_or_requested_days FROM (
                SELECT SUM(DATEDIFF(end_date, start_date)) as total_days 
                FROM subscriptions 
                WHERE customer_serial_id = ? AND sku_id = ? AND type = 'trial'
                UNION ALL
                SELECT SUM(validity_days) as total_days
                FROM requests
                WHERE customer_serial_id = ? AND order_id IN (SELECT id FROM orders WHERE sku_id = ?)
                AND type = 'trial' AND status = 'pending'
            ) AS combined_trials"
        );
        $stmt_used->bind_param('iiii', $customer_serial_id, $sku_id, $customer_serial_id, $sku_id);
        $stmt_used->execute();
        $used_days_data = $stmt_used->get_result()->fetch_assoc();
        $stmt_used->close();
        $used_days = $used_days_data ? (int)$used_days_data['total_used_or_requested_days'] : 0;
        
        $remaining_days = $max_trial_days - $used_days;
        if ((int)$validity_days > $remaining_days) {
            $error_message = "Requested trial of {$validity_days} days exceeds the available {$remaining_days} days. (Maximum: {$max_trial_days}, Already Used or Pending: {$used_days})";
            $data['validity_days'] = ''; 
            $redirect_with_error($error_message, $data);
            return;
        }

        $conn->begin_transaction();
        try {
            $status = AUTO_APPROVE_TRIALS ? 'approved' : 'pending';
            $processed_by = AUTO_APPROVE_TRIALS ? 1 : null; 
            $processed_at = AUTO_APPROVE_TRIALS ? date('Y-m-d H:i:s') : null;
            
            // --- THIS IS THE UPDATED REMARK ---
            $admin_remarks = AUTO_APPROVE_TRIALS ? 'Checked & approved by system' : null;
            // --- END OF UPDATE ---

            $stmt = $conn->prepare(
                "INSERT INTO requests (user_id, order_id, customer_serial_id, type, validity_days, remarks, status, processed_by, processed_at, admin_remarks) 
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
            );
            $stmt->bind_param('iiisssssss', $current_user['id'], $order_id, $customer_serial_id, $type, $validity_days, $remarks, $status, $processed_by, $processed_at, $admin_remarks);
            
            if (!$stmt->execute()) {
                throw new Exception("Database error creating request: " . $stmt->error);
            }

            if (AUTO_APPROVE_TRIALS) {
                $request_details = ['order_id' => $order_id, 'customer_serial_id' => $customer_serial_id, 'validity_days' => $validity_days, 'sku_id' => $sku_id];
                _activate_trial_subscription($conn, $request_details);
            }
            
            $conn->commit();
            $success_message = AUTO_APPROVE_TRIALS ? 'trial_activated_successfully' : 'request_submitted';
            redirect('/requests?success=' . $success_message);

        } catch (Exception $e) {
            $conn->rollback();
            $data['validity_days'] = '';
            $redirect_with_error($e->getMessage(), $data);
            return;
        }

    } else {
        if (empty($payment_date) || empty($payment_reference)) {
            $redirect_with_error('Payment Date and Payment Reference are mandatory for subscriptions and renewals.', $data);
            return;
        }
        
        $stmt = $conn->prepare("INSERT INTO requests (user_id, order_id, customer_serial_id, type, validity_days, remarks) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param('iiisis', $current_user['id'], $order_id, $customer_serial_id, $type, $validity_days, $remarks);
        
        if ($stmt->execute()) {
            redirect('/requests?success=request_submitted');
        } else {
            $redirect_with_error(urlencode($stmt->error), $data);
        }
    }
}

/**
 * Handles the admin's processing of a request (approve/reject).
 */
function handle_process_request($request_id, $data, $current_user) {
    $conn = get_db_connection();
    $conn->begin_transaction();

    try {
        $request = get_request_by_id($request_id, $current_user);
        if (!$request) {
            throw new Exception("Request not found.");
        }

        $status = $data['status'] ?? null;
        $admin_remarks = $data['remarks'] ?? '';
        $processed_by = $current_user['id'];

        if (empty($status)) {
            throw new Exception("Status was not provided. Please select Approve or Reject.");
        }
        
        $stmt_req = $conn->prepare("UPDATE requests SET status = ?, admin_remarks = ?, processed_by = ?, processed_at = NOW() WHERE id = ?");
        $stmt_req->bind_param('ssii', $status, $admin_remarks, $processed_by, $request_id);
        
        if (!$stmt_req->execute()) {
             throw new Exception("Database error updating request: " . $stmt_req->error);
        }

        if ($status === 'approved') {
            $sku_info_stmt = $conn->prepare("SELECT subscription_period, is_perpetual FROM skus WHERE id = ?");
            $sku_info_stmt->bind_param('i', $request['sku_id']);
            $sku_info_stmt->execute();
            $sku_info = $sku_info_stmt->get_result()->fetch_assoc();
            $sku_info_stmt->close();

            if (!$sku_info) {
                throw new Exception("SKU information not found for the requested product.");
            }

            if ($request['type'] === 'trial') {
                $request_details = [
                    'order_id' => $request['order_id'],
                    'customer_serial_id' => $request['customer_serial_id'],
                    'validity_days' => $request['validity_days'],
                    'sku_id' => $request['sku_id']
                ];
                _activate_trial_subscription($conn, $request_details);
            } 
            // --- NEW LOGIC FOR SUBSCRIBE AND RENEW ---
            elseif ($request['type'] === 'subscribe' || $request['type'] === 'renew') {
                
                // Find the previous subscription (trial or paid) to determine the new start date
                $prev_sub_stmt = $conn->prepare(
                    "SELECT end_date FROM subscriptions 
                     WHERE customer_serial_id = ? AND sku_id = ? 
                     ORDER BY end_date DESC LIMIT 1"
                );
                $prev_sub_stmt->bind_param('ii', $request['customer_serial_id'], $request['sku_id']);
                $prev_sub_stmt->execute();
                $prev_sub = $prev_sub_stmt->get_result()->fetch_assoc();
                $prev_sub_stmt->close();
                
                // The new subscription starts the day after the previous one ends, or today if none exists.
                $start_date = $prev_sub ? date('Y-m-d', strtotime($prev_sub['end_date'] . ' +1 day')) : date('Y-m-d');

                if ($sku_info['is_perpetual']) {
                    $end_date = null; // Perpetual subscriptions have no end date
                } else {
                    $subscription_days = (int)$sku_info['subscription_period'];
                    $end_date = date('Y-m-d', strtotime($start_date . " +{$subscription_days} days"));
                }
                
                // Deactivate all previous subscriptions for this SKU and serial
                $deactivate_stmt = $conn->prepare("UPDATE subscriptions SET is_active = 0 WHERE customer_serial_id = ? AND sku_id = ?");
                $deactivate_stmt->bind_param('ii', $request['customer_serial_id'], $request['sku_id']);
                $deactivate_stmt->execute();
                $deactivate_stmt->close();

                // Insert the new 'paid' subscription record
                $stmt_sub = $conn->prepare(
                    "INSERT INTO subscriptions (customer_serial_id, order_id, sku_id, start_date, end_date, type, is_active) 
                     VALUES (?, ?, ?, ?, ?, 'paid', 1)"
                );
                $stmt_sub->bind_param('iiiss', $request['customer_serial_id'], $request['order_id'], $request['sku_id'], $start_date, $end_date);
                
                if (!$stmt_sub->execute()) {
                    throw new Exception("Failed to create the new subscription record.");
                }
                $stmt_sub->close();
            }
            // --- END OF NEW LOGIC ---
        }

        $conn->commit();
        $_SESSION['flash_success'] = 'Request processed successfully.';
        redirect('/requests');

    } catch (Exception $e) {
        $conn->rollback();
        $_SESSION['flash_error'] = $e->getMessage();
        redirect("/requests/{$request_id}/review");
    }
}
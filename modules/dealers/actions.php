<?php
/**
 * Fetches a paginated, sortable, and filterable list of all dealers.
 */
function get_all_dealers() {
    $conn = get_db_connection();

    // --- 1. Get and Validate Parameters ---
    $params = $_GET;
    $current_page = isset($params['page']) ? (int)$params['page'] : 1;
    $offset = ($current_page - 1) * ITEMS_PER_PAGE;

    $sortable_columns = ['company_name', 'contact_person', 'email', 'phone', 'is_active'];
    $sort = isset($params['sort']) && in_array($params['sort'], $sortable_columns) ? $params['sort'] : 'company_name';
    $order = isset($params['order']) && in_array(strtolower($params['order']), ['asc', 'desc']) ? strtolower($params['order']) : 'asc';

    // --- 2. Build the WHERE Clause for Filtering ---
    $where_clauses = [];
    $bind_params = [];
    $types = '';

    if (!empty($params['filter_company'])) {
        $where_clauses[] = "d.company_name LIKE ?";
        $bind_params[] = '%' . $params['filter_company'] . '%';
        $types .= 's';
    }
    if (!empty($params['filter_contact'])) {
        $where_clauses[] = "u.name LIKE ?";
        $bind_params[] = '%' . $params['filter_contact'] . '%';
        $types .= 's';
    }
    // --- FIX IS HERE: Add Email and Phone filters ---
    if (!empty($params['filter_email'])) {
        $where_clauses[] = "u.email LIKE ?";
        $bind_params[] = '%' . $params['filter_email'] . '%';
        $types .= 's';
    }
    if (!empty($params['filter_phone'])) {
        $where_clauses[] = "d.phone LIKE ?";
        $bind_params[] = '%' . $params['filter_phone'] . '%';
        $types .= 's';
    }
    // --- END OF FIX ---
    if (isset($params['filter_status']) && $params['filter_status'] !== '') {
        $where_clauses[] = "d.is_active = ?";
        $bind_params[] = (int)$params['filter_status'];
        $types .= 'i';
    }
    
    $where_sql = count($where_clauses) > 0 ? 'WHERE ' . implode(' AND ', $where_clauses) : '';

    // --- 3. Run Queries ---
    $base_sql = "FROM dealers d JOIN users u ON d.user_id = u.id " . $where_sql;

    $count_stmt = $conn->prepare("SELECT COUNT(d.id) " . $base_sql);
    if (count($bind_params) > 0) $count_stmt->bind_param($types, ...$bind_params);
    $count_stmt->execute();
    $total_dealers = $count_stmt->get_result()->fetch_row()[0];

    $dealers = [];
    $data_sql = "SELECT d.id, d.company_name, d.phone, d.is_active, u.name as contact_person, u.email, u.id as user_id 
                 " . $base_sql . " ORDER BY $sort $order LIMIT ? OFFSET ?";
    
    $stmt = $conn->prepare($data_sql);
    $all_bind_params = array_merge($bind_params, [ITEMS_PER_PAGE, $offset]);
    $all_types = $types . 'ii';
    if (count($all_bind_params) > 0) $stmt->bind_param($all_types, ...$all_bind_params);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $dealers[] = $row;
        }
    }

    // --- 4. Generate Pagination and Return Data ---
    $pagination_html = generate_pagination_links($total_dealers, $current_page, '/dealers', $params);

    return [
        'dealers' => $dealers,
        'pagination' => $pagination_html,
        'params' => $params
    ];
}

/**
 * Fetches a single dealer's complete profile by their ID.
 *
 * @param int $dealer_id The ID of the dealer to fetch.
 * @return array|null The dealer's data or null if not found.
 */
function get_dealer_by_id($dealer_id) {
    $conn = get_db_connection();
    
    $stmt = $conn->prepare("SELECT d.*, u.name as contact_person, u.email 
                            FROM dealers d
                            JOIN users u ON d.user_id = u.id
                            WHERE d.id = ?");
    $stmt->bind_param('i', $dealer_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $dealer = $result->fetch_assoc();
    $stmt->close();
    
    return $dealer;
}

/**
 * Handles updating an existing dealer's profile.
 *
 * @param int $dealer_id The ID of the dealer to update.
 * @param array $data The $_POST data from the form.
 */
function handle_update_dealer($dealer_id, $data) {
    $conn = get_db_connection();
    
    // A transaction is good practice here too, to ensure both tables are updated.
    $conn->begin_transaction();
    
    try {
        // Fetch the user_id associated with this dealer
        $dealer = get_dealer_by_id($dealer_id);
        if (!$dealer) {
            throw new Exception("Dealer not found.");
        }
        $user_id = $dealer['user_id'];

        // Step 1: Update the dealers table for the specific dealer.
        // --- FIX IS HERE: Added "WHERE id = ?" ---
        $stmt_dealer = $conn->prepare("UPDATE dealers SET company_name = ?, phone = ?, address = ?, is_active = ? WHERE id = ?");
        $stmt_dealer->bind_param('sssii', $data['company_name'], $data['phone'], $data['address'], $data['is_active'], $dealer_id);
        $stmt_dealer->execute();
        $stmt_dealer->close();

        // Step 2: Update the users table for the specific associated user.
        if (!empty($data['password'])) {
            $hashed_password = password_hash($data['password'], PASSWORD_DEFAULT);
            // --- FIX IS HERE: Added "WHERE id = ?" ---
            $stmt_user = $conn->prepare("UPDATE users SET name = ?, email = ?, password = ?, is_active = ? WHERE id = ?");
            $stmt_user->bind_param('sssii', $data['contact_person'], $data['email'], $hashed_password, $data['is_active'], $user_id);
        } else {
            // --- FIX IS HERE: Added "WHERE id = ?" ---
            $stmt_user = $conn->prepare("UPDATE users SET name = ?, email = ?, is_active = ? WHERE id = ?");
            $stmt_user->bind_param('ssii', $data['contact_person'], $data['email'], $data['is_active'], $user_id);
        }
        $stmt_user->execute();
        $stmt_user->close();

        // Update Referrer Permission
        $can_manage_referrers = isset($data['can_manage_referrers']) ? 1 : 0;
        $stmt_perm = $conn->prepare("INSERT INTO dealer_permissions (dealer_id, permission_slug, is_enabled) VALUES (?, 'manage_referrers', ?) ON DUPLICATE KEY UPDATE is_enabled = VALUES(is_enabled)");
        $stmt_perm->bind_param('ii', $dealer_id, $can_manage_referrers);
        $stmt_perm->execute();
        $stmt_perm->close();
        // --- END OF ADDITION ---

        $conn->commit();
        redirect('/dealers?success=dealer_updated');

    } catch (Exception $e) {
        $conn->rollback();
        redirect("/dealers/{$dealer_id}/edit?error=" . urlencode($e->getMessage()));
  }
}

/**
 * Handles the creation of a new dealer using a database transaction.
 *
 * @param array $data The $_POST data from the form.
 */
function handle_create_dealer($data) {
    $conn = get_db_connection();
    
    // Start a transaction.
    $conn->begin_transaction();

    try {
        // Step 1: Create the User account for the dealer.
        $hashed_password = password_hash($data['password'], PASSWORD_DEFAULT);
        $role = 'dealer';
        
        $stmt_user = $conn->prepare("INSERT INTO users (name, email, password, role, is_active) VALUES (?, ?, ?, ?, ?)");
        $stmt_user->bind_param('ssssi', $data['contact_person'], $data['email'], $hashed_password, $role, $data['is_active']);
        $stmt_user->execute();
        $user_id = $conn->insert_id;
        $stmt_user->close();

        if (!$user_id) {
            throw new Exception("Failed to create the user account.");
        }

        // Step 2: Create the Dealer profile, linking it to the new user_id.
        $stmt_dealer = $conn->prepare("INSERT INTO dealers (user_id, company_name, phone, address, is_active) VALUES (?, ?, ?, ?, ?)");
        $stmt_dealer->bind_param('isssi', $user_id, $data['company_name'], $data['phone'], $data['address'], $data['is_active']);
        $stmt_dealer->execute();
        $dealer_id = $conn->insert_id;
        $stmt_dealer->close();
        
        if (!$dealer_id) {
            throw new Exception("Failed to create the dealer profile.");
        }

        // Step 3: Update the user record with the new dealer_id to complete the link.
        $stmt_update_user = $conn->prepare("UPDATE users SET dealer_id = ? WHERE id = ?");
        $stmt_update_user->bind_param('ii', $dealer_id, $user_id);
        $stmt_update_user->execute();
        $stmt_update_user->close();

        // If all steps succeeded, commit the transaction.
        $conn->commit();
        redirect('/dealers?success=dealer_created');

    } catch (Exception $e) {
        // If any step failed, roll back all changes.
        $conn->rollback();
        redirect('/dealers/create?error=' . urlencode($e->getMessage()));
    }
}

/**
 * Handles "soft deleting" a dealer by deactivating their accounts.
 *
 * @param int $dealer_id The ID of the dealer to deactivate.
 */
function handle_delete_dealer($dealer_id) {
    $conn = get_db_connection();
    $conn->begin_transaction();

    try {
        $dealer = get_dealer_by_id($dealer_id);
        if (!$dealer) {
            throw new Exception("Dealer not found.");
        }
        $user_id = $dealer['user_id'];

        // Deactivate the dealer profile
        $stmt_dealer = $conn->prepare("UPDATE dealers SET is_active = 0 WHERE id = ?");
        $stmt_dealer->bind_param('i', $dealer_id);
        $stmt_dealer->execute();
        $stmt_dealer->close();
        
        // Deactivate the associated user account
        $stmt_user = $conn->prepare("UPDATE users SET is_active = 0 WHERE id = ?");
        $stmt_user->bind_param('i', $user_id);
        $stmt_user->execute();
        $stmt_user->close();

        $conn->commit();
        redirect('/dealers?success=dealer_deactivated');

    } catch (Exception $e) {
        $conn->rollback();
        redirect('/dealers?error=' . urlencode($e->getMessage()));
    }
}

/**
 * Handles "activating" a dealer by re-enabling their accounts.
 *
 * @param int $dealer_id The ID of the dealer to activate.
 */
function handle_activate_dealer($dealer_id) {
    $conn = get_db_connection();
    $conn->begin_transaction();

    try {
        $dealer = get_dealer_by_id($dealer_id);
        if (!$dealer) {
            throw new Exception("Dealer not found.");
        }
        $user_id = $dealer['user_id'];

        // Activate the dealer profile
        $stmt_dealer = $conn->prepare("UPDATE dealers SET is_active = 1 WHERE id = ?");
        $stmt_dealer->bind_param('i', $dealer_id);
        $stmt_dealer->execute();
        $stmt_dealer->close();
        
        // Activate the associated user account
        $stmt_user = $conn->prepare("UPDATE users SET is_active = 1 WHERE id = ?");
        $stmt_user->bind_param('i', $user_id);
        $stmt_user->execute();
        $stmt_user->close();

        $conn->commit();
        redirect('/dealers?success=dealer_activated');

    } catch (Exception $e) {
        $conn->rollback();
        redirect('/dealers?error=' . urlencode($e->getMessage()));
    }
}

/**
 * Helper function to get all active dealers for dropdown menus.
 *
 * @return array A list of dealers.
 */
function get_all_dealers_for_dropdown() {
    $conn = get_db_connection();
    $dealers = [];
    $result = $conn->query("SELECT id, company_name FROM dealers WHERE is_active = 1 ORDER BY company_name ASC");
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $dealers[] = $row;
        }
    }
    return $dealers;
}
function get_dealer_permissions($dealer_id) {
    $conn = get_db_connection();
    $stmt = $conn->prepare("SELECT permission_slug FROM dealer_permissions WHERE dealer_id = ? AND is_enabled = 1");
    $stmt->bind_param('i', $dealer_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $permissions = [];
    while ($row = $result->fetch_assoc()) {
        $permissions[] = $row['permission_slug'];
    }
    return $permissions;
}
?>
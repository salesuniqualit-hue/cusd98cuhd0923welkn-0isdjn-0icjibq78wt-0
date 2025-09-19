<?php
/**
 * Fetches a paginated, sortable, and filterable list of users.
 *
 * @param array $current_user The currently logged-in user.
 * @return array An array containing the page title and the list of users.
 */
function get_users_list_data($current_user) {
    $conn = get_db_connection();
    $params = $_GET;
    $current_page = isset($params['page']) ? (int)$params['page'] : 1;
    $offset = ($current_page - 1) * ITEMS_PER_PAGE;

    // Whitelist for sortable columns
    $sortable_columns = ['name', 'email', 'is_active'];
    $sort = isset($params['sort']) && in_array($params['sort'], $sortable_columns) ? $params['sort'] : 'name';
    $order = isset($params['order']) && in_array(strtolower($params['order']), ['asc', 'desc']) ? strtolower($params['order']) : 'asc';

    // Base query and initial WHERE clause
    $base_sql = "FROM users ";
    $where_clauses = [];
    $bind_params = [];
    $types = '';

    if ($current_user['role'] === 'admin') {
        $page_title = 'Manage Internal Users';
        $where_clauses[] = "role = 'internal_user'";
    } else { // dealer
        $page_title = 'Manage Team Members';
        $where_clauses[] = "role = 'team_member' AND dealer_id = ?";
        $bind_params[] = $current_user['dealer_id'];
        $types .= 'i';
    }

    // Add filters
    if (!empty($params['filter_name'])) {
        $where_clauses[] = "name LIKE ?";
        $bind_params[] = '%' . $params['filter_name'] . '%';
        $types .= 's';
    }
    if (!empty($params['filter_email'])) {
        $where_clauses[] = "email LIKE ?";
        $bind_params[] = '%' . $params['filter_email'] . '%';
        $types .= 's';
    }
    if (isset($params['filter_status']) && $params['filter_status'] !== '') {
        $where_clauses[] = "is_active = ?";
        $bind_params[] = (int)$params['filter_status'];
        $types .= 'i';
    }

    $where_sql = "WHERE " . implode(' AND ', $where_clauses);
    $full_base_sql = $base_sql . $where_sql;

    // Count total matching users
    $count_stmt = $conn->prepare("SELECT COUNT(id) " . $full_base_sql);
    if (count($bind_params) > 0) $count_stmt->bind_param($types, ...$bind_params);
    $count_stmt->execute();
    $total_users = $count_stmt->get_result()->fetch_row()[0];

    // Fetch data for the current page
    $users = [];
    $data_sql = "SELECT id, name, email, role, is_active " . $full_base_sql . " ORDER BY $sort $order LIMIT ? OFFSET ?";
    $stmt = $conn->prepare($data_sql);
    $all_bind_params = array_merge($bind_params, [ITEMS_PER_PAGE, $offset]);
    $all_types = $types . 'ii';
    if (count($all_bind_params) > 0) $stmt->bind_param($all_types, ...$all_bind_params);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $users[] = $row;
    }

    $pagination_html = generate_pagination_links($total_users, $current_page, '/users', $params);

    return [
        'page_title' => $page_title,
        'users' => $users,
        'pagination' => $pagination_html,
        'params' => $params
    ];
}

/**
 * Fetches a single user by their ID, with permission checks.
 *
 * @param int $user_id The ID of the user to fetch.
 * @return array|null The user's data or null if not found or not permitted.
 */
function get_user_by_id($user_id) {
    $conn = get_db_connection();
    $current_user = current_user();

    $stmt = $conn->prepare("SELECT id, name, email, role, dealer_id, is_active FROM users WHERE id = ?");
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();

    if (!$user) {
        return null; // User not found
    }

    // Permission Check:
    // Admin can access any 'internal_user'.
    if ($current_user['role'] === 'admin' && $user['role'] === 'internal_user') {
        return $user;
    }
    // Dealer can access their own 'team_member'.
    if ($current_user['role'] === 'dealer' && $user['role'] === 'team_member' && $user['dealer_id'] == $current_user['dealer_id']) {
        return $user;
    }

    return null; // Permission denied
}

/**
 * Handles the creation of a new user from POST data.
 *
 * @param array $data The $_POST data from the form.
 */
function handle_create_user($data) {
    $current_user = current_user();
    // TODO: Add robust validation for all fields.

    $name = $data['name'];
    $email = $data['email'];
    $password = $data['password'];
    $is_active = isset($data['is_active']) ? 1 : 0;
    
    // Determine the role and dealer_id based on who is creating the user.
    if ($current_user['role'] === 'admin') {
        $role = 'internal_user';
        $dealer_id = null;
    } elseif ($current_user['role'] === 'dealer') {
        $role = 'team_member';
        $dealer_id = $current_user['dealer_id'];
    } else {
        // Should not happen if UI is correct
        redirect('/users?error=permission_denied');
    }
    
    // Hash the password for secure storage.
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    $conn = get_db_connection();
    $stmt = $conn->prepare("INSERT INTO users (name, email, password, role, dealer_id, is_active) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param('ssssii', $name, $email, $hashed_password, $role, $dealer_id, $is_active);
    
    if ($stmt->execute()) {
        redirect('/users?success=user_created');
    } else {
        // Handle potential duplicate email error
        redirect('/users/create?error=' . urlencode($stmt->error));
    }
    $stmt->close();
}


/**
 * Handles updating an existing user from POST data.
 *
 * @param int $user_id The ID of the user to update.
 * @param array $data The $_POST data from the form.
 */
function handle_update_user($user_id, $data) {
    // First, ensure the current user has permission to edit this user.
    $user_to_edit = get_user_by_id($user_id);
    if (!$user_to_edit) {
        redirect('/users?error=permission_denied');
    }
    
    // TODO: Add robust validation.
    $name = $data['name'];
    $email = $data['email'];
    $password = $data['password'];
    $is_active = isset($data['is_active']) ? 1 : 0;

    $conn = get_db_connection();
    
    // Check if the password needs to be updated.
    if (!empty($password)) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE users SET name = ?, email = ?, password = ?, is_active = ? WHERE id = ?");
        $stmt->bind_param('sssii', $name, $email, $hashed_password, $is_active, $user_id);
    } else {
        // Update without changing the password.
        $stmt = $conn->prepare("UPDATE users SET name = ?, email = ?, is_active = ? WHERE id = ?");
        $stmt->bind_param('ssii', $name, $email, $is_active, $user_id);
    }

    if ($stmt->execute()) {
        redirect('/users?success=user_updated');
    } else {
        redirect("/users/{$user_id}/edit?error=" . urlencode($stmt->error));
    }
    $stmt->close();
}


/**
 * Handles the deletion of a user.
 *
 * @param int $user_id The ID of the user to delete.
 */
function handle_delete_user($user_id) {
    // Ensure the current user has permission to delete this user.
    $user_to_delete = get_user_by_id($user_id);
    if (!$user_to_delete) {
        redirect('/users?error=permission_denied');
    }

    $conn = get_db_connection();
    $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
    $stmt->bind_param('i', $user_id);

    if ($stmt->execute()) {
        redirect('/users?success=user_deleted');
    } else {
        redirect('/users?error=' . urlencode($stmt->error));
    }
    $stmt->close();
}
?>
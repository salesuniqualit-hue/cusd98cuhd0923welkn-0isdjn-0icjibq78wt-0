<?php
// modules/skus/actions.php

/**
 * Helper function to get all SKU categories for dropdowns.
 *
 * @return array A list of SKU categories.
 */
function get_all_sku_categories() {
    $conn = get_db_connection();
    $categories = [];
    $sql = "SELECT id, name FROM sku_categories ORDER BY name ASC";
    $result = $conn->query($sql);
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $categories[] = $row;
        }
    }
    return $categories;
}

/**
 * Fetches a paginated, sortable, and filterable list of all SKUs.
 *
 * @return array A list of SKU records and pagination data.
 */
function get_all_skus() {
    $conn = get_db_connection();
    $params = $_GET;
    $current_page = isset($params['page']) ? (int)$params['page'] : 1;
    $offset = ($current_page - 1) * ITEMS_PER_PAGE;

    // Whitelist for sortable columns
    $sortable_columns = ['name', 'code', 'category_name'];
    $sort = isset($params['sort']) && in_array($params['sort'], $sortable_columns) ? $params['sort'] : 'name';
    $order = isset($params['order']) && in_array(strtolower($params['order']), ['asc', 'desc']) ? strtolower($params['order']) : 'asc';

    // Base query
    $base_sql = "FROM skus s LEFT JOIN sku_categories sc ON s.category_id = sc.id ";
    $where_clauses = [];
    $bind_params = [];
    $types = '';

    // Add filters
    if (!empty($params['filter_name'])) {
        $where_clauses[] = "s.name LIKE ?";
        $bind_params[] = '%' . $params['filter_name'] . '%';
        $types .= 's';
    }
    if (!empty($params['filter_code'])) {
        $where_clauses[] = "s.code LIKE ?";
        $bind_params[] = '%' . $params['filter_code'] . '%';
        $types .= 's';
    }
    if (!empty($params['filter_category'])) {
        $where_clauses[] = "sc.name LIKE ?";
        $bind_params[] = '%' . $params['filter_category'] . '%';
        $types .= 's';
    }

    $where_sql = count($where_clauses) > 0 ? 'WHERE ' . implode(' AND ', $where_clauses) : '';
    $full_base_sql = $base_sql . $where_sql;

    // Count total matching SKUs
    $count_stmt = $conn->prepare("SELECT COUNT(s.id) " . $full_base_sql);
    if (count($bind_params) > 0) $count_stmt->bind_param($types, ...$bind_params);
    $count_stmt->execute();
    $total_skus = $count_stmt->get_result()->fetch_row()[0];

    // Fetch data for the current page
    $skus = [];
    $data_sql = "SELECT s.id, s.name, s.code, s.is_yearly, s.is_perpetual, sc.name as category_name " . $full_base_sql . " ORDER BY $sort $order LIMIT ? OFFSET ?";
    $stmt = $conn->prepare($data_sql);
    $all_bind_params = array_merge($bind_params, [ITEMS_PER_PAGE, $offset]);
    $all_types = $types . 'ii';
    if (count($all_bind_params) > 0) $stmt->bind_param($all_types, ...$all_bind_params);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $skus[] = $row;
    }

    $pagination_html = generate_pagination_links($total_skus, $current_page, '/skus', $params);

    return [
        'skus' => $skus,
        'pagination' => $pagination_html,
        'params' => $params
    ];
}

/**
 * Fetches a single SKU by its ID.
 *
 * @param int $sku_id The ID of the SKU to fetch.
 * @return array|null The SKU's data or null if not found.
 */
function get_sku_by_id($sku_id) {
    $conn = get_db_connection();
    $stmt = $conn->prepare("SELECT * FROM skus WHERE id = ?");
    $stmt->bind_param('i', $sku_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $sku = $result->fetch_assoc();
    $stmt->close();
    return $sku;
}

/**
 * Handles the creation of a new SKU.
 *
 * @param array $data The $_POST data from the form.
 */
function handle_create_sku($data) {
    $conn = get_db_connection();

    // Data preparation from form
    $is_yearly = isset($data['is_yearly']) ? 1 : 0;
    $is_perpetual = isset($data['is_perpetual']) ? 1 : 0;
    $release_date = !empty($data['release_date']) ? $data['release_date'] : null;

    $stmt = $conn->prepare(
        "INSERT INTO skus (category_id, code, guid, name, description, is_yearly, is_perpetual, subscription_period, trial_period, warranty_period, release_date) 
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
    );
    $stmt->bind_param(
        'issssiisiss',
        $data['category_id'],
        $data['code'],
        $data['guid'],
        $data['name'],
        $data['description'],
        $is_yearly,
        $is_perpetual,
        $data['subscription_period'],
        $data['trial_period'],
        $data['warranty_period'],
        $release_date
    );

    if ($stmt->execute()) {
        redirect('/skus?success=sku_created');
    } else {
        redirect('/skus/create?error=' . urlencode($stmt->error));
    }
    $stmt->close();
}

/**
 * Handles updating an existing SKU.
 *
 * @param int $sku_id The ID of the SKU to update.
 * @param array $data The $_POST data from the form.
 */
function handle_update_sku($sku_id, $data) {
    $conn = get_db_connection();

    $is_yearly = isset($data['is_yearly']) ? 1 : 0;
    $is_perpetual = isset($data['is_perpetual']) ? 1 : 0;
    $release_date = !empty($data['release_date']) ? $data['release_date'] : null;
    
    $stmt = $conn->prepare(
        "UPDATE skus SET category_id = ?, code = ?, guid = ?, name = ?, description = ?, is_yearly = ?, is_perpetual = ?, 
         subscription_period = ?, trial_period = ?, warranty_period = ?, release_date = ?
         WHERE id = ?"
    );
    $stmt->bind_param(
        'issssiisissi',
        $data['category_id'],
        $data['code'],
        $data['guid'],
        $data['name'],
        $data['description'],
        $is_yearly,
        $is_perpetual,
        $data['subscription_period'],
        $data['trial_period'],
        $data['warranty_period'],
        $release_date,
        $sku_id
    );

    if ($stmt->execute()) {
        redirect('/skus?success=sku_updated');
    } else {
        redirect("/skus/{$sku_id}/edit?error=" . urlencode($stmt->error));
    }
    $stmt->close();
}

/**
 * Handles deleting an SKU, with a safety check.
 *
 * @param int $sku_id The ID of the SKU to delete.
 */
function handle_delete_sku($sku_id) {
    $conn = get_db_connection();

    // Safety Check: Prevent deletion if the SKU is linked to any orders.
    // You can add more checks here (e.g., for versions, price lists, etc.).
    $stmt = $conn->prepare("SELECT id FROM orders WHERE sku_id = ?");
    $stmt->bind_param('i', $sku_id);
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) {
        redirect('/skus?error=cannot_delete_sku_in_use');
        return;
    }
    $stmt->close();

    // Proceed with deletion if checks pass.
    $stmt = $conn->prepare("DELETE FROM skus WHERE id = ?");
    $stmt->bind_param('i', $sku_id);
    if ($stmt->execute()) {
        redirect('/skus?success=sku_deleted');
    } else {
        redirect('/skus?error=' . urlencode($stmt->error));
    }
    $stmt->close();
}

/**
 * Helper function to get all SKUs for dropdown menus.
 *
 * @return array A list of SKUs.
 */
function get_all_skus_for_dropdown() {
    $conn = get_db_connection();
    $skus = [];
    $result = $conn->query("SELECT id, name, is_yearly, is_perpetual FROM skus ORDER BY name ASC");
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $skus[] = $row;
        }
    }
    return $skus;
}
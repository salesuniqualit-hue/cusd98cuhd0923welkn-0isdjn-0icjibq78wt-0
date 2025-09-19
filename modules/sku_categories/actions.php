<?php
// modules/sku_categories/actions.php

/**
 * Fetches a paginated, sortable, and filterable list of all SKU categories.
 *
 * @return array A list of category records and pagination data.
 */
function get_all_categories() {
    $conn = get_db_connection();
    $params = $_GET;
    $current_page = isset($params['page']) ? (int)$params['page'] : 1;
    $offset = ($current_page - 1) * ITEMS_PER_PAGE;

    // Whitelist for sortable columns
    $sortable_columns = ['name', 'parent_name'];
    $sort = isset($params['sort']) && in_array($params['sort'], $sortable_columns) ? $params['sort'] : 'name';
    $order = isset($params['order']) && in_array(strtolower($params['order']), ['asc', 'desc']) ? strtolower($params['order']) : 'asc';

    // Base query
    $base_sql = "FROM sku_categories c LEFT JOIN sku_categories p ON c.parent_id = p.id ";
    $where_clauses = [];
    $bind_params = [];
    $types = '';

    // Add filters
    if (!empty($params['filter_name'])) {
        $where_clauses[] = "c.name LIKE ?";
        $bind_params[] = '%' . $params['filter_name'] . '%';
        $types .= 's';
    }
    if (!empty($params['filter_parent'])) {
        $where_clauses[] = "p.name LIKE ?";
        $bind_params[] = '%' . $params['filter_parent'] . '%';
        $types .= 's';
    }

    $where_sql = count($where_clauses) > 0 ? 'WHERE ' . implode(' AND ', $where_clauses) : '';
    $full_base_sql = $base_sql . $where_sql;

    // Count total matching categories
    $count_stmt = $conn->prepare("SELECT COUNT(c.id) " . $full_base_sql);
    if (count($bind_params) > 0) $count_stmt->bind_param($types, ...$bind_params);
    $count_stmt->execute();
    $total_categories = $count_stmt->get_result()->fetch_row()[0];

    // Fetch data for the current page
    $categories = [];
    $data_sql = "SELECT c.id, c.name, p.name AS parent_name " . $full_base_sql . " ORDER BY $sort $order LIMIT ? OFFSET ?";
    $stmt = $conn->prepare($data_sql);
    $all_bind_params = array_merge($bind_params, [ITEMS_PER_PAGE, $offset]);
    $all_types = $types . 'ii';
    if (count($all_bind_params) > 0) $stmt->bind_param($all_types, ...$all_bind_params);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $categories[] = $row;
    }

    $pagination_html = generate_pagination_links($total_categories, $current_page, '/sku_categories', $params);

    return [
        'categories' => $categories,
        'pagination' => $pagination_html,
        'params' => $params
    ];
}

/**
 * Fetches a single category by its ID.
 *
 * @param int $category_id The ID of the category to fetch.
 * @return array|null The category data or null if not found.
 */
function get_category_by_id($category_id) {
    $conn = get_db_connection();
    $stmt = $conn->prepare("SELECT id, name, parent_id FROM sku_categories WHERE id = ?");
    $stmt->bind_param('i', $category_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $category = $result->fetch_assoc();
    $stmt->close();
    return $category;
}

/**
 * Handles the creation of a new SKU category.
 *
 * @param array $data The $_POST data from the form.
 */
function handle_create_category($data) {
    $conn = get_db_connection();
    $name = trim($data['name']);
    // If parent_id is empty or 0, treat it as NULL (a top-level category).
    $parent_id = !empty($data['parent_id']) ? (int)$data['parent_id'] : null;

    if (empty($name)) {
        redirect('/sku_categories/create?error=name_required');
        return;
    }

    $stmt = $conn->prepare("INSERT INTO sku_categories (name, parent_id) VALUES (?, ?)");
    $stmt->bind_param('si', $name, $parent_id);
    
    if ($stmt->execute()) {
        redirect('/sku_categories?success=category_created');
    } else {
        redirect('/sku_categories/create?error=' . urlencode($stmt->error));
    }
    $stmt->close();
}

/**
 * Handles updating an existing SKU category.
 *
 * @param int $category_id The ID of the category to update.
 * @param array $data The $_POST data from the form.
 */
function handle_update_category($category_id, $data) {
    $conn = get_db_connection();
    $name = trim($data['name']);
    $parent_id = !empty($data['parent_id']) ? (int)$data['parent_id'] : null;

    // A category cannot be its own parent.
    if ($category_id === $parent_id) {
        redirect("/sku_categories/{$category_id}/edit?error=invalid_parent");
        return;
    }

    $stmt = $conn->prepare("UPDATE sku_categories SET name = ?, parent_id = ? WHERE id = ?");
    $stmt->bind_param('sii', $name, $parent_id, $category_id);

    if ($stmt->execute()) {
        redirect('/sku_categories?success=category_updated');
    } else {
        redirect("/sku_categories/{$category_id}/edit?error=" . urlencode($stmt->error));
    }
    $stmt->close();
}

/**
 * Handles deleting an SKU category, with safety checks.
 *
 * @param int $category_id The ID of the category to delete.
 */
function handle_delete_category($category_id) {
    $conn = get_db_connection();

    // Safety Check 1: Check for child categories.
    $stmt = $conn->prepare("SELECT id FROM sku_categories WHERE parent_id = ?");
    $stmt->bind_param('i', $category_id);
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) {
        redirect('/sku_categories?error=cannot_delete_has_children');
        return;
    }
    $stmt->close();

    // Safety Check 2: Check for SKUs assigned to this category.
    $stmt = $conn->prepare("SELECT id FROM skus WHERE category_id = ?");
    $stmt->bind_param('i', $category_id);
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) {
        redirect('/sku_categories?error=cannot_delete_has_skus');
        return;
    }
    $stmt->close();

    // If checks pass, proceed with deletion.
    $stmt = $conn->prepare("DELETE FROM sku_categories WHERE id = ?");
    $stmt->bind_param('i', $category_id);
    if ($stmt->execute()) {
        redirect('/sku_categories?success=category_deleted');
    } else {
        redirect('/sku_categories?error=' . urlencode($stmt->error));
    }
    $stmt->close();
}
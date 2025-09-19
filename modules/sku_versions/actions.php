<?php
// modules/sku_versions/actions.php

/**
 * Helper function to get changelogs that are not yet linked to a version.
 *
 * @param int|null $current_changelog_id The ID of the changelog currently assigned to the version being edited.
 * @return array A list of available changelogs.
 */
function get_available_changelogs($current_changelog_id = null) {
    $conn = get_db_connection();
    $changelogs = [];
    // This query selects changelogs whose IDs are NOT in the list of already assigned changelog_ids in the sku_versions table.
    $sql = "SELECT id, title FROM changelogs WHERE id NOT IN (SELECT changelog_id FROM sku_versions WHERE changelog_id IS NOT NULL)";
    
    // For the edit form, we must also include the currently assigned changelog in the list.
    if ($current_changelog_id) {
        $sql .= " OR id = ?";
    }
    $sql .= " ORDER BY title ASC";

    $stmt = $conn->prepare($sql);
    if ($current_changelog_id) {
        $stmt->bind_param('i', $current_changelog_id);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $changelogs[] = $row;
    }
    $stmt->close();
    return $changelogs;
}

/**
 * Fetches a paginated, sortable, and filterable list of all SKU versions.
 *
 * @return array A list of version records and pagination data.
 */
function get_all_sku_versions() {
    $conn = get_db_connection();
    $params = $_GET;
    $current_page = isset($params['page']) ? (int)$params['page'] : 1;
    $offset = ($current_page - 1) * ITEMS_PER_PAGE;

    // Whitelist for sortable columns
    $sortable_columns = ['sku_name', 'version_number', 'release_date'];
    $sort = isset($params['sort']) && in_array($params['sort'], $sortable_columns) ? $params['sort'] : 'sku_name';
    $order = isset($params['order']) && in_array(strtolower($params['order']), ['asc', 'desc']) ? strtolower($params['order']) : 'asc';

    // Base query
    $base_sql = "FROM sku_versions sv JOIN skus s ON sv.sku_id = s.id ";
    $where_clauses = [];
    $bind_params = [];
    $types = '';

    // Add filters
    if (!empty($params['filter_sku_name'])) {
        $where_clauses[] = "s.name LIKE ?";
        $bind_params[] = '%' . $params['filter_sku_name'] . '%';
        $types .= 's';
    }
    if (!empty($params['filter_version_number'])) {
        $where_clauses[] = "sv.version_number LIKE ?";
        $bind_params[] = '%' . $params['filter_version_number'] . '%';
        $types .= 's';
    }
    // Date range filter
    if (!empty($params['filter_release_from'])) {
        $where_clauses[] = "sv.release_date >= ?";
        $bind_params[] = $params['filter_release_from'];
        $types .= 's';
    }
    if (!empty($params['filter_release_to'])) {
        $where_clauses[] = "sv.release_date <= ?";
        $bind_params[] = $params['filter_release_to'];
        $types .= 's';
    }

    $where_sql = count($where_clauses) > 0 ? 'WHERE ' . implode(' AND ', $where_clauses) : '';
    $full_base_sql = $base_sql . $where_sql;

    // Count total matching versions
    $count_stmt = $conn->prepare("SELECT COUNT(sv.id) " . $full_base_sql);
    if (count($bind_params) > 0) $count_stmt->bind_param($types, ...$bind_params);
    $count_stmt->execute();
    $total_versions = $count_stmt->get_result()->fetch_row()[0];

    // Fetch data for the current page
    $versions = [];
    $data_sql = "SELECT sv.id, sv.version_number, sv.release_date, s.name as sku_name " . $full_base_sql . " ORDER BY $sort $order LIMIT ? OFFSET ?";
    $stmt = $conn->prepare($data_sql);
    $all_bind_params = array_merge($bind_params, [ITEMS_PER_PAGE, $offset]);
    $all_types = $types . 'ii';
    if (count($all_bind_params) > 0) $stmt->bind_param($all_types, ...$all_bind_params);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $versions[] = $row;
    }

    $pagination_html = generate_pagination_links($total_versions, $current_page, '/sku_versions', $params);

    return [
        'versions' => $versions,
        'pagination' => $pagination_html,
        'params' => $params
    ];
}

/**
 * Fetches a single SKU version by its ID.
 *
 * @param int $version_id The ID of the version to fetch.
 * @return array|null The version's data or null if not found.
 */
function get_sku_version_by_id($version_id) {
    $conn = get_db_connection();
    $stmt = $conn->prepare("SELECT * FROM sku_versions WHERE id = ?");
    $stmt->bind_param('i', $version_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $version = $result->fetch_assoc();
    $stmt->close();
    return $version;
}

/**
 * Handles the creation of a new SKU version.
 *
 * @param array $data The $_POST data from the form.
 */
function handle_create_sku_version($data) {
    $conn = get_db_connection();
    $changelog_id = !empty($data['changelog_id']) ? (int)$data['changelog_id'] : null;

    $stmt = $conn->prepare(
        "INSERT INTO sku_versions (sku_id, changelog_id, version_number, description, tally_compat_from, tally_compat_to, link_product, link_manual, link_ppt, link_faq, release_date)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
    );
    $stmt->bind_param(
        'iisssssssss',
        $data['sku_id'],
        $changelog_id,
        $data['version_number'],
        $data['description'],
        $data['tally_compat_from'],
        $data['tally_compat_to'],
        $data['link_product'],
        $data['link_manual'],
        $data['link_ppt'],
        $data['link_faq'],
        $data['release_date']
    );

    if ($stmt->execute()) {
        redirect('/sku_versions?success=version_created');
    } else {
        redirect('/sku_versions/create?error=' . urlencode($stmt->error));
    }
    $stmt->close();
}

/**
 * Handles updating an existing SKU version.
 *
 * @param int $version_id The ID of the version to update.
 * @param array $data The $_POST data from the form.
 */
function handle_update_sku_version($version_id, $data) {
    $conn = get_db_connection();
    $changelog_id = !empty($data['changelog_id']) ? (int)$data['changelog_id'] : null;

    $stmt = $conn->prepare(
        "UPDATE sku_versions SET sku_id = ?, changelog_id = ?, version_number = ?, description = ?, tally_compat_from = ?, 
         tally_compat_to = ?, link_product = ?, link_manual = ?, link_ppt = ?, link_faq = ?, release_date = ?
         WHERE id = ?"
    );
    $stmt->bind_param(
        'iisssssssssi',
        $data['sku_id'],
        $changelog_id,
        $data['version_number'],
        $data['description'],
        $data['tally_compat_from'],
        $data['tally_compat_to'],
        $data['link_product'],
        $data['link_manual'],
        $data['link_ppt'],
        $data['link_faq'],
        $data['release_date'],
        $version_id
    );

    if ($stmt->execute()) {
        redirect('/sku_versions?success=version_updated');
    } else {
        redirect("/sku_versions/{$version_id}/edit?error=" . urlencode($stmt->error));
    }
    $stmt->close();
}

/**
 * Handles deleting an SKU version.
 *
 * @param int $version_id The ID of the version to delete.
 */
function handle_delete_sku_version($version_id) {
    $conn = get_db_connection();
    
    // Safety Check: Prevent deletion if this version is used in any orders.
    $stmt = $conn->prepare("SELECT id FROM orders WHERE sku_version_id = ?");
    $stmt->bind_param('i', $version_id);
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) {
        redirect('/sku_versions?error=cannot_delete_version_in_use');
        return;
    }
    $stmt->close();

    // Proceed with deletion
    $stmt = $conn->prepare("DELETE FROM sku_versions WHERE id = ?");
    $stmt->bind_param('i', $version_id);
    if ($stmt->execute()) {
        redirect('/sku_versions?success=version_deleted');
    } else {
        redirect('/sku_versions?error=' . urlencode($stmt->error));
    }
    $stmt->close();
}
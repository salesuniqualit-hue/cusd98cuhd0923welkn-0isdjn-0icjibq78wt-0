<?php
// modules/changelogs/actions.php

/**
 * Fetches a paginated, sortable, and filterable list of all changelogs.
 *
 * @return array A list of all changelog records and pagination data.
 */
function get_all_changelogs() {
    $conn = get_db_connection();
    $params = $_GET;
    $current_page = isset($params['page']) ? (int)$params['page'] : 1;
    $offset = ($current_page - 1) * ITEMS_PER_PAGE;

    // Whitelist for sortable columns
    $sortable_columns = ['title', 'sku_name'];
    $sort = isset($params['sort']) && in_array($params['sort'], $sortable_columns) ? $params['sort'] : 'title';
    $order = isset($params['order']) && in_array(strtolower($params['order']), ['asc', 'desc']) ? strtolower($params['order']) : 'asc';

    // Base query
    $base_sql = "FROM changelogs c JOIN skus s ON c.sku_id = s.id LEFT JOIN sku_versions sv ON c.id = sv.changelog_id ";
    $where_clauses = [];
    $bind_params = [];
    $types = '';

    // Add filters
    if (!empty($params['filter_title'])) {
        $where_clauses[] = "c.title LIKE ?";
        $bind_params[] = '%' . $params['filter_title'] . '%';
        $types .= 's';
    }
    if (!empty($params['filter_sku_name'])) {
        $where_clauses[] = "s.name LIKE ?";
        $bind_params[] = '%' . $params['filter_sku_name'] . '%';
        $types .= 's';
    }
    if (isset($params['filter_status']) && $params['filter_status'] !== '') {
        if ($params['filter_status'] === '1') { // Assigned
            $where_clauses[] = "sv.id IS NOT NULL";
        } else { // Available
            $where_clauses[] = "sv.id IS NULL";
        }
    }

    $where_sql = count($where_clauses) > 0 ? 'WHERE ' . implode(' AND ', $where_clauses) : '';
    $full_base_sql = $base_sql . $where_sql;

    // Count total matching changelogs
    $count_stmt = $conn->prepare("SELECT COUNT(c.id) " . $full_base_sql);
    if (count($bind_params) > 0) $count_stmt->bind_param($types, ...$bind_params);
    $count_stmt->execute();
    $total_changelogs = $count_stmt->get_result()->fetch_row()[0];

    // Fetch data for the current page
    $changelogs = [];
    $data_sql = "SELECT c.id, c.title, s.name as sku_name, sv.id as version_id " . $full_base_sql . " ORDER BY $sort $order LIMIT ? OFFSET ?";
    $stmt = $conn->prepare($data_sql);
    $all_bind_params = array_merge($bind_params, [ITEMS_PER_PAGE, $offset]);
    $all_types = $types . 'ii';
    if (count($all_bind_params) > 0) $stmt->bind_param($all_types, ...$all_bind_params);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $changelogs[] = $row;
    }

    $pagination_html = generate_pagination_links($total_changelogs, $current_page, '/changelogs', $params);

    return [
        'changelogs' => $changelogs,
        'pagination' => $pagination_html,
        'params' => $params
    ];
}

/**
 * Fetches a single changelog by its ID.
 *
 * @param int $changelog_id The ID of the changelog to fetch.
 * @return array|null The changelog data or null if not found.
 */
function get_changelog_by_id($changelog_id) {
    $conn = get_db_connection();
    $stmt = $conn->prepare("SELECT id, sku_id, title, changes FROM changelogs WHERE id = ?");
    $stmt->bind_param('i', $changelog_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $changelog = $result->fetch_assoc();
    $stmt->close();
    return $changelog;
}

/**
 * Handles the creation of a new changelog.
 *
 * @param array $data The $_POST data from the form.
 */
function handle_create_changelog($data) {
    $conn = get_db_connection();
    $stmt = $conn->prepare("INSERT INTO changelogs (sku_id, title, changes) VALUES (?, ?, ?)");
    $stmt->bind_param('iss', $data['sku_id'], $data['title'], $data['changes']);

    if ($stmt->execute()) {
        redirect('/changelogs?success=changelog_created');
    } else {
        redirect('/changelogs/create?error=' . urlencode($stmt->error));
    }
    $stmt->close();
}

/**
 * Handles updating an existing changelog.
 *
 * @param int $changelog_id The ID of the changelog to update.
 * @param array $data The $_POST data from the form.
 */
function handle_update_changelog($changelog_id, $data) {
    $conn = get_db_connection();
    $stmt = $conn->prepare("UPDATE changelogs SET sku_id = ?, title = ?, changes = ? WHERE id = ?");
    $stmt->bind_param('issi', $data['sku_id'], $data['title'], $data['changes'], $changelog_id);

    if ($stmt->execute()) {
        redirect('/changelogs?success=changelog_updated');
    } else {
        redirect("/changelogs/{$changelog_id}/edit?error=" . urlencode($stmt->error));
    }
    $stmt->close();
}

/**
 * Handles deleting a changelog, with a safety check.
 *
 * @param int $changelog_id The ID of the changelog to delete.
 */
function handle_delete_changelog($changelog_id) {
    $conn = get_db_connection();

    // Safety Check: Prevent deletion if the changelog is linked to a version.
    $stmt = $conn->prepare("SELECT id FROM sku_versions WHERE changelog_id = ?");
    $stmt->bind_param('i', $changelog_id);
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) {
        redirect('/changelogs?error=cannot_delete_changelog_in_use');
        return;
    }
    $stmt->close();

    // If check passes, proceed with deletion.
    $stmt = $conn->prepare("DELETE FROM changelogs WHERE id = ?");
    $stmt->bind_param('i', $changelog_id);
    if ($stmt->execute()) {
        redirect('/changelogs?success=changelog_deleted');
    } else {
        redirect('/changelogs?error=' . urlencode($stmt->error));
    }
    $stmt->close();
}
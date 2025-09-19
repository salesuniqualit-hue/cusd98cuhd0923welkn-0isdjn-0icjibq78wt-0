<?php
// core/functions.php

/**
 * Generates a full, correctly formatted URL relative to the application's base path.
 */
function url($path) {
    return rtrim(BASE_PATH, '/') . '/' . ltrim($path, '/');
}

/**
 * Escapes HTML special characters in a string to prevent XSS attacks.
 */
function e($string) {
    return htmlspecialchars($string ?? '', ENT_QUOTES, 'UTF-8');
}

/**
 * Redirects the user to a specified URL and terminates the script.
 */
function redirect($path) {
    header('Location: ' . url($path));
    exit();
}

/**
 * Returns the currently logged-in user's data from the session.
 */
function current_user() {
    if (isset($_SESSION['user_id'])) {
        return [
            'id' => $_SESSION['user_id'],
            'role' => $_SESSION['user_role'] ?? null,
            'dealer_id' => $_SESSION['dealer_id'] ?? null
        ];
    }
    return null;
}

/**
 * Sends a JSON response and terminates the script.
 */
function json_response($statusCode, $data) {
    header('Content-Type: application/json');
    http_response_code($statusCode);
    echo json_encode($data);
    exit();
}

/**
 * Checks if the current user has the required permission for a specific action on a module.
 * This is the live, database-driven permission check.
 *
 * @param string $moduleSlug The slug of the module (e.g., 'customers').
 * @param string $action The action to check ('view', 'create', 'update', 'delete').
 * @return bool True if the user has permission, false otherwise.
 */
function has_permission($moduleSlug, $action) {
    static $user_permissions = null; // Caches permissions for the request.
    
    $user = current_user();
    if (!$user) {
        return false; // No user, no permissions.
    }

    // Admins have god-mode. They can do anything.
    if ($user['role'] === 'admin') {
        return true;
    }

    // If permissions for this user haven't been loaded yet, fetch them from the DB.
    if ($user_permissions === null) {
        $user_permissions = [];
        $conn = get_db_connection();
        
        $sql = "SELECT m.slug, p.can_view, p.can_create, p.can_update, p.can_delete
                FROM permissions p
                JOIN modules m ON p.module_id = m.id
                WHERE p.user_id = ?";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('i', $user['id']);
        $stmt->execute();
        $result = $stmt->get_result();
        
        while ($row = $result->fetch_assoc()) {
            $user_permissions[$row['slug']] = $row;
        }
        $stmt->close();
    }

    // Now, check the cached permissions.
    $action_column = 'can_' . $action; // e.g., 'can_view'
    
    // Check if a permission entry exists for the module and if the specific action is allowed.
    if (isset($user_permissions[$moduleSlug]) && !empty($user_permissions[$moduleSlug][$action_column])) {
        return true;
    }

    // Default to deny if no specific permission is found.
    return false;
}

/**
 * Generates intelligent HTML for compact pagination links, preserving filters and sorting.
 *
 * @param int $total_items The total number of items.
 * @param int $current_page The current page number.
 * @param string $base_url The base URL for the page.
 * @param array $params The current URL parameters to preserve.
 * @param int $links_to_show The number of page links to show.
 * @return string The generated HTML for the pagination links.
 */
function generate_pagination_links($total_items, $current_page, $base_url, $params = [], $links_to_show = 5) {
    $total_pages = ceil($total_items / ITEMS_PER_PAGE);
    if ($total_pages <= 1) {
        return ''; // No pagination needed
    }

    // Unset current page from params to avoid duplicates in the URL
    unset($params['page']);

    $build_url = function($page) use ($base_url, $params) {
        $query_params = http_build_query(array_merge($params, ['page' => $page]));
        return url($base_url . '?' . $query_params);
    };

    $html = '<nav class="pagination-nav"><ul class="pagination">';

    // First and Previous links
    if ($current_page > 1) {
        $html .= '<li class="page-item"><a class="page-link" href="' . $build_url(1) . '">&laquo; First</a></li>';
        $html .= '<li class="page-item"><a class="page-link" href="' . $build_url($current_page - 1) . '">&lsaquo; Previous</a></li>';
    }

    // Determine the start and end page numbers to display
    $start = max(1, $current_page - floor($links_to_show / 2));
    $end = min($total_pages, $start + $links_to_show - 1);
    if ($end - $start + 1 < $links_to_show) {
        $start = max(1, $end - $links_to_show + 1);
    }
    
    if ($start > 1) {
         $html .= '<li class="page-item disabled"><span class="page-link">...</span></li>';
    }

    // Page number links
    for ($i = $start; $i <= $end; $i++) {
        $active_class = ($i == $current_page) ? 'active' : '';
        $html .= '<li class="page-item ' . $active_class . '"><a class="page-link" href="' . $build_url($i) . '">' . $i . '</a></li>';
    }
    
    if ($end < $total_pages) {
        $html .= '<li class="page-item disabled"><span class="page-link">...</span></li>';
    }

    // Next and Last links
    if ($current_page < $total_pages) {
        $html .= '<li class="page-item"><a class="page-link" href="' . $build_url($current_page + 1) . '">Next &rsaquo;</a></li>';
        $html .= '<li class="page-item"><a class="page-link" href="' . $build_url($total_pages) . '">Last &raquo;</a></li>';
    }

    $html .= '</ul></nav>';
    return $html;
}

/**
 * Generates a sortable table header link.
 *
 * @param string $column_name The name of the database column to sort by.
 * @param string $display_text The text to display for the header.
 * @param array $params The current URL parameters (sort, order, filters).
 * @param string $base_url The base URL for the page.
 * @return string The generated HTML for the table header.
 */
function generate_sortable_link($column_name, $display_text, $params, $base_url) {
    $current_sort = $params['sort'] ?? '';
    $current_order = $params['order'] ?? 'asc';
    
    $order = ($current_sort === $column_name && $current_order === 'asc') ? 'desc' : 'asc';
    $icon = '';
    if ($current_sort === $column_name) {
        $icon = ($current_order === 'asc') ? ' &#9650;' : ' &#9660;'; // Up/Down arrows
    }

    // Preserve existing filters and page number
    $query_params = http_build_query(array_merge($params, ['sort' => $column_name, 'order' => $order]));
    
    return '<a href="' . url($base_url . '?' . $query_params) . '">' . e($display_text) . $icon . '</a>';
}
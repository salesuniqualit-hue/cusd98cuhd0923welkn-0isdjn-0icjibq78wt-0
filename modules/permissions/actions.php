<?php
// modules/permissions/actions.php

/**
 * Fetches the list of users that the current user is allowed to manage permissions for.
 *
 * @param array $current_user The logged-in user's session data.
 * @return array Data for the main permissions page.
 */
function get_permissions_page_data($current_user) {
    $conn = get_db_connection();
    $data = [
        'page_title' => 'Set Permissions',
        'users' => []
    ];

    if ($current_user['role'] === 'admin') {
        $data['page_title'] = 'Set Permissions for Users';
        // --- UPDATED LOGIC ---
        // Admin manages permissions for Dealers and Internal Users.
        // We use a LEFT JOIN to get company_name for dealers.
        $sql = "SELECT u.id, u.name, u.email, u.role, d.company_name 
                FROM users u 
                LEFT JOIN dealers d ON u.dealer_id = d.id AND u.role = 'dealer'
                WHERE (u.role = 'dealer' OR u.role = 'internal_user') AND u.is_active = 1 
                ORDER BY u.role DESC, d.company_name ASC, u.name ASC";
        $stmt = $conn->prepare($sql);

    } elseif ($current_user['role'] === 'dealer') {
        $data['page_title'] = 'Set Permissions for Team Members';
        // Dealer manages permissions for their own team members.
        $sql = "SELECT id, name, email, role FROM users 
                WHERE role = 'team_member' AND dealer_id = ? AND is_active = 1 
                ORDER BY name ASC";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('i', $current_user['dealer_id']);
    } else {
        return $data; // Should not be reached due to router check
    }

    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $data['users'][] = $row;
    }
    $stmt->close();
    return $data;
}

/**
 * Fetches all data needed for the permission editor page for a specific user.
 *
 * @param int $user_id_to_manage The user whose permissions are being edited.
 * @param array $current_user The logged-in user.
 * @return array|null The data for the editor or null if permission is denied.
 */
function get_user_permissions_data($user_id_to_manage, $current_user) {
    $conn = get_db_connection();
    
    // Security Check: Verify that the current user is allowed to manage the target user.
    $user_to_manage = $conn->query("SELECT id, name, role, dealer_id FROM users WHERE id = $user_id_to_manage")->fetch_assoc();
    if (!$user_to_manage) return null;

    if ($current_user['role'] === 'admin' && !in_array($user_to_manage['role'], ['dealer', 'internal_user'])) return null; // Admin can manage dealers and internal users
    if ($current_user['role'] === 'dealer' && ($user_to_manage['role'] !== 'team_member' || $user_to_manage['dealer_id'] != $current_user['dealer_id'])) return null; // Dealer can only manage their team

    // Fetch all non-core modules that permissions can be set for.
    $modules = $conn->query("SELECT id, name, slug FROM modules WHERE is_core = 0 ORDER BY name ASC")->fetch_all(MYSQLI_ASSOC);
    
    // Fetch the user's current permissions and format them for easy lookup in the template.
    $current_permissions = [];
    $perm_result = $conn->query("SELECT * FROM permissions WHERE user_id = $user_id_to_manage");
    while($perm = $perm_result->fetch_assoc()) {
        $current_permissions[$perm['module_id']] = $perm;
    }

    return [
        'user_to_manage' => $user_to_manage,
        'modules' => $modules,
        'current_permissions' => $current_permissions
    ];
}

/**
 * Saves the permissions from the submitted form.
 *
 * @param array $data The $_POST data.
 * @param array $current_user The logged-in user.
 */
function handle_save_permissions($data, $current_user) {
    $conn = get_db_connection();
    $user_id = (int)$data['user_id'];
    $permissions = $data['permissions'] ?? [];

    // Security Check again on the server side
    $user_to_manage_data = get_user_permissions_data($user_id, $current_user);
    if (!$user_to_manage_data) {
        redirect('/permissions?error=permission_denied');
    }

    $conn->begin_transaction();
    try {
        // Step 1: Delete all existing permissions for this user.
        // This is simpler than checking for updates and handles unchecking boxes correctly.
        $stmt_delete = $conn->prepare("DELETE FROM permissions WHERE user_id = ?");
        $stmt_delete->bind_param('i', $user_id);
        $stmt_delete->execute();

        // Step 2: Insert the new permissions from the form.
        $stmt_insert = $conn->prepare("INSERT INTO permissions (user_id, module_id, can_view, can_create, can_update, can_delete) VALUES (?, ?, ?, ?, ?, ?)");
        
        foreach ($permissions as $module_id => $actions) {
            $can_view = isset($actions['view']) ? 1 : 0;
            $can_create = isset($actions['create']) ? 1 : 0;
            $can_update = isset($actions['update']) ? 1 : 0;
            $can_delete = isset($actions['delete']) ? 1 : 0;

            // Only insert a row if at least one permission is granted.
            if ($can_view || $can_create || $can_update || $can_delete) {
                $stmt_insert->bind_param('iiiiii', $user_id, $module_id, $can_view, $can_create, $can_update, $can_delete);
                $stmt_insert->execute();
            }
        }

        $conn->commit();
        redirect('/permissions?success=permissions_updated');

    } catch (Exception $e) {
        $conn->rollback();
        redirect('/permissions/user/' . $user_id . '?error=' . urlencode($e->getMessage()));
    }
}
<?php
// modules/referrers/actions.php

/**
 * Checks if a user has permission to access the referrer module.
 */
function has_referrer_permission($current_user) {
    if ($current_user['role'] === 'admin') {
        return true;
    }
    if ($current_user['role'] === 'dealer') {
        $conn = get_db_connection();
        $stmt = $conn->prepare("SELECT is_enabled FROM dealer_permissions WHERE dealer_id = ? AND permission_slug = 'manage_referrers'");
        $stmt->bind_param('i', $current_user['dealer_id']);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        return $result && $result['is_enabled'];
    }
    return false;
}

/**
 * Fetches all referrers created by the current user (or all for admin).
 */
function get_all_referrers($current_user) {
    $conn = get_db_connection();
    if ($current_user['role'] === 'admin') {
        $sql = "SELECT r.*, u.name as creator_name FROM referrers r JOIN users u ON r.created_by_user_id = u.id ORDER BY r.name ASC";
        $stmt = $conn->prepare($sql);
    } else { // Dealer
        $sql = "SELECT * FROM referrers WHERE created_by_user_id = ? ORDER BY name ASC";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('i', $current_user['id']);
    }
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

function get_referrer_by_id($id, $current_user) {
    $conn = get_db_connection();
    $stmt = $conn->prepare("SELECT * FROM referrers WHERE id = ?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $referrer = $stmt->get_result()->fetch_assoc();

    // Security check: ensure user owns this referrer or is admin
    if ($referrer && ($current_user['role'] === 'admin' || $referrer['created_by_user_id'] == $current_user['id'])) {
        return $referrer;
    }
    return null;
}

function handle_create_referrer($data, $current_user) {
    $conn = get_db_connection();
    $stmt = $conn->prepare("INSERT INTO referrers (name, phone, email, address, commission_rate, remarks, created_by_user_id) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param('ssssdsi', $data['name'], $data['phone'], $data['email'], $data['address'], $data['commission_rate'], $data['remarks'], $current_user['id']);
    if ($stmt->execute()) {
        redirect('/referrers?success=referrer_created');
    } else {
        redirect('/referrers/create?error=' . urlencode($stmt->error));
    }
}

function handle_update_referrer($id, $data, $current_user) {
    $referrer = get_referrer_by_id($id, $current_user);
    if (!$referrer) {
        redirect('/referrers?error=permission_denied');
    }
    $conn = get_db_connection();
    $stmt = $conn->prepare("UPDATE referrers SET name = ?, phone = ?, email = ?, address = ?, commission_rate = ?, remarks = ? WHERE id = ?");
    $stmt->bind_param('ssssdsi', $data['name'], $data['phone'], $data['email'], $data['address'], $data['commission_rate'], $data['remarks'], $id);
    if ($stmt->execute()) {
        redirect('/referrers?success=referrer_updated');
    } else {
         redirect("/referrers/{$id}/edit?error=" . urlencode($stmt->error));
    }
}

function handle_delete_referrer($id, $current_user) {
    $referrer = get_referrer_by_id($id, $current_user);
     if (!$referrer) {
        redirect('/referrers?error=permission_denied');
    }
    $conn = get_db_connection();
    $stmt = $conn->prepare("DELETE FROM referrers WHERE id = ?");
    $stmt->bind_param('i', $id);
    if ($stmt->execute()) {
        redirect('/referrers?success=referrer_deleted');
    } else {
        redirect('/referrers?error=' . urlencode($stmt->error));
    }
}

/**
 * Helper function to get referrers for a dropdown menu, scoped to the current user.
 *
 * @param array $current_user The logged-in user.
 * @return array A list of referrers.
 */
function get_all_referrers_for_dropdown($current_user) {
    $conn = get_db_connection();
    $referrers = [];

    if ($current_user['role'] === 'admin') {
        // Admin sees all referrers
        $sql = "SELECT id, name FROM referrers ORDER BY name ASC";
        $stmt = $conn->prepare($sql);
    } else {
        // Dealers see only referrers they have created
        $sql = "SELECT id, name FROM referrers WHERE created_by_user_id = ? ORDER BY name ASC";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('i', $current_user['id']);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $referrers[] = $row;
        }
    }
    $stmt->close();
    return $referrers;
}

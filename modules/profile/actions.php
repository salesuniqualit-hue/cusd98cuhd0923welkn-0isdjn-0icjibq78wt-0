<?php
// modules/profile/actions.php

/**
 * Fetches the complete profile data for the currently logged-in user.
 */
function get_profile_data($current_user) {
    $conn = get_db_connection();
    $user_id = $current_user['id'];
    $data = [];

    // Fetch base user data
    $stmt = $conn->prepare("SELECT id, name, email, role FROM users WHERE id = ?");
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $data['user'] = $stmt->get_result()->fetch_assoc();
    
    // If the user is a dealer, also fetch their company data
    if ($current_user['role'] === 'dealer') {
        $stmt = $conn->prepare("SELECT * FROM dealers WHERE user_id = ?");
        $stmt->bind_param('i', $user_id);
        $stmt->execute();
        $data['dealer'] = $stmt->get_result()->fetch_assoc();
    }
    return $data;
}

/**
 * Handles updating the user's profile information.
 */
function handle_update_profile($data, $current_user) {
    $conn = get_db_connection();
    $user_id = $current_user['id'];
    
    $conn->begin_transaction();
    try {
        // ... (Update 'users' table logic remains the same) ...

        // If the user is a dealer, update the 'dealers' table
        if ($current_user['role'] === 'dealer') {
            // --- NEW LOGO UPLOAD LOGIC ---
            $logo_path = null;
            if (isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
                // Create a directory for logos if it doesn't exist
                $upload_dir = '/assets/uploads/logos/';
                if (!is_dir(ROOT_PATH . $upload_dir)) {
                    mkdir(ROOT_PATH . $upload_dir, 0755, true);
                }
                
                // Create a unique filename
                $filename = 'dealer_' . $current_user['dealer_id'] . '_' . time() . '_' . basename($_FILES['logo']['name']);
                $target_path = ROOT_PATH . $upload_dir . $filename;
                
                // Move the uploaded file
                if (move_uploaded_file($_FILES['logo']['tmp_name'], $target_path)) {
                    $logo_path = $upload_dir . $filename;
                }
            }

            // Update the dealers table
            if ($logo_path) {
                // If a new logo was uploaded, update the path
                $stmt_dealer = $conn->prepare("UPDATE dealers SET company_name = ?, phone = ?, address = ?, logo_path = ? WHERE user_id = ?");
                $stmt_dealer->bind_param('ssssi', $data['company_name'], $data['phone'], $data['address'], $logo_path, $user_id);
                // --- NEW: Update session with new logo path ---
                $_SESSION['dealer_logo_path'] = $logo_path;
            } else {
                // Otherwise, update without changing the logo
                $stmt_dealer = $conn->prepare("UPDATE dealers SET company_name = ?, phone = ?, address = ? WHERE user_id = ?");
                $stmt_dealer->bind_param('sssi', $data['company_name'], $data['phone'], $data['address'], $user_id);
            }
            $stmt_dealer->execute();
        }

        $conn->commit();
        redirect('/profile?success=profile_updated');
    } catch (Exception $e) {
        $conn->rollback();
        redirect('/profile?error=' . urlencode($e->getMessage()));
    }
}
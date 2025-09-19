<?php
// modules/attendance/actions.php

/**
 * Checks if the user is eligible to use the attendance module.
 */
function is_attendance_user($current_user) {
    return in_array($current_user['role'], ['internal_user', 'team_member']);
}

/**
 * Checks if the user is a manager for the attendance module.
 */
function is_attendance_manager($current_user) {
    return in_array($current_user['role'], ['admin', 'dealer']);
}

/**
 * Fetches the user's current attendance status for today.
 */
function get_today_attendance($user_id) {
    $conn = get_db_connection();
    $stmt = $conn->prepare("SELECT * FROM attendance WHERE user_id = ? AND DATE(punch_in_time) = CURDATE() ORDER BY id DESC LIMIT 1");
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

/**
 * Fetches the user's last attendance record to check for missing punch-outs.
 */
function get_last_attendance($user_id) {
    $conn = get_db_connection();
    $stmt = $conn->prepare("SELECT * FROM attendance WHERE user_id = ? ORDER BY id DESC LIMIT 1");
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}


/**
 * Handles the punch-in action.
 */
function handle_punch_in($user_id, $location) {
    $today_attendance = get_today_attendance($user_id);
    if ($today_attendance) {
        redirect('/attendance?error=already_punched_in_today');
        return;
    }

    $last_attendance = get_last_attendance($user_id);
    if ($last_attendance && !$last_attendance['punch_out_time']) {
        redirect('/attendance?error=must_punch_out_first');
        return;
    }

    $conn = get_db_connection();
    $stmt = $conn->prepare("INSERT INTO attendance (user_id, punch_in_time, punch_in_location) VALUES (?, NOW(), ?)");
    $stmt->bind_param('is', $user_id, $location);
    $stmt->execute();
    redirect('/attendance?success=punched_in');
}

/**
 * Handles the punch-out action.
 */
function handle_punch_out($attendance_id, $user_id, $location) {
    $conn = get_db_connection();
    $stmt = $conn->prepare("UPDATE attendance SET punch_out_time = NOW(), punch_out_location = ? WHERE id = ? AND user_id = ?");
    $stmt->bind_param('sii', $location, $attendance_id, $user_id);
    $stmt->execute();
    redirect('/attendance?success=punched_out');
}

/**
 * Fetches a list of users for the report dropdown.
 */
function get_users_for_attendance_report($current_user) {
    $conn = get_db_connection();
    $users = [];
    // --- FIX IS HERE: Only managers should get a list of users ---
    if (!is_attendance_manager($current_user)) {
        return [];
    }

    if ($current_user['role'] === 'admin') {
        $sql = "SELECT id, name FROM users WHERE role = 'internal_user' AND is_active = 1 ORDER BY name ASC";
        $stmt = $conn->prepare($sql);
    } elseif ($current_user['role'] === 'dealer') {
        $sql = "SELECT id, name FROM users WHERE role = 'team_member' AND dealer_id = ? AND is_active = 1 ORDER BY name ASC";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('i', $current_user['dealer_id']);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $users[] = $row;
    }
    return $users;
}

/**
 * Generates the attendance report data.
 */
function get_attendance_report($from_date, $to_date, $user_ids) {
    if (empty($user_ids)) return [];
    
    $conn = get_db_connection();
    $placeholders = implode(',', array_fill(0, count($user_ids), '?'));
    $types = str_repeat('i', count($user_ids)) . 'ss';
    $params = array_merge($user_ids, [$from_date, $to_date]);

    $sql = "SELECT a.*, u.name as user_name, TIMEDIFF(punch_out_time, punch_in_time) as worked_hours 
            FROM attendance a
            JOIN users u ON a.user_id = u.id
            WHERE a.user_id IN ({$placeholders}) 
            AND DATE(a.punch_in_time) BETWEEN ? AND ?
            ORDER BY u.name, a.punch_in_time";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

function get_holidays($from_date, $to_date)
{
    $conn = get_db_connection();
    $stmt = $conn->prepare("SELECT * FROM holidays WHERE holiday_date BETWEEN ? AND ?");
    $stmt->bind_param('ss', $from_date, $to_date);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

function handle_add_holiday($holiday_date, $description)
{
    $conn = get_db_connection();
    $stmt = $conn->prepare("INSERT INTO holidays (holiday_date, description) VALUES (?, ?)");
    $stmt->bind_param('ss', $holiday_date, $description);
    $stmt->execute();
    redirect('/attendance/holidays?success=holiday_added');
}
<?php
// modules/attendance/routes.php

require_login();
require_once __DIR__ . '/actions.php';

$route = get_route();
$current_user = current_user();

if ($route === '/attendance') {
    if (!is_attendance_user($current_user) && !is_attendance_manager($current_user)) {
        redirect('/?error=permission_denied');
    }
    
    $page_title = 'Attendance';
    $todays_record = is_attendance_user($current_user) ? get_today_attendance($current_user['id']) : null;
    $content_view = __DIR__ . '/templates/index.php';
    require_once __DIR__ . '/../../templates/layout.php';
}
elseif ($route === '/attendance/punch_in' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!is_attendance_user($current_user)) redirect('/?error=permission_denied');
    handle_punch_in($current_user['id'], $_POST['location']);
}
elseif ($route === '/attendance/punch_out' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!is_attendance_user($current_user)) redirect('/?error=permission_denied');
    handle_punch_out($_POST['attendance_id'], $current_user['id'], $_POST['location']);
}
elseif ($route === '/attendance/report') {
    // --- FIX IS HERE: Allow both managers and users to see the report page ---
    if (!is_attendance_manager($current_user) && !is_attendance_user($current_user)) {
        redirect('/?error=permission_denied');
    }
    
    $page_title = 'Attendance Report';
    $report_users = get_users_for_attendance_report($current_user);
    $report_data = [];

    if ($_SERVER['REQUEST_METHOD'] === 'GET' && (!empty($_GET['user_ids']) || is_attendance_user($current_user))) {
        // If the user is not a manager, force the report to be for their own ID.
        $user_ids = is_attendance_manager($current_user) ? $_GET['user_ids'] : [$current_user['id']];
        $report_data = get_attendance_report($_GET['from_date'], $_GET['to_date'], $user_ids);
    }
    
    $content_view = __DIR__ . '/templates/report.php';
    require_once __DIR__ . '/../../templates/layout.php';
} elseif ($route === '/attendance/holidays' && $_SERVER['REQUEST_METHOD'] === 'GET') {
    $page_title = 'Manage Holidays';
    $holidays = get_holidays(date('Y-m-d'), date('Y-m-d', strtotime('+1 year')));
    $content_view = __DIR__ . '/templates/holidays.php';
    require_once __DIR__ . '/../../templates/layout.php';
} elseif ($route === '/attendance/holidays/add' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    handle_add_holiday($_POST['holiday_date'], $_POST['description']);
}
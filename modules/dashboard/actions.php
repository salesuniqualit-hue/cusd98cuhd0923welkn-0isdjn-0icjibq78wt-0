<?php
// modules/dashboard/actions.php

/**
 * Fetches data for the dashboard based on the user's role.
 *
 * @param array $user An array containing the current user's data (id, role, etc.).
 * @return array An array of data to be displayed on the dashboard.
 */
function get_dashboard_data($user) {
    // This is a placeholder. In the future, this function will run complex
    // database queries to get the real statistics you requested.
    
    $data = [
        'stats' => [],
        'user_name' => $_SESSION['user_name'] ?? 'User' // We'll add user_name to the session later
    ];

    if ($user['role'] === 'admin') {
        // Data for the Admin dashboard
        $data['stats'] = [
            'Total Customers' => ['value' => '1,250', 'change' => '+50 this month'],
            'Total Orders' => ['value' => '4,820', 'change' => '+120 this month'],
            'Active Subscriptions' => ['value' => '3,500', 'change' => '15 expiring soon'],
            'Active Trials' => ['value' => '215', 'change' => '5 expiring tomorrow'],
        ];
    } elseif ($user['role'] === 'dealer') {
        // Data for the Dealer dashboard
        $data['stats'] = [
            'Your Customers' => ['value' => '85', 'change' => '+5 this month'],
            'Your Orders' => ['value' => '210', 'change' => '+12 this month'],
            'Your Subscriptions' => ['value' => '150', 'change' => '4 expiring soon'],
            'Your Trials' => ['value' => '15', 'change' => '1 expiring tomorrow'],
        ];
    }
    // You can add more roles like 'team_member' or 'internal_user' here later.

    return $data;
}
?>
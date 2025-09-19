<?php
// modules/reports/actions.php

/**
 * Defines all available reports and filters them based on the user's role.
 *
 * @param array $current_user The logged-in user's session data.
 * @return array A list of reports the user is allowed to see.
 */
function get_available_reports($current_user) {
    // Master list of all possible reports in the system.
    $all_reports = [
        [
            'slug' => 'subscription_summary',
            'title' => 'Subscription Summary',
            'description' => 'An overview of all active, expired, and expiring subscriptions.',
            'min_role' => ['admin', 'dealer'] // Both admins and dealers can see this.
        ],
        [
            'slug' => 'trial_summary',
            'title' => 'Trial Summary',
            'description' => 'An overview of all active, expired, and expiring trials.',
            'min_role' => ['admin', 'dealer']
        ],
        [
            'slug' => 'dealer_performance',
            'title' => 'Dealer Performance',
            'description' => 'A breakdown of sales and order volume by dealer.',
            'min_role' => ['admin'] // Only admins can see this.
        ],
        [
            'slug' => 'sales_overview',
            'title' => 'Sales Overview (BI)',
            'description' => 'Visual charts and graphs for sales performance analysis.',
            'min_role' => ['admin', 'dealer']
        ],
    ];

    $available_reports = [];
    foreach ($all_reports as $report) {
        if (in_array($current_user['role'], $report['min_role'])) {
            $available_reports[] = $report;
        }
    }
    return $available_reports;
}

function generate_sales_overview_report($current_user, $filters) {
    $conn = get_db_connection();
    $title = 'Sales Overview';
    
    // Monthly Sales Data
    $monthly_sales_sql = "SELECT DATE_FORMAT(order_date, '%Y-%m') as month, SUM(rate) as total_sales FROM orders GROUP BY month ORDER BY month ASC";
    $monthly_sales_result = $conn->query($monthly_sales_sql);
    $monthly_sales_data = [];
    while($row = $monthly_sales_result->fetch_assoc()) {
        $monthly_sales_data['labels'][] = $row['month'];
        $monthly_sales_data['data'][] = $row['total_sales'];
    }

    // Top 5 SKUs by Sales
    $top_skus_sql = "SELECT s.name as sku_name, SUM(o.rate) as total_sales FROM orders o JOIN skus s ON o.sku_id = s.id GROUP BY s.id ORDER BY total_sales DESC LIMIT 5";
    $top_skus_result = $conn->query($top_skus_sql);
    $top_skus_data = [];
    while($row = $top_skus_result->fetch_assoc()) {
        $top_skus_data['labels'][] = $row['sku_name'];
        $top_skus_data['data'][] = $row['total_sales'];
    }
    
    // Top 5 Dealers by Sales
    $top_dealers_data = [];
    if ($current_user['role'] === 'admin') {
        $top_dealers_sql = "SELECT d.company_name, SUM(o.rate) as total_sales FROM orders o JOIN dealers d ON o.dealer_id = d.id GROUP BY d.id ORDER BY total_sales DESC LIMIT 5";
        $top_dealers_result = $conn->query($top_dealers_sql);
        while($row = $top_dealers_result->fetch_assoc()) {
            $top_dealers_data['labels'][] = $row['company_name'];
            $top_dealers_data['data'][] = $row['total_sales'];
        }
    }
    
    $data = [
        'monthly_sales' => $monthly_sales_data,
        'top_skus' => $top_skus_data,
        'top_dealers' => $top_dealers_data
    ];

    ob_start();
    include __DIR__ . '/templates/_sales_overview.php';
    $html = ob_get_clean();

    return ['title' => $title, 'html' => $html];
}

/**
 * A master function that calls the correct specific function to generate a report.
 *
 * @param string $report_slug The unique identifier for the report.
 * @param array $current_user The logged-in user.
 * @param array $filters URL query parameters for filtering (e.g., dealer_id).
 * @return array|null The report data or null if access is denied.
 */
function generate_report($report_slug, $current_user, $filters) {
    // Check if the user has permission to view this report type
    $available_reports = get_available_reports($current_user);
    $report_exists = false;
    foreach($available_reports as $report) {
        if ($report['slug'] === $report_slug) {
            $report_exists = true;
            break;
        }
    }
    if (!$report_exists) {
        return null; // Permission denied
    }

    // Call the specific function for the requested report
    switch ($report_slug) {
        case 'subscription_summary':
            return generate_subscription_summary_report($current_user, $filters);
        case 'trial_summary':
            // TODO: Create a similar function for trials
            return ['title' => 'Trial Summary', 'html' => '<p>This report is under construction.</p>'];
        case 'dealer_performance':
            // TODO: Create the dealer performance report function
            return ['title' => 'Dealer Performance', 'html' => '<p>This report is under construction.</p>'];
        case 'sales_overview':
            return generate_sales_overview_report($current_user, $filters);
        default:
            return null; // Report not found
    }
}


/**
 * Generates the complex Subscription Summary report.
 *
 * @param array $current_user The logged-in user.
 * @param array $filters Filters from the URL.
 * @return array The report title and generated HTML.
 */
function generate_subscription_summary_report($current_user, $filters) {
    $conn = get_db_connection();
    $title = 'Subscription Summary';
    $where_clause = "WHERE s.type = 'paid'";
    
    // Filter by dealer if the current user is a dealer, or if an admin selects one.
    $dealer_id = $filters['dealer_id'] ?? ($current_user['role'] === 'dealer' ? $current_user['dealer_id'] : null);
    if ($dealer_id) {
        $where_clause .= " AND o.dealer_id = " . (int)$dealer_id;
        $dealer_info = $conn->query("SELECT company_name FROM dealers WHERE id = " . (int)$dealer_id)->fetch_assoc();
        $title .= ' for ' . e($dealer_info['company_name']);
    }

    // This single, powerful SQL query calculates all the required stats using conditional aggregation.
    $sql = "
        SELECT
            COUNT(*) AS total,
            SUM(CASE WHEN s.end_date >= CURDATE() OR s.end_date IS NULL THEN 1 ELSE 0 END) AS running,

            SUM(CASE WHEN s.end_date < CURDATE() AND s.end_date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY) THEN 1 ELSE 0 END) AS expired_7_days,
            SUM(CASE WHEN s.end_date < DATE_SUB(CURDATE(), INTERVAL 7 DAY) AND s.end_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY) THEN 1 ELSE 0 END) AS expired_8_30_days,
            SUM(CASE WHEN s.end_date < DATE_SUB(CURDATE(), INTERVAL 30 DAY) AND s.end_date >= DATE_SUB(CURDATE(), INTERVAL 365 DAY) THEN 1 ELSE 0 END) AS expired_31_365_days,
            SUM(CASE WHEN s.end_date < DATE_SUB(CURDATE(), INTERVAL 365 DAY) THEN 1 ELSE 0 END) AS expired_over_1_year,

            SUM(CASE WHEN s.end_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY) THEN 1 ELSE 0 END) AS expiring_7_days,
            SUM(CASE WHEN s.end_date BETWEEN DATE_ADD(CURDATE(), INTERVAL 8 DAY) AND DATE_ADD(CURDATE(), INTERVAL 30 DAY) THEN 1 ELSE 0 END) AS expiring_8_30_days
        FROM subscriptions s
        JOIN orders o ON s.order_id = o.id
        {$where_clause}
    ";
    
    $stats = $conn->query($sql)->fetch_assoc();

    // Use output buffering to capture HTML from a separate partial file.
    ob_start();
    include __DIR__ . '/templates/_subscription_summary.php';
    $html = ob_get_clean();

    return ['title' => $title, 'html' => $html];
}
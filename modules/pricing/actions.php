<?php
// modules/pricing/actions.php

/**
 * Fetches all SKUs and their most recent standard prices.
 *
 * @return array A list of SKUs with pricing information.
 */
function get_all_skus_with_prices() {
    $conn = get_db_connection();
    // This complex query first finds the most recent applicable date for each SKU's standard price,
    // then joins back to get the price details for that specific date.
    $sql = "SELECT 
                s.id, s.name, s.is_yearly, s.is_perpetual,
                sp.price_yearly, sp.price_perpetual, sp.applicable_date
            FROM skus s
            LEFT JOIN (
                SELECT sku_id, MAX(applicable_date) as max_date
                FROM sku_standard_prices
                WHERE applicable_date <= CURDATE()
                GROUP BY sku_id
            ) AS latest_dates ON s.id = latest_dates.sku_id
            LEFT JOIN sku_standard_prices sp ON s.id = sp.sku_id AND sp.applicable_date = latest_dates.max_date
            ORDER BY s.name ASC";
            
    $result = $conn->query($sql);
    return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
}

/**
 * Fetches a complete price list for a specific dealer, showing their price against the standard price.
 *
 * @param int $dealer_id The ID of the dealer.
 * @return array A list of SKUs with dealer-specific and standard pricing.
 */
function get_dealer_price_list($dealer_id) {
    $conn = get_db_connection();
    // This query is similar to the one above but also joins the dealer_price_lists table.
    $sql = "SELECT 
                s.id, s.name, s.is_yearly, s.is_perpetual,
                sp.price_yearly AS standard_price_yearly, 
                sp.price_perpetual AS standard_price_perpetual,
                dpl.price_yearly AS dealer_price_yearly, 
                dpl.price_perpetual AS dealer_price_perpetual,
                dpl.applicable_date
            FROM skus s
            LEFT JOIN (
                SELECT sku_id, MAX(applicable_date) AS max_date FROM sku_standard_prices WHERE applicable_date <= CURDATE() GROUP BY sku_id
            ) AS latest_sp ON s.id = latest_sp.sku_id
            LEFT JOIN sku_standard_prices sp ON s.id = sp.sku_id AND sp.applicable_date = latest_sp.max_date
            LEFT JOIN (
                SELECT sku_id, MAX(applicable_date) AS max_date FROM dealer_price_lists WHERE dealer_id = ? AND applicable_date <= CURDATE() GROUP BY sku_id
            ) AS latest_dpl ON s.id = latest_dpl.sku_id
            LEFT JOIN dealer_price_lists dpl ON s.id = dpl.sku_id AND dpl.applicable_date = latest_dpl.max_date AND dpl.dealer_id = ?
            ORDER BY s.name ASC";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param('ii', $dealer_id, $dealer_id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
}

/**
 * Handles updating the standard price list from a form submission.
 *
 * @param array $data The $_POST data.
 */
function handle_update_standard_prices($data) {
    $conn = get_db_connection();
    $applicable_date = $data['applicable_date'];
    $prices = $data['prices'];

    $stmt = $conn->prepare("INSERT INTO sku_standard_prices (sku_id, applicable_date, price_yearly, price_perpetual) VALUES (?, ?, ?, ?)");

    foreach ($prices as $sku_id => $price) {
        $price_yearly = !empty($price['yearly']) ? $price['yearly'] : null;
        $price_perpetual = !empty($price['perpetual']) ? $price['perpetual'] : null;
        // Only insert if at least one price is set.
        if ($price_yearly !== null || $price_perpetual !== null) {
            $stmt->bind_param('isdd', $sku_id, $applicable_date, $price_yearly, $price_perpetual);
            $stmt->execute();
        }
    }
    $stmt->close();
    redirect('/pricing?success=standard_prices_updated');
}

/**
 * Handles updating a dealer-specific price list.
 *
 * @param array $data The $_POST data.
 */
function handle_update_dealer_prices($data) {
    $conn = get_db_connection();
    $dealer_id = $data['dealer_id'];
    $applicable_date = $data['applicable_date'];
    $prices = $data['prices'];

    $stmt = $conn->prepare("INSERT INTO dealer_price_lists (dealer_id, sku_id, applicable_date, price_yearly, price_perpetual) VALUES (?, ?, ?, ?, ?)");

    foreach ($prices as $sku_id => $price) {
        $price_yearly = !empty($price['yearly']) ? $price['yearly'] : null;
        $price_perpetual = !empty($price['perpetual']) ? $price['perpetual'] : null;
        if ($price_yearly !== null || $price_perpetual !== null) {
            $stmt->bind_param('iisdd', $dealer_id, $sku_id, $applicable_date, $price_yearly, $price_perpetual);
            $stmt->execute();
        }
    }
    $stmt->close();
    redirect('/pricing?dealer_id=' . $dealer_id . '&success=dealer_prices_updated');
}

/**
 * Handles the bulk price revision logic.
 *
 * @param array $data The $_POST data from the revision form.
 */
function handle_price_revision($data) {
    // This is a complex feature. A full implementation would require careful transaction
    // handling and fetching current prices before applying changes.
    // For now, this redirects with a "not implemented" message.
    // TODO: Implement the full price revision logic.
    redirect('/pricing/revise?info=feature_not_implemented');
}
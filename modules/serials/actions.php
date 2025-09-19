<?php
// modules/serials/actions.php

/**
 * Fetches all data for the Manage Serials page for a specific customer.
 *
 * @param int $customer_id The ID of the customer.
 * @return array
 */
function get_customer_serials_data($customer_id) {
    $conn = get_db_connection();

    // Fetch the customer's details for the page title
    $stmt_customer = $conn->prepare("SELECT name FROM customers WHERE id = ?");
    $stmt_customer->bind_param('i', $customer_id);
    $stmt_customer->execute();
    $customer = $stmt_customer->get_result()->fetch_assoc();

    if (!$customer) {
        return null; // Customer not found
    }

    // Fetch all serials for this customer
    $stmt_serials = $conn->prepare("SELECT * FROM customer_serials WHERE customer_id = ? ORDER BY created_at DESC");
    $stmt_serials->bind_param('i', $customer_id);
    $stmt_serials->execute();
    $serials = $stmt_serials->get_result()->fetch_all(MYSQLI_ASSOC);

    return [
        'customer' => $customer,
        'serials' => $serials,
    ];
}

/**
 * Handles adding a new serial number for a customer.
 *
 * @param array $data The $_POST data.
 * @param int $customer_id The ID of the customer.
 */
function handle_add_serial($data, $customer_id) {
    $conn = get_db_connection();
    $serial_number = trim($data['serial_number']);

    if (empty($serial_number)) {
        redirect('/serials/customer/' . $customer_id . '?error=serial_number_required');
        return;
    }

    $stmt = $conn->prepare("INSERT INTO customer_serials (customer_id, serial_number) VALUES (?, ?)");
    $stmt->bind_param('is', $customer_id, $serial_number);

    if ($stmt->execute()) {
        redirect('/serials/customer/' . $customer_id . '?success=serial_added');
    } else {
        redirect('/serials/customer/' . $customer_id . '?error=' . urlencode($stmt->error));
    }
}

/**
 * Handles deleting a customer serial number.
 *
 * @param int $serial_id The ID of the serial to delete.
 * @param int $customer_id The ID of the customer for redirection.
 */
function handle_delete_serial($serial_id, $customer_id) {
    $conn = get_db_connection();
    // Note: You might want to add a permission check here to ensure the user
    // can manage this specific customer before deleting.
    $stmt = $conn->prepare("DELETE FROM customer_serials WHERE id = ?");
    $stmt->bind_param('i', $serial_id);

    if ($stmt->execute()) {
        redirect('/serials/customer/' . $customer_id . '?success=serial_deleted');
    } else {
        redirect('/serials/customer/' . $customer_id . '?error=' . urlencode($stmt->error));
    }
}
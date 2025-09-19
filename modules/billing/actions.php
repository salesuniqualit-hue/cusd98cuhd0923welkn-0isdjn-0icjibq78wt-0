<?php
// modules/billing/actions.php

/**
 * Fetches all data needed for the billing page.
 *
 * @param int $dealer_id The ID of the dealer.
 * @return array
 */
function get_billing_page_data($dealer_id) {
    $conn = get_db_connection();

    // Fetch the most recent billing address
    $stmt_billing = $conn->prepare("SELECT * FROM dealer_billing_history WHERE dealer_id = ? ORDER BY created_at DESC LIMIT 1");
    $stmt_billing->bind_param('i', $dealer_id);
    $stmt_billing->execute();
    $current_billing = $stmt_billing->get_result()->fetch_assoc();

    // Fetch all payment methods
    $stmt_payment = $conn->prepare("SELECT * FROM dealer_payment_methods WHERE dealer_id = ? ORDER BY is_preferred DESC, id ASC");
    $stmt_payment->bind_param('i', $dealer_id);
    $stmt_payment->execute();
    $payment_methods = $stmt_payment->get_result()->fetch_all(MYSQLI_ASSOC);

    return [
        'current_billing' => $current_billing,
        'payment_methods' => $payment_methods,
    ];
}

/**
 * Handles saving the revised billing information.
 * This always creates a new record to maintain history.
 *
 * @param array $data The $_POST data.
 * @param int $dealer_id The ID of the dealer.
 */
function handle_save_billing_info($data, $dealer_id) {
    $conn = get_db_connection();
    $stmt = $conn->prepare(
        "INSERT INTO dealer_billing_history (dealer_id, billing_name, address, gstin, state, pan, cin, email) VALUES (?, ?, ?, ?, ?, ?, ?, ?)"
    );
    $stmt->bind_param(
        'isssssss',
        $dealer_id,
        $data['billing_name'],
        $data['address'],
        $data['gstin'],
        $data['state'],
        $data['pan'],
        $data['cin'],
        $data['email']
    );

    if (!$stmt->execute()) {
        redirect('/billing?error=' . urlencode($stmt->error));
    }
}

/**
 * Handles adding or updating a payment method.
 *
 * @param array $data The $_POST data.
 * @param int $dealer_id The ID of the dealer.
 */
function handle_save_payment_method($data, $dealer_id) {
    $conn = get_db_connection();
    $conn->begin_transaction();

    try {
        $is_preferred = isset($data['is_preferred']) ? 1 : 0;
        $payment_id = isset($data['payment_id']) && !empty($data['payment_id']) ? (int)$data['payment_id'] : 0;
        
        // If a method is marked as preferred, unset the flag on all other methods for this dealer.
        if ($is_preferred) {
            $stmt_unmark = $conn->prepare("UPDATE dealer_payment_methods SET is_preferred = 0 WHERE dealer_id = ?");
            $stmt_unmark->bind_param('i', $dealer_id);
            $stmt_unmark->execute();
        }

        if ($payment_id > 0) {
            // Update existing payment method
            $stmt = $conn->prepare(
                "UPDATE dealer_payment_methods SET payment_bank = ?, account_no = ?, branch = ?, ifsc_code = ?, upi_vpa = ?, is_preferred = ? WHERE id = ? AND dealer_id = ?"
            );
            $stmt->bind_param(
                'sssssiii',
                $data['payment_bank'],
                $data['account_no'],
                $data['branch'],
                $data['ifsc_code'],
                $data['upi_vpa'],
                $is_preferred,
                $payment_id,
                $dealer_id
            );
        } else {
            // Insert new payment method
            $stmt = $conn->prepare(
                "INSERT INTO dealer_payment_methods (dealer_id, payment_bank, account_no, branch, ifsc_code, upi_vpa, is_preferred) VALUES (?, ?, ?, ?, ?, ?, ?)"
            );
            $stmt->bind_param(
                'isssssi',
                $dealer_id,
                $data['payment_bank'],
                $data['account_no'],
                $data['branch'],
                $data['ifsc_code'],
                $data['upi_vpa'],
                $is_preferred
            );
        }
        try{
            $stmt->execute();
        }
        catch (Exception $e)
        {
            die($e->getMessage());
        }
        $conn->commit();
        redirect('/billing?success=payment_method_saved');

    } catch (Exception $e) {
        $conn->rollback();
        redirect('/billing?error=' . urlencode($e->getMessage()));
    }
}

/**
 * Handles deleting a payment method.
 *
 * @param int $payment_id The ID of the payment method to delete.
 * @param int $dealer_id The ID of the current dealer for security.
 */
function handle_delete_payment_method($payment_id, $dealer_id) {
    $conn = get_db_connection();
    $stmt = $conn->prepare("DELETE FROM dealer_payment_methods WHERE id = ? AND dealer_id = ?");
    $stmt->bind_param('ii', $payment_id, $dealer_id);
    if ($stmt->execute()) {
        redirect('/billing?success=payment_method_deleted');
    } else {
        redirect('/billing?error=' . urlencode($stmt->error));
    }
}
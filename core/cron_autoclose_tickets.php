// salesuniqualit-hue/97v6g6r9sx-cdiwe56e21fvbc-9qg87g/97v6g6r9sx-cdiwe56e21fvbc-9qg87g-c51efed1d01096a2f6b18a608f5ef4b422d0ee00/core/cron_autoclose_tickets.php
<?php
// This script is intended to be run by a cron job, e.g., once a day.
// It will automatically close tickets that have been inactive for a certain period.

require_once __DIR__ . '/bootstrap.php';

function auto_close_inactive_tickets() {
    $conn = get_db_connection();
    $auto_close_days = defined('TICKET_AUTO_CLOSE_DAYS') ? TICKET_AUTO_CLOSE_DAYS : 7;

    // Find tickets that are 'open' or 'in_progress' and have not been updated for the specified number of days.
    $sql = "SELECT t.id FROM tickets t
            LEFT JOIN ticket_replies tr ON t.id = tr.ticket_id
            WHERE t.status IN ('open', 'in_progress')
            GROUP BY t.id
            HAVING MAX(tr.created_at) < NOW() - INTERVAL ? DAY";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $auto_close_days);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $ticket_id = $row['id'];

        // Add a reply indicating auto-closure
        $reply_text = "This ticket has been automatically closed due to inactivity.";
        $stmt_reply = $conn->prepare("INSERT INTO ticket_replies (ticket_id, user_id, reply_text) VALUES (?, 1, ?)"); // Assuming user_id 1 is the system/admin
        $stmt_reply->bind_param('is', $ticket_id, $reply_text);
        $stmt_reply->execute();

        // Update the ticket status to 'closed'
        $stmt_close = $conn->prepare("UPDATE tickets SET status = 'closed', updated_at = NOW() WHERE id = ?");
        $stmt_close->bind_param('i', $ticket_id);
        $stmt_close->execute();

        echo "Ticket #{$ticket_id} has been auto-closed.\n";
    }

    $stmt->close();
}

auto_close_inactive_tickets();
?>
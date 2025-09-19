<?php
// core/database.php

/**
 * Establishes a connection to the database or returns the existing connection.
 *
 * This function uses a static variable to ensure that the database connection
 * is created only once per request, implementing the Singleton pattern.
 * It fetches the database credentials from the config.php file.
 *
 * @return mysqli A mysqli database connection object.
 */
function get_db_connection() {
    // The static variable holds the connection object. It persists across
    // function calls within the same request.
    static $conn;

    // If the connection hasn't been established yet, create it.
    if ($conn === null) {
        // Create a new mysqli object with credentials from the config file.
        // The '@' symbol suppresses the default PHP warning on connection failure,
        // allowing for custom error handling below.
        @$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

        // Check if the connection failed.
        if ($conn->connect_error) {
            // Log the detailed error to the server's error log for debugging.
            // It's crucial not to show detailed SQL errors to the end-user
            // in a production environment for security reasons.
            error_log("Database Connection Failed: " . $conn->connect_error);
            
            // Display a user-friendly, generic error message and stop script execution.
            // In a more complex application, you might redirect to an error page.
            die("There was a problem connecting to the database. Please try again later.");
        }

        // Set the character set to utf8mb4 to support a wide range of characters,
        // including emojis, which is a modern best practice.
        $conn->set_charset("utf8mb4");
    }

    // Return the active database connection.
    return $conn;
}
?>
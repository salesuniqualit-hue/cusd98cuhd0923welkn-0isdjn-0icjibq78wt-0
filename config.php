<?php
// config.php

// -- DATABASE SETTINGS --
// Replace with your actual database credentials.
// define('DB_HOST', 'localhost');
// define('DB_USER', 'root');
// define('DB_PASS', 'XKs24#yVGB2d12231'); // Add your database password here
// define('DB_NAME', 'dmdl-mod');
define('DB_HOST', 'uniqual-it.com');
define('DB_USER', 'uniqutec_dmdl');
define('DB_PASS', 'HxKe%)kQb}(%]o1be#'); // Add your database password here
define('DB_NAME', 'uniqutec_dmdldb');

// -- APPLICATION SETTINGS --
// The name of your application.
define('APP_NAME', 'Dealer Portal');

define('CMP_NAME', 'Uniqual IT Solutions');
define('BRAND_NAME', 'UQIT');
define('PROD_NAME', 'Product Name');

define('EMAIL_FROM', 'crm@uniqual-it.com');
define('EMAIL_NAME', 'Uniqual IT Solutions');
define('SUPPORT_EMAIL', 'helpdesk@uniqual-it.com');
define('SUPPORT_PHONE', '+91 97999 81781');
define('SUPPORT_MOBILE', '+91 97999 81781');
define('ENQUIRY_EMAIL', 'enquiry@uniqual-it.com');
define('SALES_EMAIL', 'sales@uniqual-it.com');

// The base path of your application relative to the web root.
// If your app is at http://localhost/DMDL, this should be '/DMDL'.
// If it's at the root, this should be an empty string ''.
define('BASE_PATH', '/');

// -- SECURITY SETTINGS --
// Minimum required password length for all users.
define('MIN_PASSWORD_LENGTH', 8);

// Number of items to display per page on list views.
define('ITEMS_PER_PAGE', 10);

// -- CACHE CONTROL --
// Instructs browsers and proxies not to cache pages.
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

// -- ERROR REPORTING --
// Set to E_ALL for development to see all errors and warnings.
// Set to 0 for production to hide errors from users.
//error_reporting(E_ALL);
ini_set('display_errors', 0);

// -- EMAIL (SMTP) SETTINGS --
define('SMTP_HOST', 'mail.uniqual-it.com');         // e.g., 'smtp.gmail.com' or your web host's mail server
define('SMTP_USERNAME', 'tallynet@uniqual-it.com');
define('SMTP_PASSWORD', 'eUMV%2}o0B0D');
define('SMTP_PORT', 465);                     // 587 for TLS, 465 for SSL
define('SMTP_SECURE', 'ssl');                 // 'tls' or 'ssl'
define('SMTP_FROM_EMAIL', 'no-reply@example.com');
define('SMTP_FROM_NAME', APP_NAME);

// -- NEW: ORDER PROCESSING SETTINGS --
// Set to true to automatically process new orders, allowing dealers to request trials immediately.
// Set to false to require an admin to manually process orders before trials can be requested.
define('AUTO_PROCESS_ORDERS', true);

// -- NEW: TRIAL REQUEST SETTINGS --
// Set to true to automatically approve and activate new trial requests upon submission.
// Set to false to require an admin to manually approve trial requests.
define('AUTO_APPROVE_TRIALS', true);

// The number of days before a paid subscription expires to show the "Renew" button.
define('SUBSCRIPTION_RENEWAL_WINDOW_DAYS', 30);

// -- NEW: TICKET SETTINGS --
// Number of days of inactivity before a ticket is automatically closed.
define('TICKET_AUTO_CLOSE_DAYS', 15);
?>
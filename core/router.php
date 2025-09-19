<?php
// core/router.php

/**
 * Parses the incoming request URI to determine the clean application route.
 * This robust version correctly handles subdirectories.
 */
function get_route() {
    // Get the full request path, without any query string (e.g., /DMDL-test/customers)
    $request_path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    
    // Get the path to the directory containing the main index.php
    // In your case, this will be '/DMDL-test'
    $base_path = dirname($_SERVER['SCRIPT_NAME']);
    
    // If the base path is just a slash, we are at the root, so make it an empty string
    // to avoid double slashes later.
    if ($base_path === '/' || $base_path === '\\') {
        $base_path = '';
    }

    // Remove the base path from the beginning of the request path to get the clean route.
    // e.g., /DMDL-test/customers becomes /customers
    $route = substr($request_path, strlen($base_path));

    // Return '/' for the homepage, otherwise return the calculated route.
    return $route ?: '/';
}

/**
 * Dispatches the request to the appropriate module's router.
 */
function dispatch() {
    die("as");
    $route = get_route();
    $parts = explode('/', trim($route, '/'));
    $module_slug = !empty($parts[0]) ? $parts[0] : 'dashboard';

    // --- FIX IS HERE ---
    // Define all routes that belong to the 'auth' module.
    $auth_routes = ['login', 'logout', 'forgot_password', 'reset_password', 'setup_2fa', 'verify_2fa'];

    if (in_array($module_slug, $auth_routes)) {
        $module_slug = 'auth';
    }
    // --- END OF FIX ---

    $module_router_file = ROOT_PATH . '/modules/' . $module_slug . '/routes.php';
    $normalized_path = str_replace('/', DIRECTORY_SEPARATOR, $module_router_file);
    
    if (file_exists($normalized_path)) {
        require_once $normalized_path;
    } else {
        http_response_code(404);
        echo "<h1>404 Not Found</h1><p>The page you are looking for does not exist.</p>";
        // For debugging:
        // echo "<p>Debug Info:<br>Route: {$route}<br>Module: {$module_slug}<br>Path: {$normalized_path}</p>";
        exit();
    }
}
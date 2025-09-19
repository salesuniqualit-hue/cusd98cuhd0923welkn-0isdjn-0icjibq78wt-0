<?php
// core/bootstrap.php

// Define a reliable constant for the project's root directory.
// realpath() resolves all symbolic links and relative path parts (like '..').
define('ROOT_PATH', realpath(__DIR__ . '/..'));

// 1. Load the main configuration file first.
require_once ROOT_PATH . '/config.php';

// 2. Load the core application components.
require_once ROOT_PATH . '/core/session.php';
require_once ROOT_PATH . '/core/database.php';
require_once ROOT_PATH . '/core/functions.php';

?>
<?php
// index.php

// The path to the bootstrap file is now directly in the 'core' folder.
require_once __DIR__ . '/core/bootstrap.php';

// The path to the router is also in the 'core' folder.
require_once __DIR__ . '/core/router.php';
dispatch();
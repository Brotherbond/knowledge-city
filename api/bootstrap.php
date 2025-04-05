<?php

declare (strict_types = 1);

namespace api;

use api\config\Database;

// Check if autoloader exists
if (! file_exists(__DIR__ . '/vendor/autoload.php')) {
    die("Autoloader not found. Please run 'composer dump-autoload'.\n");
}

require_once __DIR__ . '/vendor/autoload.php';

// Initialize database connection
$db            = new Database();
$GLOBALS['db'] = $db->getConnection();

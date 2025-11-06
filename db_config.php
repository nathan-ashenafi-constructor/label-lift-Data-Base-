<?php
// db_config.php - Database configuration constants

// Only define if not already defined (prevent redefinition errors)
if (!defined('DB_HOST')) {
    define('DB_HOST', 'localhost');
    define('DB_PORT', 3306);  // ← Add port for TCP
    define('DB_USER', 'mznaien');
    define('DB_PASS', 'YDrknvGJINrcbATt');
    define('DB_NAME', 'db_mznaien');
}

// Base directory for HTML files
if (!defined('BASE_DIR')) {
    define('BASE_DIR', dirname(__FILE__));
}
?>
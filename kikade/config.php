<?php
// Database configuration
define('DB_HOST', 'sql109.infinityfree.com');
define('DB_USER', 'if0_38595302');
define('DB_PASS', '6uK4j3ta2qQr');
define('DB_NAME', 'if0_38595302_secondhand_marketplace');

// Create connection
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set charset
$conn->set_charset("utf8");
?>
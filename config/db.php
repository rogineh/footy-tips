<?php
/**
 * Database Configuration
 * Update these settings with your HostGator MySQL credentials.
 */

$db_host = 'localhost';
$db_user = 'root'; // User should change this
$db_pass = '';     // User should change this
$db_name = 'footy_tips';

// Create connection
$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set charset to utf8mb4
$conn->set_charset("utf8mb4");

/**
 * Global Constants
 */
define('SITE_NAME', 'Footy Tips AU');
define('ADMIN_EMAIL', 'admin@example.com');
?>

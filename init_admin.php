<?php
/**
 * Initial Setup Script
 * Use this to create the initial admin user.
 * Delete this file after use.
 */
require_once 'config/db.php';

$admin_user = 'rok';
$admin_pass = 'pwd789';
$admin_email = 'admin@example.com';
$hashed_pass = password_hash($admin_pass, PASSWORD_DEFAULT);

$stmt = $conn->prepare("INSERT INTO users (username, email, password, role, is_approved, has_nrl, has_afl) VALUES (?, ?, ?, 'admin', TRUE, TRUE, TRUE)");
$stmt->bind_param("sss", $admin_user, $admin_email, $hashed_pass);

if ($stmt->execute()) {
    echo "Admin user created successfully!<br>";
    echo "Username: $admin_user<br>";
    echo "Password: $admin_pass<br>";
    echo "<strong>Please delete this file (init_admin.php) immediately.</strong>";
} else {
    echo "Error creating admin user: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>
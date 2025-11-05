<?php
/**
 * Diagnostic script to check database connection and user data
 * Run this via browser: http://localhost/attendence-portal/check_database.php
 */

require_once "config/config.php";

echo "<h2>Database Diagnostics</h2>";

// Check connection
if ($conn->connect_error) {
    echo "<p style='color: red;'>✗ Database connection failed: " . $conn->connect_error . "</p>";
    exit;
} else {
    echo "<p style='color: green;'>✓ Database connection successful</p>";
}

// Check if database exists
$db_check = $conn->query("SELECT DATABASE()");
if ($db_check) {
    $db_name = $db_check->fetch_array()[0];
    echo "<p>Current database: <strong>" . htmlspecialchars($db_name) . "</strong></p>";
}

// Check if users table exists
$table_check = $conn->query("SHOW TABLES LIKE 'users'");
if ($table_check && $table_check->num_rows > 0) {
    echo "<p style='color: green;'>✓ Users table exists</p>";
    
    // Check for admin user
    $admin_check = $conn->query("SELECT user_id, username, role_id, full_name FROM users WHERE username = 'admin'");
    if ($admin_check && $admin_check->num_rows > 0) {
        $admin = $admin_check->fetch_assoc();
        echo "<p style='color: green;'>✓ Admin user found:</p>";
        echo "<ul>";
        echo "<li>User ID: " . $admin['user_id'] . "</li>";
        echo "<li>Username: " . htmlspecialchars($admin['username']) . "</li>";
        echo "<li>Role ID: " . $admin['role_id'] . "</li>";
        echo "<li>Full Name: " . htmlspecialchars($admin['full_name']) . "</li>";
        echo "</ul>";
        
        // Get password hash and verify
        $hash_check = $conn->query("SELECT password_hash FROM users WHERE username = 'admin'");
        if ($hash_check && $row = $hash_check->fetch_assoc()) {
            $hash = $row['password_hash'];
            echo "<p>Password hash length: " . strlen($hash) . " characters</p>";
            echo "<p>Password hash (first 20 chars): " . htmlspecialchars(substr($hash, 0, 20)) . "...</p>";
            
            // Test password verification
            $test_password = 'password';
            $verified = password_verify($test_password, $hash);
            echo "<p>Password verification for 'password': " . 
                 ($verified ? "<span style='color: green;'>✓ PASSED</span>" : "<span style='color: red;'>✗ FAILED</span>") . "</p>";
            
            if (!$verified) {
                echo "<p style='color: red;'><strong>⚠ Password hash is incorrect! Run fix_admin_password.php to fix it.</strong></p>";
            }
        }
    } else {
        echo "<p style='color: red;'>✗ Admin user not found in database</p>";
        echo "<p>Please import the schema.sql file to create the users.</p>";
    }
    
    // List all users
    echo "<h3>All Users in Database:</h3>";
    $all_users = $conn->query("SELECT user_id, username, role_id, full_name FROM users");
    if ($all_users && $all_users->num_rows > 0) {
        echo "<table border='1' cellpadding='5' style='border-collapse: collapse;'>";
        echo "<tr><th>ID</th><th>Username</th><th>Role</th><th>Full Name</th></tr>";
        while ($user = $all_users->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . $user['user_id'] . "</td>";
            echo "<td>" . htmlspecialchars($user['username']) . "</td>";
            echo "<td>" . $user['role_id'] . "</td>";
            echo "<td>" . htmlspecialchars($user['full_name']) . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p>No users found in database.</p>";
    }
} else {
    echo "<p style='color: red;'>✗ Users table does not exist</p>";
    echo "<p>Please import the schema.sql file to create the tables.</p>";
}

$conn->close();
?>


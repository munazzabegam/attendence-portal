<?php
/**
 * This script fixes the admin password in the database
 * Run this once via browser: http://localhost/attendence-portal/fix_admin_password.php
 * Then delete this file for security
 */

require_once "config/config.php";

// Generate a new hash for 'password'
$new_hash = password_hash('password', PASSWORD_DEFAULT);

echo "<h2>Updating Admin Password</h2>";
echo "<p>New password hash generated: " . htmlspecialchars($new_hash) . "</p>";

// Update the admin user's password
$sql = "UPDATE users SET password_hash = ? WHERE username = 'admin'";
if ($stmt = $conn->prepare($sql)) {
    $stmt->bind_param("s", $new_hash);
    
    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            echo "<p style='color: green;'><strong>✓ Success!</strong> Admin password has been updated.</p>";
            echo "<p>You can now login with:</p>";
            echo "<ul>";
            echo "<li><strong>Username:</strong> admin</li>";
            echo "<li><strong>Password:</strong> password</li>";
            echo "</ul>";
            
            // Verify the password works
            $verify_sql = "SELECT password_hash FROM users WHERE username = 'admin'";
            $result = $conn->query($verify_sql);
            if ($result && $row = $result->fetch_assoc()) {
                $verified = password_verify('password', $row['password_hash']);
                echo "<p>Password verification: " . ($verified ? "<span style='color: green;'>✓ PASSED</span>" : "<span style='color: red;'>✗ FAILED</span>") . "</p>";
            }
        } else {
            echo "<p style='color: orange;'>No rows updated. Admin user may not exist. Make sure you've imported the schema.sql file.</p>";
        }
    } else {
        echo "<p style='color: red;'>Error updating password: " . $conn->error . "</p>";
    }
    $stmt->close();
} else {
    echo "<p style='color: red;'>Error preparing statement: " . $conn->error . "</p>";
}

// Also update other users for consistency
$users = ['prof.smith', 'student.alice'];
foreach ($users as $username) {
    $sql = "UPDATE users SET password_hash = ? WHERE username = ?";
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("ss", $new_hash, $username);
        $stmt->execute();
        $stmt->close();
    }
}

echo "<p style='margin-top: 20px;'><strong>Note:</strong> Please delete this file (fix_admin_password.php) after running it for security.</p>";

$conn->close();
?>


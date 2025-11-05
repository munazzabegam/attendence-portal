<?php
// PHP Error Reporting for Development (Disable in production)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Start the session (must be the very first thing in all files)
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Database Credentials
define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'root'); // CHANGE THIS TO A NON-ROOT USER IN PRODUCTION
define('DB_PASSWORD', ''); // Default XAMPP has NO password. If you set one, change this.
define('DB_NAME', 'attendance_portal_db');

// Attempt to connect to MySQL database
$conn = @new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

// Check connection
if ($conn->connect_error) {
    $error_msg = "ERROR: Could not connect to MySQL database. ";
    $error_msg .= $conn->connect_error . "<br><br>";
    $error_msg .= "<strong>Troubleshooting Steps:</strong><br>";
    $error_msg .= "1. Make sure MySQL is running in XAMPP Control Panel<br>";
    $error_msg .= "2. Check if MySQL root password is set (default XAMPP has NO password)<br>";
    $error_msg .= "3. If password is set, update DB_PASSWORD in config.php<br>";
    $error_msg .= "4. Verify database 'attendance_portal_db' exists in phpMyAdmin<br>";
    $error_msg .= "5. Try connecting via phpMyAdmin to verify credentials";
    die($error_msg);
}

// Set charset to utf8mb4 for proper character handling
$conn->set_charset("utf8mb4");

/**
 * Checks if the user is logged in and has the required role access.
 * If not, redirects them to the login page.
 * @param int $required_role The role ID required (1=Admin, 2=Teacher, 3=Student)
 */
function check_auth($required_role) {
    // Check if user is logged in
    if (!isset($_SESSION['user_id']) || !isset($_SESSION['role_id'])) {
        // Redirect to root login page (Go up one level: ../index.php)
        header("location: ../index.php?error=unauthorized");
        exit;
    }

    // Check if the user has the required role
    if ($_SESSION['role_id'] != $required_role) {
        // Redirect to their own dashboard if they are logged in but unauthorized for this page
        // Note: The paths are relative to the *calling* script (e.g., admin/index.php),
        // so we need to go up one level (..) to access the other folders.
        switch ($_SESSION['role_id']) {
            case 1:
                header("location: ../admin/");
                break;
            case 2:
                header("location: ../teacher/");
                break;
            case 3:
                header("location: ../student/");
                break;
            default:
                // Log them out if role is invalid
                session_destroy();
                header("location: ../index.php?error=invalid_role");
                break;
        }
        exit;
    }
}
?>
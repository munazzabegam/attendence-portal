<?php
require_once "config/config.php";

// Redirect logged-in users to their respective folder dashboards
if (isset($_SESSION['user_id'])) {
    switch ($_SESSION['role_id']) {
        case 1: header("location: admin/"); exit;
        case 2: header("location: teacher/"); exit;
        case 3: header("location: student/"); exit;
    }
}

$username = $password = "";
$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST["username"]);
    $password = trim($_POST["password"]);

    if (empty($username) || empty($password)) {
        $error = "Please enter both username and password.";
    } else {
        $sql = "SELECT user_id, password_hash, role_id, full_name FROM users WHERE username = ?";
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("s", $param_username);
            $param_username = $username;

            if ($stmt->execute()) {
                $stmt->store_result();
                if ($stmt->num_rows == 1) {
                    $stmt->bind_result($id, $hashed_password, $role_id, $full_name);
                    if ($stmt->fetch()) {
                        if (password_verify($password, $hashed_password)) {
                            $_SESSION["loggedin"] = true;
                            $_SESSION["user_id"] = $id;
                            $_SESSION["username"] = $username;
                            $_SESSION["role_id"] = $role_id;
                            $_SESSION["full_name"] = $full_name;

                            // Updated Redirects
                            switch ($role_id) {
                                case 1: header("location: admin/"); break;
                                case 2: header("location: teacher/"); break;
                                case 3: header("location: student/"); break;
                                default: $error = "Invalid user role."; break;
                            }
                            exit;
                        } else { $error = "Invalid username or password."; }
                    }
                } else { $error = "Invalid username or password."; }
            } else { $error = "Oops! Something went wrong. Please try again later."; }
            $stmt->close();
        }
    }
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Attendance Portal Login</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="login-page">
    <div class="container">
        <div style="text-align: center; margin-bottom: 30px;">
            <i class="fas fa-graduation-cap" style="font-size: 48px; color: #fff; margin-bottom: 15px; display: block;"></i>
            <h2>Attendance Portal</h2>
            <p style="opacity: 0.8; margin-top: 8px;">Sign in to continue</p>
        </div>
        
        <?php 
        if(!empty($error)){ echo '<div class="message error"><i class="fas fa-exclamation-circle"></i> '.$error.'</div>'; }        
        if(isset($_GET['error']) && $_GET['error'] == 'unauthorized'){ echo '<div class="message error"><i class="fas fa-lock"></i> Access denied. Please login.</div>'; }
        ?>

        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            <div class="form-group">
                <label for="username"><i class="fas fa-user"></i> Username</label>
                <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($username); ?>" placeholder="Enter your username" required>
            </div>    
            <div class="form-group">
                <label for="password"><i class="fas fa-lock"></i> Password</label>
                <input type="password" id="password" name="password" placeholder="Enter your password" required>
            </div>
            <div class="form-group">
                <button type="submit" style="width: 100%;">
                    <i class="fas fa-sign-in-alt"></i> Login
                </button>
            </div>
        </form>
    </div>
</body>
</html>
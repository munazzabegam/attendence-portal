<?php
require_once "../config/config.php"; 
check_auth(1); // Check for Admin (Role ID 1)

$users = [];
$sql = "SELECT user_id, username, full_name, role_id FROM users ORDER BY role_id, full_name";
$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $users[] = $row;
    }
}

// Get statistics
$total_users = count($users);
$total_admins = count(array_filter($users, fn($u) => $u['role_id'] == 1));
$total_teachers = count(array_filter($users, fn($u) => $u['role_id'] == 2));
$total_students = count(array_filter($users, fn($u) => $u['role_id'] == 3));

$classes_sql = "SELECT COUNT(*) as count FROM classes";
$classes_result = $conn->query($classes_sql);
$total_classes = $classes_result ? $classes_result->fetch_assoc()['count'] : 0;

function get_role_name($id) {
    switch ($id) {
        case 1: return "Admin";
        case 2: return "Teacher";
        case 3: return "Student";
        default: return "Unknown";
    }
}

function get_role_icon($id) {
    switch ($id) {
        case 1: return "fas fa-user-shield";
        case 2: return "fas fa-chalkboard-teacher";
        case 3: return "fas fa-user-graduate";
        default: return "fas fa-user";
    }
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - Attendance Portal</title>
    <link rel="stylesheet" href="../style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="dashboard-container">
        <aside class="sidebar">
            <div class="logo">
                <i class="fas fa-graduation-cap"></i>
                <span>Portal</span>
            </div>
            <nav class="menu">
                <ul>
                    <li class="active">
                        <a href="index.php">
                            <i class="fas fa-home"></i>
                            <span>Dashboard</span>
                        </a>
                    </li>
                    <li>
                        <a href="index.php">
                            <i class="fas fa-users"></i>
                            <span>Users</span>
                        </a>
                    </li>
                    <li>
                        <a href="index.php">
                            <i class="fas fa-book"></i>
                            <span>Classes</span>
                        </a>
                    </li>
                    <li>
                        <a href="index.php">
                            <i class="fas fa-chart-bar"></i>
                            <span>Reports</span>
                        </a>
                    </li>
                    <li>
                        <a href="index.php">
                            <i class="fas fa-cog"></i>
                            <span>Settings</span>
                        </a>
                    </li>
                </ul>
            </nav>
            <div class="profile">
                <div class="avatar">
                    <?php echo strtoupper(substr($_SESSION['full_name'], 0, 1)); ?>
                </div>
                <div class="user-info">
                    <h3><?php echo htmlspecialchars($_SESSION['full_name']); ?></h3>
                    <p>Administrator</p>
                    <a href="../config/logout.php" class="logout-link">Logout</a>
                </div>
            </div>
        </aside>
        <main class="content">
            <header>
                <h1>Welcome Back, <?php echo htmlspecialchars(explode(' ', $_SESSION['full_name'])[0]); ?></h1>
                <p>Manage your attendance portal system</p>
            </header>

            <div class="card-container">
                <div class="card">
                    <div class="card-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="card-info">
                        <h3><?php echo $total_users; ?></h3>
                        <p>Total Users</p>
                    </div>
                </div>
                <div class="card">
                    <div class="card-icon">
                        <i class="fas fa-chalkboard-teacher"></i>
                    </div>
                    <div class="card-info">
                        <h3><?php echo $total_teachers; ?></h3>
                        <p>Teachers</p>
                    </div>
                </div>
                <div class="card">
                    <div class="card-icon">
                        <i class="fas fa-user-graduate"></i>
                    </div>
                    <div class="card-info">
                        <h3><?php echo $total_students; ?></h3>
                        <p>Students</p>
                    </div>
                </div>
                <div class="card">
                    <div class="card-icon">
                        <i class="fas fa-book"></i>
                    </div>
                    <div class="card-info">
                        <h3><?php echo $total_classes; ?></h3>
                        <p>Classes</p>
                    </div>
                </div>
            </div>

            <h2>User Management</h2>
            
            <?php if (!empty($users)): ?>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Username</th>
                            <th>Full Name</th>
                            <th>Role</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user): ?>
                        <tr>
                            <td><?php echo $user['user_id']; ?></td>
                            <td><?php echo htmlspecialchars($user['username']); ?></td>
                            <td><?php echo htmlspecialchars($user['full_name']); ?></td>
                            <td>
                                <i class="<?php echo get_role_icon($user['role_id']); ?>" style="margin-right: 5px;"></i>
                                <?php echo get_role_name($user['role_id']); ?>
                            </td>
                            <td>
                                <button style="background: #f97316; padding: 8px 16px; font-size: 12px;">
                                    <i class="fas fa-edit"></i> Edit
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="message">No users found in the database.</div>
            <?php endif; ?>

            <h2 style="margin-top: 30px;">Class Management</h2>
            <button style="background-color: var(--secondary-color);">
                <i class="fas fa-plus"></i> Create New Class
            </button>
        </main>
    </div>
    <script src="../script.js"></script>
</body>
</html>

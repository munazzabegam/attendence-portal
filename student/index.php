<?php
require_once "../config/config.php"; 
check_auth(3); // Requires Student (Role ID 3)

$student_id = $_SESSION['user_id'];
$attendance_records = [];

// Fetch attendance records for the current student
$sql = "
    SELECT 
        c.class_name, 
        a.attendance_date, 
        a.status 
    FROM attendance a
    JOIN enrollment e ON a.enrollment_id = e.enrollment_id
    JOIN classes c ON e.class_id = c.class_id
    WHERE e.student_id = ?
    ORDER BY a.attendance_date DESC";

if ($stmt = $conn->prepare($sql)) {
    $stmt->bind_param("i", $student_id);
    if ($stmt->execute()) {
        $result = $stmt->get_result();
        while($row = $result->fetch_assoc()) {
            $attendance_records[] = $row;
        }
    }
    $stmt->close();
}

// Get statistics
$total_records = count($attendance_records);
$present_count = count(array_filter($attendance_records, fn($r) => $r['status'] == 'Present'));
$absent_count = count(array_filter($attendance_records, fn($r) => $r['status'] == 'Absent'));
$late_count = count(array_filter($attendance_records, fn($r) => $r['status'] == 'Late'));

// Get enrolled classes
$classes_sql = "
    SELECT c.class_name 
    FROM classes c
    JOIN enrollment e ON c.class_id = e.class_id
    WHERE e.student_id = ?";
$enrolled_classes = [];
if ($stmt_classes = $conn->prepare($classes_sql)) {
    $stmt_classes->bind_param("i", $student_id);
    if ($stmt_classes->execute()) {
        $result_classes = $stmt_classes->get_result();
        while($row = $result_classes->fetch_assoc()) {
            $enrolled_classes[] = $row['class_name'];
        }
    }
    $stmt_classes->close();
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Panel - Attendance Portal</title>
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
                            <i class="fas fa-calendar-check"></i>
                            <span>Attendance</span>
                        </a>
                    </li>
                    <li>
                        <a href="index.php">
                            <i class="fas fa-book"></i>
                            <span>My Classes</span>
                        </a>
                    </li>
                    <li>
                        <a href="index.php">
                            <i class="fas fa-chart-pie"></i>
                            <span>Statistics</span>
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
                    <p>Student</p>
                    <a href="../config/logout.php" class="logout-link">Logout</a>
                </div>
            </div>
        </aside>
        <main class="content">
            <header>
                <h1>Welcome Back, <?php echo htmlspecialchars(explode(' ', $_SESSION['full_name'])[0]); ?></h1>
                <p>View your attendance records and class information</p>
            </header>

            <div class="card-container">
                <div class="card">
                    <div class="card-icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="card-info">
                        <h3><?php echo $present_count; ?></h3>
                        <p>Present Days</p>
                    </div>
                </div>
                <div class="card">
                    <div class="card-icon">
                        <i class="fas fa-times-circle"></i>
                    </div>
                    <div class="card-info">
                        <h3><?php echo $absent_count; ?></h3>
                        <p>Absent Days</p>
                    </div>
                </div>
                <div class="card">
                    <div class="card-icon">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="card-info">
                        <h3><?php echo $late_count; ?></h3>
                        <p>Late Days</p>
                    </div>
                </div>
                <div class="card">
                    <div class="card-icon">
                        <i class="fas fa-book"></i>
                    </div>
                    <div class="card-info">
                        <h3><?php echo count($enrolled_classes); ?></h3>
                        <p>Enrolled Classes</p>
                    </div>
                </div>
            </div>

            <h2>My Attendance History</h2>

            <?php if (!empty($attendance_records)): ?>
                <table>
                    <thead>
                        <tr>
                            <th>Class Name</th>
                            <th>Date</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($attendance_records as $record): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($record['class_name']); ?></td>
                            <td><?php echo date('F j, Y', strtotime($record['attendance_date'])); ?></td>
                            <td>
                                <span class="status-<?php echo $record['status']; ?>">
                                    <?php echo htmlspecialchars($record['status']); ?>
                                </span>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="message">No attendance records found yet.</div>
            <?php endif; ?>

            <?php if (!empty($enrolled_classes)): ?>
                <h2 style="margin-top: 30px;">My Classes</h2>
                <div class="attendance-grid">
                    <?php foreach ($enrolled_classes as $class_name): ?>
                        <div class="student-card">
                            <h4>
                                <i class="fas fa-book" style="margin-right: 8px;"></i>
                                <?php echo htmlspecialchars($class_name); ?>
                            </h4>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </main>
    </div>
    <script src="../script.js"></script>
</body>
</html>

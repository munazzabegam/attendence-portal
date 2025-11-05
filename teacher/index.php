<?php
require_once "../config/config.php";
check_auth(2); // Requires Teacher (Role ID 2)

$teacher_id = $_SESSION['user_id'];
$message = '';

// Mock Attendance Submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['class_id'])) {
    $class_id = (int)$_POST['class_id'];
    $message = "<div class='message success'>Attendance for Class ID: $class_id was submitted! (Full CRUD implementation needed)</div>";
}

// Fetch classes taught by the current teacher
$classes = [];
$sql = "SELECT class_id, class_name FROM classes WHERE teacher_id = ?";
if ($stmt = $conn->prepare($sql)) {
    $stmt->bind_param("i", $teacher_id);
    if ($stmt->execute()) {
        $result = $stmt->get_result();
        while($row = $result->fetch_assoc()) {
            $classes[] = $row;
        }
    }
    $stmt->close();
}

// Fetch students for the first class (if available)
$current_class_id = !empty($classes) ? $classes[0]['class_id'] : null;
$students = [];
if ($current_class_id) {
    $sql_students = "
        SELECT u.full_name, e.enrollment_id 
        FROM users u
        JOIN enrollment e ON u.user_id = e.student_id
        WHERE e.class_id = ?";

    if ($stmt_s = $conn->prepare($sql_students)) {
        $stmt_s->bind_param("i", $current_class_id);
        if ($stmt_s->execute()) {
            $result_s = $stmt_s->get_result();
            while($row_s = $result_s->fetch_assoc()) {
                $students[] = $row_s;
            }
        }
        $stmt_s->close();
    }
}

// Get statistics
$total_classes = count($classes);
$total_students = count($students);

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teacher Panel - Attendance Portal</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/teacher.css">
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
                            <i class="fas fa-check-circle"></i>
                            <span>Mark Attendance</span>
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
                            <i class="fas fa-chart-line"></i>
                            <span>Reports</span>
                        </a>
                    </li>
                    <li>
                        <a href="index.php">
                            <i class="fas fa-user-graduate"></i>
                            <span>Students</span>
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
                    <p>Teacher</p>
                    <a href="../config/logout.php" class="logout-link">Logout</a>
                </div>
            </div>
        </aside>
        <main class="content">
            <header>
                <h1>Welcome Back, <?php echo htmlspecialchars(explode(' ', $_SESSION['full_name'])[0]); ?></h1>
                <p>Manage attendance and track student progress</p>
            </header>

            <?php echo $message; ?>

            <div class="card-container">
                <div class="card">
                    <div class="card-icon">
                        <i class="fas fa-book"></i>
                    </div>
                    <div class="card-info">
                        <h3><?php echo $total_classes; ?></h3>
                        <p>My Classes</p>
                    </div>
                </div>
                <div class="card">
                    <div class="card-icon">
                        <i class="fas fa-user-graduate"></i>
                    </div>
                    <div class="card-info">
                        <h3><?php echo $total_students; ?></h3>
                        <p>Total Students</p>
                    </div>
                </div>
                <div class="card">
                    <div class="card-icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="card-info">
                        <h3>Today</h3>
                        <p>Attendance</p>
                    </div>
                </div>
            </div>

            <h2>Mark Attendance</h2>

            <form method="POST" action="index.php">
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
                    <div class="form-group">
                        <label for="class_select">Select Class</label>
                        <select name="class_id" id="class_select" required>
                            <?php if (empty($classes)): ?>
                                <option value="">No classes assigned</option>
                            <?php else: ?>
                                <?php foreach ($classes as $class): ?>
                                    <option value="<?php echo $class['class_id']; ?>">
                                        <?php echo htmlspecialchars($class['class_name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="date_select">Attendance Date</label>
                        <input type="date" name="attendance_date" id="date_select" value="<?php echo date('Y-m-d'); ?>" required>
                    </div>
                </div>

                <?php if ($current_class_id && !empty($students)): ?>
                    <h3>Students in <?php echo htmlspecialchars($classes[0]['class_name']); ?></h3>
                    
                    <div class="attendance-grid">
                        <?php foreach ($students as $student): ?>
                            <div class="student-card">
                                <h4><?php echo htmlspecialchars($student['full_name']); ?></h4>
                                <div class="form-group">
                                    <select name="status_<?php echo $student['enrollment_id']; ?>"> 
                                        <option value="Present">Present</option>
                                        <option value="Absent" selected>Absent</option>
                                        <option value="Late">Late</option>
                                        <option value="Excused">Excused</option>
                                    </select>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <div class="form-group" style="margin-top: 20px;">
                        <button type="submit">
                            <i class="fas fa-save"></i> Save Attendance
                        </button>
                    </div>
                <?php elseif ($current_class_id): ?>
                    <div class="message">No students are currently enrolled in this class.</div>
                <?php else: ?>
                    <div class="message">No classes assigned. Please contact an administrator.</div>
                <?php endif; ?>
            </form>
        </main>
    </div>
    <script src="../assets/js/script.js"></script>
</body>
</html>

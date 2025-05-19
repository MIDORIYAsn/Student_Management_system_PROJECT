<?php
session_start();
require_once 'config/db_connect.php';
require_once 'includes/functions.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$date = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');
$selected_class = isset($_GET['class']) ? $_GET['class'] : '';

// Get all classes
$classes_query = "SELECT DISTINCT class FROM students ORDER BY class";
$classes_result = $conn->query($classes_query);
$classes = [];
while ($row = $classes_result->fetch_assoc()) {
    if (!empty($row['class'])) {
        $classes[] = $row['class'];
    }
}

// Get attendance statistics for selected class
$attendance_stats = [];
if ($selected_class) {
    $stats_query = "SELECT a.status, COUNT(*) as count 
                    FROM attendance a 
                    JOIN students s ON a.student_id = s.id 
                    WHERE s.class = ? AND a.date = ?
                    GROUP BY a.status";
    $stmt = $conn->prepare($stats_query);
    $stmt->bind_param("ss", $selected_class, $date);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $attendance_stats[$row['status']] = $row['count'];
    }
}

// Get students and their attendance for selected class
$students = [];
if ($selected_class) {
    $students_query = "SELECT s.*, a.status 
                      FROM students s 
                      LEFT JOIN attendance a ON s.id = a.student_id AND a.date = ? 
                      WHERE s.class = ?
                      ORDER BY s.first_name";
    $stmt = $conn->prepare($students_query);
    $stmt->bind_param("ss", $date, $selected_class);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $students[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Class Attendance - Student Management System</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .attendance-stats {
            display: flex;
            gap: 20px;
            margin-bottom: 20px;
        }
        .stat-box {
            background: white;
            padding: 15px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            text-align: center;
            flex: 1;
        }
        .stat-box h3 {
            margin: 0;
            color: #4a90e2;
            font-size: 24px;
        }
        .stat-box p {
            margin: 5px 0 0;
            color: #666;
        }
        .status-present { color: #2ecc71; }
        .status-absent { color: #e74c3c; }
        .status-late { color: #f1c40f; }
        .attendance-form {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
        }
        .attendance-form select,
        .attendance-form input {
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <div class="container">
        <div class="card">
            <div class="card-header">
                <h2>Class Attendance</h2>
                <form action="" method="GET" class="attendance-form">
                    <select id="class" name="class" class="form-control" required>
                        <option value="">Select Class</option>
                        <?php
                        for ($i = 1; $i <= 12; $i++) {
                            echo "<option value=\"$i\" " . ($i == $selected_class ? 'selected' : '') . ">Class $i</option>";
                        }
                        ?>
                    </select>
                    <input type="date" name="date" value="<?php echo $date; ?>" required>
                    <button type="submit" class="btn btn-primary">View Attendance</button>
                </form>
            </div>

            <?php if ($selected_class): ?>
            <div class="attendance-stats">
                <div class="stat-box">
                    <h3><?php echo isset($attendance_stats['present']) ? $attendance_stats['present'] : 0; ?></h3>
                    <p>Present</p>
                </div>
                <div class="stat-box">
                    <h3><?php echo isset($attendance_stats['absent']) ? $attendance_stats['absent'] : 0; ?></h3>
                    <p>Absent</p>
                </div>
                <div class="stat-box">
                    <h3><?php echo isset($attendance_stats['late']) ? $attendance_stats['late'] : 0; ?></h3>
                    <p>Late</p>
                </div>
            </div>

            <table class="table">
                <thead>
                    <tr>
                        <th>Student ID</th>
                        <th>Name</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($students as $student): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($student['student_id']); ?></td>
                        <td><?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name']); ?></td>
                        <td>
                            <span class="status-<?php echo $student['status'] ?? 'absent'; ?>">
                                <?php echo ucfirst($student['status'] ?? 'Not Marked'); ?>
                            </span>
                        </td>
                        <td>
                            <select onchange="markAttendance(<?php echo $student['id']; ?>, '<?php echo $date; ?>', this.value)"
                                    class="form-control">
                                <option value="">Mark Attendance</option>
                                <option value="present" <?php echo $student['status'] === 'present' ? 'selected' : ''; ?>>Present</option>
                                <option value="absent" <?php echo $student['status'] === 'absent' ? 'selected' : ''; ?>>Absent</option>
                                <option value="late" <?php echo $student['status'] === 'late' ? 'selected' : ''; ?>>Late</option>
                            </select>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <?php if (empty($students)): ?>
            <div class="alert alert-info">No students found in this class.</div>
            <?php endif; ?>

            <?php endif; ?>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>
    <script src="assets/js/main.js"></script>
</body>
</html>

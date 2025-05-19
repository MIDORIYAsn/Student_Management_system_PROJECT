<?php
session_start();
require_once 'config/db_connect.php';
require_once 'includes/functions.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$date = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');
$class = isset($_GET['class']) ? $_GET['class'] : '';

// Get all classes
$classes_query = "SELECT DISTINCT class FROM students ORDER BY class";
$classes_result = $conn->query($classes_query);
$classes = [];
while ($row = $classes_result->fetch_assoc()) {
    $classes[] = $row['class'];
}

// Get students based on class filter
$students_sql = "SELECT * FROM students";
if ($class) {
    $students_sql .= " WHERE class = '$class'";
}
$students_sql .= " ORDER BY first_name";
$students_result = $conn->query($students_sql);
$students = [];
while ($row = $students_result->fetch_assoc()) {
    $students[] = $row;
}

// Get attendance for the selected date
$attendance = [];
if (!empty($students)) {
    $attendance_sql = "SELECT * FROM attendance WHERE date = '$date'";
    $attendance_result = $conn->query($attendance_sql);
    while ($row = $attendance_result->fetch_assoc()) {
        $attendance[$row['student_id']] = $row['status'];
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Attendance - Student Management System</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <div class="container">
        <div class="card">
            <div class="card-header">
                <h2>Mark Attendance</h2>
                <div class="header-actions">
                    <form action="" method="GET" class="form-inline">
                        <input type="date" name="date" value="<?php echo $date; ?>" class="form-control">
                        <select name="class" class="form-control">
                            <option value="">All Classes</option>
                            <?php foreach ($classes as $class_option): ?>
                            <option value="<?php echo $class_option; ?>" 
                                    <?php echo $class_option === $class ? 'selected' : ''; ?>>
                                <?php echo $class_option; ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                        <button type="submit" class="btn btn-primary">Filter</button>
                    </form>
                </div>
            </div>

            <form action="includes/save_attendance.php" method="POST">
                <input type="hidden" name="date" value="<?php echo $date; ?>">
                
                <table class="table">
                    <thead>
                        <tr>
                            <th>Student ID</th>
                            <th>Name</th>
                            <th>Class</th>
                            <th>Attendance</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($students as $student): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($student['student_id']); ?></td>
                            <td><?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name']); ?></td>
                            <td><?php echo htmlspecialchars($student['class']); ?></td>
                            <td>
                                <select name="attendance[<?php echo $student['id']; ?>]" 
                                        class="form-control"
                                        onchange="markAttendance(<?php echo $student['id']; ?>, '<?php echo $date; ?>', this.value)">
                                    <option value="">Select Status</option>
                                    <option value="present" <?php echo isset($attendance[$student['id']]) && $attendance[$student['id']] === 'present' ? 'selected' : ''; ?>>Present</option>
                                    <option value="absent" <?php echo isset($attendance[$student['id']]) && $attendance[$student['id']] === 'absent' ? 'selected' : ''; ?>>Absent</option>
                                    <option value="late" <?php echo isset($attendance[$student['id']]) && $attendance[$student['id']] === 'late' ? 'selected' : ''; ?>>Late</option>
                                </select>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <?php if (empty($students)): ?>
                <div class="alert alert-info">No students found for the selected criteria.</div>
                <?php endif; ?>
            </form>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>
    <script src="assets/js/main.js"></script>
</body>
</html>

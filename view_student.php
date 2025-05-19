<?php
session_start();
require_once 'config/db_connect.php';
require_once 'includes/functions.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

if (!isset($_GET['id'])) {
    header('Location: students.php');
    exit();
}

$student_id = (int)$_GET['id'];
$student = getStudentById($conn, $student_id);

if (!$student) {
    header('Location: students.php');
    exit();
}

// Get attendance history
$attendance = getStudentAttendance($conn, $student_id);

// Get marks history
$marks = getStudentMarks($conn, $student_id);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Student - Student Management System</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <div class="container">
        <div class="card">
            <div class="card-header">
                <h2>Student Details</h2>
                <div class="header-actions">
                    <a href="edit_student.php?id=<?php echo $student_id; ?>" class="btn btn-primary">Edit Student</a>
                    <button onclick="printReport('studentDetails')" class="btn btn-primary">Print Report</button>
                </div>
            </div>

            <div id="studentDetails">
                <div class="student-info">
                    <div class="info-group">
                        <label>Student ID:</label>
                        <span><?php echo htmlspecialchars($student['student_id']); ?></span>
                    </div>
                    <div class="info-group">
                        <label>Name:</label>
                        <span><?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name']); ?></span>
                    </div>
                    <div class="info-group">
                        <label>Email:</label>
                        <span><?php echo htmlspecialchars($student['email']); ?></span>
                    </div>
                    <div class="info-group">
                        <label>Phone:</label>
                        <span><?php echo htmlspecialchars($student['phone']); ?></span>
                    </div>
                    <div class="info-group">
                        <label>Date of Birth:</label>
                        <span><?php echo htmlspecialchars($student['date_of_birth']); ?></span>
                    </div>
                    <div class="info-group">
                        <label>Gender:</label>
                        <span><?php echo htmlspecialchars($student['gender']); ?></span>
                    </div>
                    <div class="info-group">
                        <label>Class:</label>
                        <span><?php echo htmlspecialchars($student['class']); ?></span>
                    </div>
                    <div class="info-group">
                        <label>Address:</label>
                        <span><?php echo htmlspecialchars($student['address']); ?></span>
                    </div>
                    <div class="info-group">
                        <label>Admission Date:</label>
                        <span><?php echo htmlspecialchars($student['admission_date']); ?></span>
                    </div>
                </div>

                <div class="section">
                    <h3>Attendance History</h3>
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($attendance as $record): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($record['date']); ?></td>
                                <td>
                                    <span class="status-badge status-<?php echo $record['status']; ?>">
                                        <?php echo ucfirst($record['status']); ?>
                                    </span>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <?php if (empty($attendance)): ?>
                    <div class="alert alert-info">No attendance records found.</div>
                    <?php endif; ?>
                </div>

                <div class="section">
                    <h3>Academic Performance</h3>
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Subject</th>
                                <th>Marks</th>
                                <th>Exam Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($marks as $mark): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($mark['subject']); ?></td>
                                <td><?php echo htmlspecialchars($mark['marks']); ?></td>
                                <td><?php echo htmlspecialchars($mark['exam_date']); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <?php if (empty($marks)): ?>
                    <div class="alert alert-info">No marks records found.</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>
    <script src="assets/js/main.js"></script>
</body>
</html>

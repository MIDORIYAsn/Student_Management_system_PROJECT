<?php
session_start();
require_once 'config/db_connect.php';
require_once 'includes/functions.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Get dashboard statistics
$total_students = getTotalStudents($conn);
$total_teachers = getTotalTeachers($conn);
$attendance_stats = getTodayAttendance($conn);
$recent_admissions = getRecentAdmissions($conn);
$recent_teachers = getRecentTeachers($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Management System - Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <div class="container">
        <h1>Dashboard</h1>


        
        <div class="dashboard-stats">
            <div class="stat-card">
                <h3>Total Students</h3>
                <p class="stat-number"><?php echo $total_students; ?></p>
                <a href="students.php" class="stat-link">View All Students</a>
            </div>
            
            <div class="stat-card">
                <h3>Total Teachers</h3>
                <p class="stat-number"><?php echo $total_teachers; ?></p>
                <a href="teachers.php" class="stat-link">View All Teachers</a>
            </div>
            
            <div class="stat-card">
                <h3>Today's Attendance</h3>
                <div class="attendance-stats">
                    <p>Present: <?php echo $attendance_stats['present'] ?? 0; ?></p>
                    <p>Absent: <?php echo $attendance_stats['absent'] ?? 0; ?></p>
                    <p>Late: <?php echo $attendance_stats['late'] ?? 0; ?></p>
                </div>
                <a href="class_attendance.php" class="stat-link">View Attendance</a>
            </div>
        </div>

        <div class="dashboard-sections">
            <div class="dashboard-section">
                <h2>Recent Admissions</h2>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Student ID</th>
                                <th>Name</th>
                                <th>Class</th>
                                <th>Admission Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recent_admissions as $student): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($student['student_id']); ?></td>
                                <td><?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name']); ?></td>
                                <td><?php echo htmlspecialchars($student['class']); ?></td>
                                <td><?php echo date('M d, Y', strtotime($student['admission_date'])); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="dashboard-section">
                <h2>Recent Teachers</h2>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Teacher ID</th>
                                <th>Name</th>
                                <th>Subject Specialty</th>
                                <th>Joining Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recent_teachers as $teacher): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($teacher['teacher_id']); ?></td>
                                <td><?php echo htmlspecialchars($teacher['first_name'] . ' ' . $teacher['last_name']); ?></td>
                                <td><?php echo htmlspecialchars($teacher['subject_specialty']); ?></td>
                                <td><?php echo date('M d, Y', strtotime($teacher['joining_date'])); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h2>Quick Actions</h2>
            </div>
            <div class="quick-actions-container">
                <a href="add_student.php" class="quick-action-btn">
                    <i class="fas fa-user-plus"></i>
                    <span>Add New Student</span>
                </a>
                <a href="attendance.php" class="quick-action-btn">
                    <i class="fas fa-clipboard-check"></i>
                    <span>Mark Attendance</span>
                </a>
                <a href="reports.php" class="quick-action-btn">
                    <i class="fas fa-chart-bar"></i>
                    <span>View Reports</span>
                </a>
            </div>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>
    <script src="assets/js/main.js"></script>
</body>
</html>

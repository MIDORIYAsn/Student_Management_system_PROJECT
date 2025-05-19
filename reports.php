<?php
session_start();
require_once 'config/db_connect.php';
require_once 'includes/functions.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Get statistics
$total_students = getTotalStudents($conn);
$class_distribution = [];
$class_query = "SELECT class, COUNT(*) as count FROM students GROUP BY class ORDER BY class";
$result = $conn->query($class_query);
while ($row = $result->fetch_assoc()) {
    $class_distribution[$row['class']] = $row['count'];
}

// Get attendance statistics
$attendance_query = "SELECT status, COUNT(*) as count FROM attendance WHERE date = CURDATE() GROUP BY status";
$result = $conn->query($attendance_query);
$attendance_stats = [
    'present' => 0,
    'absent' => 0,
    'late' => 0
];
while ($row = $result->fetch_assoc()) {
    $attendance_stats[$row['status']] = $row['count'];
}

// Get top performing students
$top_students_query = "SELECT s.first_name, s.last_name, s.class, AVG(m.marks) as average_marks 
                      FROM students s 
                      JOIN marks m ON s.id = m.student_id 
                      GROUP BY s.id 
                      ORDER BY average_marks DESC 
                      LIMIT 5";
$result = $conn->query($top_students_query);
$top_students = [];
while ($row = $result->fetch_assoc()) {
    $top_students[] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports - Student Management System</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <div class="container">
        <div class="card">
            <div class="card-header">
                <h2>Reports & Analytics</h2>
                <div class="header-actions">
                    <button onclick="printReport('reportsContent')" class="btn btn-primary">Print Report</button>
                </div>
            </div>

            <div id="reportsContent">
                <div class="dashboard-stats">
                    <div class="stat-card">
                        <h3><?php echo $total_students; ?></h3>
                        <p>Total Students</p>
                    </div>
                    <div class="stat-card">
                        <h3><?php echo $attendance_stats['present']; ?></h3>
                        <p>Present Today</p>
                    </div>
                    <div class="stat-card">
                        <h3><?php echo $attendance_stats['absent']; ?></h3>
                        <p>Absent Today</p>
                    </div>
                    <div class="stat-card">
                        <h3><?php echo $attendance_stats['late']; ?></h3>
                        <p>Late Today</p>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h3>Class Distribution</h3>
                            </div>
                            <canvas id="classDistributionChart"></canvas>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h3>Today's Attendance</h3>
                            </div>
                            <canvas id="attendanceChart"></canvas>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h3>Top Performing Students</h3>
                    </div>
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Class</th>
                                <th>Average Marks</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($top_students as $student): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name']); ?></td>
                                <td><?php echo htmlspecialchars($student['class']); ?></td>
                                <td><?php echo number_format($student['average_marks'], 2); ?>%</td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>
    
    <script>
        // Class Distribution Chart
        const classData = <?php echo json_encode(array_values($class_distribution)); ?>;
        const classLabels = <?php echo json_encode(array_keys($class_distribution)); ?>;
        
        new Chart(document.getElementById('classDistributionChart'), {
            type: 'bar',
            data: {
                labels: classLabels,
                datasets: [{
                    label: 'Number of Students',
                    data: classData,
                    backgroundColor: '#4a90e2'
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });

        // Attendance Chart
        const attendanceData = [
            <?php echo $attendance_stats['present']; ?>,
            <?php echo $attendance_stats['absent']; ?>,
            <?php echo $attendance_stats['late']; ?>
        ];
        
        new Chart(document.getElementById('attendanceChart'), {
            type: 'pie',
            data: {
                labels: ['Present', 'Absent', 'Late'],
                datasets: [{
                    data: attendanceData,
                    backgroundColor: ['#2ecc71', '#e74c3c', '#f1c40f']
                }]
            },
            options: {
                responsive: true
            }
        });
    </script>
</body>
</html>

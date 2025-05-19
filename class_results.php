<?php
session_start();
require_once 'config/db_connect.php';
require_once 'includes/functions.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$selected_class = isset($_GET['class']) ? $_GET['class'] : '';
$exam_date = isset($_GET['exam_date']) ? $_GET['exam_date'] : date('Y-m-d');
$exam_type = isset($_GET['exam_type']) ? $_GET['exam_type'] : '';

// Get all classes
$classes_query = "SELECT DISTINCT class FROM students ORDER BY class";
$classes_result = $conn->query($classes_query);
$classes = [];
while ($row = $classes_result->fetch_assoc()) {
    if (!empty($row['class'])) {
        $classes[] = $row['class'];
    }
}

// Get all exam dates for the selected class
$exam_dates = [];
if ($selected_class) {
    $dates_query = "SELECT DISTINCT m.exam_date 
                   FROM marks m 
                   JOIN students s ON m.student_id = s.id 
                   WHERE s.class = ? 
                   ORDER BY m.exam_date DESC";
    $stmt = $conn->prepare($dates_query);
    $stmt->bind_param("s", $selected_class);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $exam_dates[] = $row['exam_date'];
    }
}

// Get results for selected class and exam date
$results = [];
$subjects = [];
if ($selected_class && $exam_date) {
    // First get all subjects
    $subjects_query = "SELECT DISTINCT subject 
                      FROM marks m 
                      JOIN students s ON m.student_id = s.id 
                      WHERE s.class = ? AND m.exam_date = ?
                      ORDER BY subject";
    $stmt = $conn->prepare($subjects_query);
    $stmt->bind_param("ss", $selected_class, $exam_date);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $subjects[] = $row['subject'];
    }

    // Then get all students and their marks
    $results_query = "SELECT s.id, s.student_id, s.first_name, s.last_name,
                            GROUP_CONCAT(m.subject) as subjects,
                            GROUP_CONCAT(m.marks) as marks
                     FROM students s
                     LEFT JOIN marks m ON s.id = m.student_id AND m.exam_date = ?
                     WHERE s.class = ?
                     GROUP BY s.id
                     ORDER BY s.first_name";
    $stmt = $conn->prepare($results_query);
    $stmt->bind_param("ss", $exam_date, $selected_class);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        $student_marks = [];
        if ($row['subjects']) {
            $subject_array = explode(',', $row['subjects']);
            $marks_array = explode(',', $row['marks']);
            for ($i = 0; $i < count($subject_array); $i++) {
                $student_marks[$subject_array[$i]] = $marks_array[$i];
            }
        }
        $row['marks_array'] = $student_marks;
        $results[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Class Results - Student Management System</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .results-form {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
        }
        .results-form select,
        .results-form input {
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .marks-table th,
        .marks-table td {
            text-align: center;
        }
        .total-column {
            background-color: #f8f9fa;
            font-weight: bold;
        }
        .percentage-column {
            background-color: #e9ecef;
            font-weight: bold;
        }
        .grade-a { color: #2ecc71; }
        .grade-b { color: #3498db; }
        .grade-c { color: #f1c40f; }
        .grade-d { color: #e67e22; }
        .grade-f { color: #e74c3c; }
        
        /* Print styles */
        @media print {
            .no-print {
                display: none !important;
            }
            .container {
                width: 100% !important;
                padding: 0 !important;
                margin: 0 !important;
            }
            .card {
                border: none !important;
                box-shadow: none !important;
            }
            .table {
                width: 100% !important;
                font-size: 12px !important;
            }
            .grade-a, .grade-b, .grade-c, .grade-d, .grade-f {
                color: black !important;
            }
            body {
                padding: 0 !important;
                margin: 0 !important;
            }
            h3 {
                margin-top: 20px !important;
            }
            /* Add school header for print */
            .print-header {
                display: block !important;
                text-align: center;
                margin-bottom: 20px;
            }
            .print-header h1 {
                margin: 0;
                padding: 10px 0;
            }
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <div class="container">
        <div class="card">
            <!-- Print Header -->
            <div class="print-header" style="display: none;">
                <h1>School Management System</h1>
                <h2>Academic Results</h2>
                <hr>
            </div>
            
            <div class="card-header">
                <h2>Class Results</h2>
                <button type="button" class="btn btn-primary no-print" onclick="window.print();">Print Results</button>
                <form id="resultsForm" class="form-inline no-print" method="GET">
                    <select name="class" class="form-control" required>
                        <option value="">Select Class</option>
                        <?php foreach ($classes as $class): ?>
                        <option value="<?php echo htmlspecialchars($class); ?>" 
                                <?php echo $class === $selected_class ? 'selected' : ''; ?>>
                            Class <?php echo htmlspecialchars($class); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>

                    <select name="exam_type" class="form-control" required>
                        <option value="">Select Exam Type</option>
                        <option value="unit_test" <?php echo $exam_type === 'unit_test' ? 'selected' : ''; ?>>Unit Test</option>
                        <option value="midterm" <?php echo $exam_type === 'midterm' ? 'selected' : ''; ?>>Midterm</option>
                        <option value="final" <?php echo $exam_type === 'final' ? 'selected' : ''; ?>>Final</option>
                    </select>

                    <input type="date" name="exam_date" class="form-control" 
                           value="<?php echo htmlspecialchars($exam_date); ?>" required>

                    <button type="submit" class="btn btn-primary">View Results</button>
                    
                    <?php if ($selected_class && $exam_date): ?>
                    <button type="button" onclick="printReport('resultsTable')" class="btn btn-secondary">
                        <i class="fas fa-print"></i> Print Results
                    </button>
                    <?php endif; ?>
                </form>
            </div>

            <?php if ($selected_class && $exam_date && !empty($subjects)): ?>
            <div id="resultsTable">
                <h3>Results for <?php echo htmlspecialchars($selected_class); ?> - <?php echo htmlspecialchars($exam_date); ?></h3>
                <table class="table marks-table">
                    <thead>
                        <tr>
                            <th>Student ID</th>
                            <th>Name</th>
                            <?php foreach ($subjects as $subject): ?>
                            <th><?php echo htmlspecialchars($subject); ?></th>
                            <?php endforeach; ?>
                            <th class="total-column">Total</th>
                            <th class="percentage-column">Percentage</th>
                            <th class="percentage-column">Grade</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($results as $student): ?>
                        <?php
                            $total_marks = 0;
                            $total_subjects = count($subjects);
                            foreach ($subjects as $subject) {
                                if (isset($student['marks_array'][$subject])) {
                                    $total_marks += $student['marks_array'][$subject];
                                }
                            }
                            $percentage = $total_subjects > 0 ? ($total_marks / ($total_subjects * 100)) * 100 : 0;
                            
                            // Calculate grade
                            $grade = '';
                            $grade_class = '';
                            $pass_threshold = 40; // Pass threshold set to 40%
                            
                            // Determine grade based on percentage
                            if ($percentage >= 85) {
                                $grade = 'A+';
                                $grade_class = 'grade-a';
                            } elseif ($percentage >= 75) {
                                $grade = 'A';
                                $grade_class = 'grade-a';
                            } elseif ($percentage >= 65) {
                                $grade = 'B';
                                $grade_class = 'grade-b';
                            } elseif ($percentage >= 55) {
                                $grade = 'C';
                                $grade_class = 'grade-c';
                            } elseif ($percentage >= $pass_threshold) {
                                $grade = 'D';
                                $grade_class = 'grade-d';
                            } else {
                                $grade = 'F';
                                $grade_class = 'grade-f';
                            }
                            
                            // Add pass/fail status
                            $status = ($percentage >= $pass_threshold) ? 'PASS' : 'FAIL';
                        ?>
                        <tr>
                            <td><?php echo htmlspecialchars($student['student_id']); ?></td>
                            <td><?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name']); ?></td>
                            <?php foreach ($subjects as $subject): ?>
                            <td>
                                <?php 
                                if (isset($student['marks_array'][$subject])) {
                                    echo htmlspecialchars($student['marks_array'][$subject]) . '/100';
                                } else {
                                    echo '-';
                                }
                                ?>
                            </td>
                            <?php endforeach; ?>
                            <td class="total-column"><?php echo $total_marks; ?>/<?php echo ($total_subjects * 100); ?></td>
                            <td class="percentage-column"><?php echo number_format($percentage, 1); ?>%</td>
                            <td class="<?php echo $grade_class; ?>"><?php echo $grade; ?> (<?php echo $status; ?>)</td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                
                <?php
                // Calculate class statistics
                $total_students = count($results);
                $passed_students = 0;
                $failed_students = 0;
                $grade_distribution = ['A+' => 0, 'A' => 0, 'B' => 0, 'C' => 0, 'D' => 0, 'F' => 0];
                
                foreach ($results as $student) {
                    $total_marks = 0;
                    foreach ($subjects as $subject) {
                        if (isset($student['marks_array'][$subject])) {
                            $total_marks += $student['marks_array'][$subject];
                        }
                    }
                    $percentage = $total_subjects > 0 ? ($total_marks / ($total_subjects * 100)) * 100 : 0;
                    
                    if ($percentage >= $pass_threshold) {
                        $passed_students++;
                    } else {
                        $failed_students++;
                    }
                    
                    // Update grade distribution
                    if ($percentage >= 85) $grade_distribution['A+']++;
                    elseif ($percentage >= 75) $grade_distribution['A']++;
                    elseif ($percentage >= 65) $grade_distribution['B']++;
                    elseif ($percentage >= 55) $grade_distribution['C']++;
                    elseif ($percentage >= 40) $grade_distribution['D']++;
                    else $grade_distribution['F']++;
                }
                ?>
                
                <!-- Class Summary -->
                <div class="mt-4">
                    <h4>Class Summary</h4>
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-sm">
                                <tr>
                                    <th>Total Students:</th>
                                    <td><?php echo $total_students; ?></td>
                                </tr>
                                <tr>
                                    <th>Passed:</th>
                                    <td><?php echo $passed_students; ?> (<?php echo round(($passed_students/$total_students)*100); ?>%)</td>
                                </tr>
                                <tr>
                                    <th>Failed:</th>
                                    <td><?php echo $failed_students; ?> (<?php echo round(($failed_students/$total_students)*100); ?>%)</td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-sm">
                                <tr><th colspan="2">Grade Distribution</th></tr>
                                <?php foreach ($grade_distribution as $grade => $count): ?>
                                <tr>
                                    <td><?php echo $grade; ?>:</td>
                                    <td><?php echo $count; ?> (<?php echo round(($count/$total_students)*100); ?>%)</td>
                                </tr>
                                <?php endforeach; ?>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <?php elseif ($selected_class && $exam_date): ?>
            <div class="alert alert-info">No results found for the selected class and exam date.</div>
            <?php endif; ?>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>
    <script src="assets/js/main.js"></script>
    <script>
        function printReport(elementId) {
            const printContents = document.getElementById(elementId).innerHTML;
            const originalContents = document.body.innerHTML;

            document.body.innerHTML = `
                <div class="print-header">
                    <h1>Class Results</h1>
                    <p>Class: ${document.querySelector('select[name="class"]').value}</p>
                    <p>Exam Type: ${document.querySelector('select[name="exam_type"] option:checked').text}</p>
                    <p>Date: ${document.querySelector('input[name="exam_date"]').value}</p>
                </div>
                ${printContents}
            `;

            window.print();
            document.body.innerHTML = originalContents;
            location.reload();
        }
    </script>
</body>
</html>

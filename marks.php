<?php
session_start();
require_once 'config/db_connect.php';
require_once 'includes/functions.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$class = isset($_GET['class']) ? $_GET['class'] : '';
$student_id = isset($_GET['student_id']) ? $_GET['student_id'] : '';

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

// Get marks if student is selected
$marks = [];
if ($student_id) {
    $marks = getStudentMarks($conn, $student_id);
}

$subjects = ['Mathematics', 'Science', 'English', 'History', 'Geography', 'Physics', 'Chemistry', 'Biology'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Marks - Student Management System</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <div class="container">
        <div class="card">
            <div class="card-header">
                <h2>Student Marks</h2>
                <div class="header-actions">
                    <form action="" method="GET" class="form-inline">
                        <select name="class" class="form-control" onchange="this.form.submit()">
                            <option value="">All Classes</option>
                            <?php foreach ($classes as $class_option): ?>
                            <option value="<?php echo $class_option; ?>" 
                                    <?php echo $class_option === $class ? 'selected' : ''; ?>>
                                <?php echo $class_option; ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </form>
                </div>
            </div>

            <div class="row">
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header">
                            <h3>Select Student</h3>
                        </div>
                        <div class="student-list">
                            <?php foreach ($students as $student): ?>
                            <a href="?class=<?php echo $class; ?>&student_id=<?php echo $student['id']; ?>" 
                               class="student-item <?php echo $student['id'] == $student_id ? 'active' : ''; ?>">
                                <?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name']); ?>
                                <span class="class-label"><?php echo htmlspecialchars($student['class']); ?></span>
                            </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <div class="col-md-8">
                    <?php if ($student_id): ?>
                    <div class="card">
                        <div class="card-header">
                            <h3>Add Marks</h3>
                        </div>
                        <form action="includes/save_marks.php" method="POST" id="marksForm">
                            <input type="hidden" name="student_id" value="<?php echo $student_id; ?>">
                            
                            <div class="form-group">
                                <label for="subject" class="form-label">Subject</label>
                                <select name="subject" id="subject" class="form-control" required>
                                    <option value="">Select Subject</option>
                                    <?php foreach ($subjects as $subject): ?>
                                    <option value="<?php echo $subject; ?>"><?php echo $subject; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="class" class="form-label">Class</label>
                                <select id="class" name="class" class="form-control" required>
                                    <option value="">Select Class</option>
                                    <?php
                                    for ($i = 1; $i <= 12; $i++) {
                                        $selected = isset($_GET['class']) && $_GET['class'] == $i ? 'selected' : '';
                                        echo "<option value=\"$i\" $selected>Class $i</option>";
                                    }
                                    ?>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="exam_type" class="form-label">Exam Type</label>
                                <select name="exam_type" id="exam_type" class="form-control" required>
                                    <option value="">Select Exam Type</option>
                                    <option value="Unit Test">Unit Test</option>
                                    <option value="Mid Term">Mid Term</option>
                                    <option value="Final Term">Final Term</option>
                                    <option value="Project">Project</option>
                                    <option value="Assignment">Assignment</option>
                                    <option value="Practical">Practical</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="marks" class="form-label">Marks</label>
                                <input type="number" name="marks" id="marks" class="form-control" 
                                       min="0" max="100" required>
                            </div>

                            <div class="form-group">
                                <label for="exam_date" class="form-label">Exam Date</label>
                                <input type="date" name="exam_date" id="exam_date" class="form-control" required>
                            </div>

                            <button type="submit" class="btn btn-primary">Save Marks</button>
                        </form>
                    </div>

                    <div class="card">
                        <div class="card-header">
                            <h3>Marks History</h3>
                        </div>
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Subject</th>
                                    <th>Exam Type</th>
                                    <th>Marks</th>
                                    <th>Exam Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($marks as $mark): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($mark['subject']); ?></td>
                                    <td><?php echo htmlspecialchars($mark['exam_type']); ?></td>
                                    <td><?php echo htmlspecialchars($mark['marks']); ?></td>
                                    <td><?php echo htmlspecialchars($mark['exam_date']); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                        <?php if (empty($marks)): ?>
                        <div class="alert alert-info">No marks recorded yet.</div>
                        <?php endif; ?>
                    </div>
                    <?php else: ?>
                    <div class="alert alert-info">Please select a student to view or add marks.</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>
    <script src="assets/js/main.js"></script>
</body>
</html>

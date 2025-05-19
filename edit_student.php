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

$success = $error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $student_data = [
        'first_name' => $_POST['first_name'],
        'last_name' => $_POST['last_name'],
        'email' => $_POST['email'],
        'phone' => $_POST['phone'],
        'date_of_birth' => $_POST['date_of_birth'],
        'gender' => $_POST['gender'],
        'address' => $_POST['address'],
        'class' => $_POST['class']
    ];

    if (updateStudent($conn, $student_id, $student_data)) {
        $success = "Student updated successfully!";
        $student = getStudentById($conn, $student_id); // Refresh student data
    } else {
        $error = "Error updating student: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Student - Student Management System</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <div class="container">
        <div class="card">
            <div class="card-header">
                <h2>Edit Student</h2>
            </div>

            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>

            <form method="POST" action="" id="studentForm">
                <div class="form-group">
                    <label for="student_id" class="form-label">Student ID</label>
                    <input type="text" id="student_id" class="form-control" value="<?php echo htmlspecialchars($student['student_id']); ?>" readonly>
                </div>

                <div class="form-group">
                    <label for="first_name" class="form-label">First Name</label>
                    <input type="text" id="first_name" name="first_name" class="form-control" value="<?php echo htmlspecialchars($student['first_name']); ?>" required>
                </div>

                <div class="form-group">
                    <label for="last_name" class="form-label">Last Name</label>
                    <input type="text" id="last_name" name="last_name" class="form-control" value="<?php echo htmlspecialchars($student['last_name']); ?>" required>
                </div>

                <div class="form-group">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" id="email" name="email" class="form-control" value="<?php echo htmlspecialchars($student['email']); ?>" required>
                </div>

                <div class="form-group">
                    <label for="phone" class="form-label">Phone</label>
                    <input type="tel" id="phone" name="phone" class="form-control" value="<?php echo htmlspecialchars($student['phone']); ?>">
                </div>

                <div class="form-group">
                    <label for="date_of_birth" class="form-label">Date of Birth</label>
                    <input type="date" id="date_of_birth" name="date_of_birth" class="form-control" value="<?php echo htmlspecialchars($student['date_of_birth']); ?>" required>
                </div>

                <div class="form-group">
                    <label for="gender" class="form-label">Gender</label>
                    <select id="gender" name="gender" class="form-control" required>
                        <option value="">Select Gender</option>
                        <option value="Male" <?php echo $student['gender'] === 'Male' ? 'selected' : ''; ?>>Male</option>
                        <option value="Female" <?php echo $student['gender'] === 'Female' ? 'selected' : ''; ?>>Female</option>
                        <option value="Other" <?php echo $student['gender'] === 'Other' ? 'selected' : ''; ?>>Other</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="class">Class</label>
                    <select id="class" name="class" required class="form-control">
                        <option value="">Select Class</option>
                        <?php
                        $classes = ['Class 1', 'Class 2', 'Class 3', 'Class 4', 'Class 5', 
                                  'Class 6', 'Class 7', 'Class 8', 'Class 9', 'Class 10'];
                        foreach ($classes as $class):
                        ?>
                        <option value="<?php echo $class; ?>" <?php echo $student['class'] === $class ? 'selected' : ''; ?>>
                            <?php echo $class; ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <button type="submit" class="btn btn-primary">Update Student</button>
                    <a href="students.php" class="btn btn-danger">Cancel</a>
                </div>
            </form>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>
    <script src="assets/js/main.js"></script>
</body>
</html>

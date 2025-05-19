<?php
session_start();
require_once 'config/db_connect.php';
require_once 'includes/functions.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Get all students with pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

$search = isset($_GET['search']) ? $_GET['search'] : '';

if ($search) {
    $students = searchStudents($conn, $search);
    $total = count($students);
} else {
    $sql = "SELECT * FROM students ORDER BY first_name LIMIT ? OFFSET ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $limit, $offset);
    $stmt->execute();
    $result = $stmt->get_result();
    $students = $result->fetch_all(MYSQLI_ASSOC);

    $total_result = $conn->query("SELECT COUNT(*) as count FROM students");
    $total = $total_result->fetch_assoc()['count'];
}

$total_pages = ceil($total / $limit);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Students - Student Management System</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <div class="container">
        <div class="card">
            <div class="card-header">
                <h2>Students</h2>
                <div class="header-actions">
                    <input type="text" id="searchStudent" placeholder="Search students..." class="form-control" style="width: 200px;">
                    <a href="add_student.php" class="btn btn-primary">Add New Student</a>
                </div>
            </div>

            <table class="table" id="studentTable">
                <thead>
                    <tr>
                        <th>Student ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Class</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($students as $student): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($student['student_id']); ?></td>
                        <td><?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name']); ?></td>
                        <td><?php echo htmlspecialchars($student['email']); ?></td>
                        <td><?php echo htmlspecialchars($student['class']); ?></td>
                        <td>
                            <a href="view_student.php?id=<?php echo $student['id']; ?>" class="btn btn-primary btn-sm">View</a>
                            <a href="edit_student.php?id=<?php echo $student['id']; ?>" class="btn btn-primary btn-sm">Edit</a>
                            <a href="includes/delete_student.php?id=<?php echo $student['id']; ?>" 
                               class="btn btn-danger btn-sm delete-student">Delete</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <?php if ($total_pages > 1 && !$search): ?>
            <div class="pagination">
                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <a href="?page=<?php echo $i; ?>" 
                       class="btn <?php echo $i === $page ? 'btn-primary' : ''; ?>">
                        <?php echo $i; ?>
                    </a>
                <?php endfor; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>
    <script src="assets/js/main.js"></script>
</body>
</html>

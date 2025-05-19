<?php
session_start();
require_once 'config/db_connect.php';
require_once 'includes/functions.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Get all teachers with search functionality
$search = isset($_GET['search']) ? $_GET['search'] : '';
$sql = "SELECT * FROM teachers WHERE 
        teacher_id LIKE ? OR 
        first_name LIKE ? OR 
        last_name LIKE ? OR 
        email LIKE ? OR
        subject_specialty LIKE ?
        ORDER BY first_name";

$search_term = "%$search%";
$stmt = $conn->prepare($sql);
$stmt->bind_param("sssss", $search_term, $search_term, $search_term, $search_term, $search_term);
$stmt->execute();
$result = $stmt->get_result();
$teachers = $result->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teachers Management - Student Management System</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .teacher-card {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 20px;
            padding: 20px;
            transition: transform 0.2s;
        }
        .teacher-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
        }
        .teacher-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }
        .teacher-name {
            font-size: 1.2em;
            font-weight: 600;
            color: #2c3e50;
        }
        .teacher-specialty {
            color: #3498db;
            font-weight: 500;
        }
        .teacher-info {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 10px;
            margin-bottom: 15px;
        }
        .teacher-info p {
            margin: 5px 0;
            color: #666;
        }
        .teacher-actions {
            display: flex;
            gap: 10px;
        }
        .status-active {
            color: #27ae60;
            font-weight: 500;
        }
        .status-inactive {
            color: #e74c3c;
            font-weight: 500;
        }
        .search-box {
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <div class="container">
        <div class="page-header">
            <h2>Teachers Management</h2>
            <a href="add_teacher.php" class="btn btn-primary">Add New Teacher</a>
        </div>

        <div class="search-box">
            <form action="" method="GET" class="search-form">
                <input type="text" name="search" placeholder="Search teachers..." value="<?php echo htmlspecialchars($search); ?>" class="form-control">
                <button type="submit" class="btn btn-primary">Search</button>
            </form>
        </div>

        <div class="teachers-grid">
            <?php if (empty($teachers)): ?>
            <div class="alert alert-info">No teachers found.</div>
            <?php else: ?>
                <?php foreach ($teachers as $teacher): ?>
                <div class="teacher-card">
                    <div class="teacher-header">
                        <div>
                            <div class="teacher-name"><?php echo htmlspecialchars($teacher['first_name'] . ' ' . $teacher['last_name']); ?></div>
                            <div class="teacher-specialty"><?php echo htmlspecialchars($teacher['subject_specialty']); ?></div>
                        </div>
                        <span class="status-<?php echo $teacher['status']; ?>">
                            <?php echo ucfirst($teacher['status']); ?>
                        </span>
                    </div>
                    <div class="teacher-info">
                        <p><strong>Teacher ID:</strong> <?php echo htmlspecialchars($teacher['teacher_id']); ?></p>
                        <p><strong>Email:</strong> <?php echo htmlspecialchars($teacher['email']); ?></p>
                        <p><strong>Phone:</strong> <?php echo htmlspecialchars($teacher['phone']); ?></p>
                        <p><strong>Qualification:</strong> <?php echo htmlspecialchars($teacher['qualification']); ?></p>
                        <p><strong>Joining Date:</strong> <?php echo date('M d, Y', strtotime($teacher['joining_date'])); ?></p>
                    </div>
                    <div class="teacher-actions">
                        <a href="edit_teacher.php?id=<?php echo $teacher['id']; ?>" class="btn btn-primary btn-sm">Edit</a>
                        <a href="view_teacher.php?id=<?php echo $teacher['id']; ?>" class="btn btn-info btn-sm">View Details</a>
                        <?php if ($_SESSION['role'] === 'admin'): ?>
                        <button onclick="toggleTeacherStatus(<?php echo $teacher['id']; ?>, '<?php echo $teacher['status']; ?>')" 
                                class="btn btn-<?php echo $teacher['status'] === 'active' ? 'warning' : 'success'; ?> btn-sm">
                            <?php echo $teacher['status'] === 'active' ? 'Deactivate' : 'Activate'; ?>
                        </button>
                        <button onclick="deleteTeacher(<?php echo $teacher['id']; ?>)" class="btn btn-danger btn-sm">Delete</button>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>
    <script src="assets/js/main.js"></script>
    <script>
        function toggleTeacherStatus(teacherId, currentStatus) {
            if (confirm('Are you sure you want to ' + (currentStatus === 'active' ? 'deactivate' : 'activate') + ' this teacher?')) {
                fetch('includes/toggle_teacher_status.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'teacher_id=' + teacherId
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showAlert(data.message, 'success');
                        setTimeout(() => window.location.reload(), 1000);
                    } else {
                        showAlert(data.message, 'danger');
                    }
                })
                .catch(error => {
                    showAlert('An error occurred while updating status', 'danger');
                });
            }
        }

        function deleteTeacher(teacherId) {
            if (confirm('Are you sure you want to delete this teacher? This action cannot be undone.')) {
                fetch('includes/delete_teacher.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'teacher_id=' + teacherId
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showAlert(data.message, 'success');
                        setTimeout(() => window.location.reload(), 1000);
                    } else {
                        showAlert(data.message, 'danger');
                    }
                })
                .catch(error => {
                    showAlert('An error occurred while deleting teacher', 'danger');
                });
            }
        }
    </script>
</body>
</html>

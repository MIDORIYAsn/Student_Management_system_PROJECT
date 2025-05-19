<?php
session_start();
require_once 'config/db_connect.php';
require_once 'includes/functions.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Get teacher ID from URL
$teacher_id = isset($_GET['id']) ? $_GET['id'] : 0;

// Get teacher details
$sql = "SELECT * FROM teachers WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $teacher_id);
$stmt->execute();
$result = $stmt->get_result();
$teacher = $result->fetch_assoc();

// If teacher not found, redirect to teachers list
if (!$teacher) {
    $_SESSION['error'] = "Teacher not found.";
    header('Location: teachers.php');
    exit();
}

// Get assigned classes
$assigned_classes = [];

// Check if class_teachers table exists
$table_exists = $conn->query("SHOW TABLES LIKE 'class_teachers'")->num_rows > 0;

if ($table_exists) {
    $sql = "SELECT DISTINCT class FROM class_teachers WHERE teacher_id = ? ORDER BY class";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $teacher_id);
    $stmt->execute();
    $classes_result = $stmt->get_result();
    while ($row = $classes_result->fetch_assoc()) {
        $assigned_classes[] = $row['class'];
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Teacher - Student Management System</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .teacher-profile {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            padding: 2rem;
            margin-bottom: 2rem;
        }
        .profile-header {
            display: flex;
            align-items: center;
            gap: 2rem;
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid #eee;
        }
        .profile-image {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            background-color: #f0f0f0;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 3rem;
            color: #666;
        }
        .profile-info h2 {
            margin: 0;
            color: var(--secondary-color);
        }
        .profile-info .specialty {
            color: var(--primary-color);
            font-size: 1.1rem;
            margin: 0.5rem 0;
        }
        .profile-info .status {
            display: inline-block;
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.9rem;
            font-weight: 500;
        }
        .status.active {
            background-color: #e3f2fd;
            color: #1976d2;
        }
        .status.inactive {
            background-color: #ffebee;
            color: #c62828;
        }
        .details-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
            margin-top: 2rem;
        }
        .detail-section {
            background: #f8f9fa;
            padding: 1.5rem;
            border-radius: 8px;
        }
        .detail-section h3 {
            margin-top: 0;
            color: var(--secondary-color);
            font-size: 1.2rem;
            margin-bottom: 1rem;
        }
        .detail-item {
            margin-bottom: 1rem;
        }
        .detail-item:last-child {
            margin-bottom: 0;
        }
        .detail-label {
            color: #666;
            font-size: 0.9rem;
            margin-bottom: 0.25rem;
        }
        .detail-value {
            color: var(--secondary-color);
            font-weight: 500;
        }
        .assigned-classes {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
        }
        .class-badge {
            background: var(--primary-color);
            color: white;
            padding: 0.25rem 0.75rem;
            border-radius: 15px;
            font-size: 0.9rem;
        }
        .actions {
            margin-top: 2rem;
            display: flex;
            gap: 1rem;
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <div class="container">
        <div class="breadcrumb">
            <a href="teachers.php" class="btn btn-link"><i class="fas fa-arrow-left"></i> Back to Teachers</a>
        </div>

        <div class="teacher-profile">
            <div class="profile-header">
                <div class="profile-image">
                    <i class="fas fa-user"></i>
                </div>
                <div class="profile-info">
                    <h2><?php echo htmlspecialchars($teacher['first_name'] . ' ' . $teacher['last_name']); ?></h2>
                    <div class="specialty"><?php echo htmlspecialchars($teacher['subject_specialty']); ?></div>
                    <span class="status <?php echo $teacher['status']; ?>">
                        <?php echo ucfirst($teacher['status']); ?>
                    </span>
                </div>
            </div>

            <div class="details-grid">
                <div class="detail-section">
                    <h3>Personal Information</h3>
                    <div class="detail-item">
                        <div class="detail-label">Teacher ID</div>
                        <div class="detail-value"><?php echo htmlspecialchars($teacher['teacher_id']); ?></div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Email</div>
                        <div class="detail-value"><?php echo htmlspecialchars($teacher['email']); ?></div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Phone</div>
                        <div class="detail-value"><?php echo htmlspecialchars($teacher['phone']); ?></div>
                    </div>
                </div>

                <div class="detail-section">
                    <h3>Professional Information</h3>
                    <div class="detail-item">
                        <div class="detail-label">Qualification</div>
                        <div class="detail-value"><?php echo htmlspecialchars($teacher['qualification']); ?></div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Subject Specialty</div>
                        <div class="detail-value"><?php echo htmlspecialchars($teacher['subject_specialty']); ?></div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Joining Date</div>
                        <div class="detail-value"><?php echo date('F d, Y', strtotime($teacher['joining_date'])); ?></div>
                    </div>
                </div>

                <div class="detail-section">
                    <h3>Assigned Classes</h3>
                    <div class="assigned-classes">
                        <?php if (empty($assigned_classes)): ?>
                            <p>No classes assigned yet.</p>
                        <?php else: ?>
                            <?php foreach ($assigned_classes as $class): ?>
                                <span class="class-badge">Class <?php echo htmlspecialchars($class); ?></span>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="actions">
                <a href="edit_teacher.php?id=<?php echo $teacher['id']; ?>" class="btn btn-primary">
                    <i class="fas fa-edit"></i> Edit Teacher
                </a>
                <?php if ($_SESSION['role'] === 'admin'): ?>
                <button onclick="toggleTeacherStatus(<?php echo $teacher['id']; ?>, '<?php echo $teacher['status']; ?>')" 
                        class="btn btn-<?php echo $teacher['status'] === 'active' ? 'warning' : 'success'; ?>">
                    <i class="fas fa-<?php echo $teacher['status'] === 'active' ? 'user-slash' : 'user-check'; ?>"></i>
                    <?php echo $teacher['status'] === 'active' ? 'Deactivate' : 'Activate'; ?> Teacher
                </button>
                <?php endif; ?>
            </div>
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
    </script>
</body>
</html>

<?php
session_start();
require_once 'config/db_connect.php';
require_once 'includes/functions.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Teacher - Student Management System</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <div class="container">
        <div class="card">
            <div class="card-header">
                <h2>Add New Teacher</h2>
            </div>
            <div class="card-body">
                <form id="teacherForm" method="POST" action="includes/add_teacher.php">
                    <div class="form-group">
                        <label for="first_name">First Name *</label>
                        <input type="text" id="first_name" name="first_name" required class="form-control">
                    </div>

                    <div class="form-group">
                        <label for="last_name">Last Name *</label>
                        <input type="text" id="last_name" name="last_name" required class="form-control">
                    </div>

                    <div class="form-group">
                        <label for="email">Email *</label>
                        <input type="email" id="email" name="email" required class="form-control">
                    </div>

                    <div class="form-group">
                        <label for="phone">Phone</label>
                        <input type="tel" id="phone" name="phone" class="form-control">
                    </div>

                    <div class="form-group">
                        <label for="date_of_birth">Date of Birth</label>
                        <input type="date" id="date_of_birth" name="date_of_birth" class="form-control">
                    </div>

                    <div class="form-group">
                        <label for="gender">Gender</label>
                        <select id="gender" name="gender" class="form-control">
                            <option value="">Select Gender</option>
                            <option value="male">Male</option>
                            <option value="female">Female</option>
                            <option value="other">Other</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="address">Address</label>
                        <textarea id="address" name="address" class="form-control" rows="3"></textarea>
                    </div>

                    <div class="form-group">
                        <label for="subject_specialty">Subject Specialty *</label>
                        <input type="text" id="subject_specialty" name="subject_specialty" required class="form-control">
                    </div>

                    <div class="form-group">
                        <label for="qualification">Qualification *</label>
                        <input type="text" id="qualification" name="qualification" required class="form-control">
                    </div>

                    <div class="form-group">
                        <label for="joining_date">Joining Date *</label>
                        <input type="date" id="joining_date" name="joining_date" required class="form-control" 
                               value="<?php echo date('Y-m-d'); ?>">
                    </div>

                    <div class="form-buttons">
                        <button type="submit" class="btn btn-primary">Add Teacher</button>
                        <a href="teachers.php" class="btn btn-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>
    <script src="assets/js/main.js"></script>
    <script>
    document.getElementById('teacherForm').addEventListener('submit', async function(e) {
        e.preventDefault();
        if (validateForm('teacherForm')) {
            try {
                const formData = new FormData(this);
                const response = await fetch('includes/add_teacher.php', {
                    method: 'POST',
                    body: formData
                });
                
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                
                const data = await response.json();
                if (data.success) {
                    showAlert(data.message, 'success');
                    setTimeout(() => {
                        window.location.href = 'teachers.php';
                    }, 1500);
                } else {
                    showAlert(data.message, 'danger');
                }
            } catch (error) {
                console.error('Error:', error);
                showAlert('An error occurred. Please try again.', 'danger');
            }
        }
    });
    </script>
</body>
</html>

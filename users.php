<?php
session_start();
require_once 'config/db_connect.php';
require_once 'includes/functions.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit();
}

// Get all users with additional info
$sql = "SELECT u.id, u.username, u.role, u.created_at, 
        (SELECT COUNT(*) FROM students) as total_students,
        (SELECT COUNT(*) FROM teachers) as total_teachers
        FROM users u ORDER BY u.created_at DESC";
$result = $conn->query($sql);
$users = [];
$stats = ['students' => 0, 'teachers' => 0];

if ($row = $result->fetch_assoc()) {
    $stats['students'] = $row['total_students'];
    $stats['teachers'] = $row['total_teachers'];
    do {
        unset($row['total_students']);
        unset($row['total_teachers']);
        $users[] = $row;
    } while ($row = $result->fetch_assoc());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management - Student Management System</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <div class="container">
        <div class="dashboard-header">
            <h1>User Management</h1>
            <button class="btn btn-primary" onclick="showAddUserModal()">
                <i class="fas fa-plus"></i> Add New User
            </button>
        </div>

        <!-- Statistics Cards -->
        <div class="stats-container">
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-users"></i>
                </div>
                <div class="stat-info">
                    <h3>Total Users</h3>
                    <p class="stat-number"><?php echo count($users); ?></p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-user-graduate"></i>
                </div>
                <div class="stat-info">
                    <h3>Total Students</h3>
                    <p class="stat-number"><?php echo $stats['students']; ?></p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-chalkboard-teacher"></i>
                </div>
                <div class="stat-info">
                    <h3>Total Teachers</h3>
                    <p class="stat-number"><?php echo $stats['teachers']; ?></p>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h2>User List</h2>
                <div class="search-box">
                    <input type="text" id="userSearch" onkeyup="searchUsers()" placeholder="Search users..." class="form-control">
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Username</th>
                                <th>Role</th>
                                <th>Created At</th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($users as $user): ?>
                            <tr>
                                <td>
                                    <div class="user-info">
                                        <span class="user-icon <?php echo $user['role'] === 'admin' ? 'admin' : 'teacher'; ?>">
                                            <i class="fas <?php echo $user['role'] === 'admin' ? 'fa-user-shield' : 'fa-user'; ?>"></i>
                                        </span>
                                        <?php echo htmlspecialchars($user['username']); ?>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge <?php echo $user['role'] === 'admin' ? 'badge-primary' : 'badge-info'; ?>">
                                        <?php echo ucfirst(htmlspecialchars($user['role'])); ?>
                                    </span>
                                </td>
                                <td><?php echo date('M d, Y', strtotime($user['created_at'])); ?></td>
                                <td class="text-center">
                                    <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                    <button class="btn btn-sm btn-warning" onclick="showEditUserModal(<?php echo $user['id']; ?>)">
                                        <i class="fas fa-edit"></i> Edit
                                    </button>
                                    <button class="btn btn-sm btn-danger" onclick="deleteUser(<?php echo $user['id']; ?>)">
                                        <i class="fas fa-trash"></i> Delete
                                    </button>
                                    <?php else: ?>
                                    <span class="badge badge-secondary">Current User</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Add User Modal -->
    <div id="addUserModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2><i class="fas fa-user-plus"></i> Add New User</h2>
                <span class="close">&times;</span>
            </div>
            <form id="addUserForm" method="POST" action="includes/add_user.php">
                <div class="modal-body">
                    <div class="form-group">
                        <label for="username">
                            <i class="fas fa-user"></i> Username
                        </label>
                        <input type="text" id="username" name="username" required 
                               class="form-control" placeholder="Enter username">
                    </div>
                    <div class="form-group">
                        <label for="password">
                            <i class="fas fa-lock"></i> Password
                        </label>
                        <div class="password-input">
                            <input type="password" id="password" name="password" required 
                                   class="form-control" placeholder="Enter password">
                            <span class="toggle-password" onclick="togglePassword('password')">
                                <i class="fas fa-eye"></i>
                            </span>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="role">
                            <i class="fas fa-user-shield"></i> Role
                        </label>
                        <select id="role" name="role" required class="form-control">
                            <option value="">Select Role</option>
                            <option value="admin">Administrator</option>
                            <option value="teacher">Teacher</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('addUserModal')">
                        <i class="fas fa-times"></i> Cancel
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Add User
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit User Modal -->
    <div id="editUserModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2>Edit User</h2>
            <form id="editUserForm" method="POST" action="includes/edit_user.php">
                <input type="hidden" id="edit_user_id" name="user_id">
                <div class="form-group">
                    <label for="edit_username">Username</label>
                    <input type="text" id="edit_username" name="username" required class="form-control">
                </div>
                <div class="form-group">
                    <label for="edit_password">New Password (leave blank to keep current)</label>
                    <input type="password" id="edit_password" name="password" class="form-control">
                </div>
                <div class="form-group">
                    <label for="edit_role">Role</label>
                    <select id="edit_role" name="role" required class="form-control">
                        <option value="admin">Admin</option>
                        <option value="teacher">Teacher</option>
                    </select>
                </div>
                <div class="form-buttons">
                    <button type="submit" class="btn btn-primary">Update User</button>
                    <button type="button" class="btn btn-secondary" onclick="closeModal('editUserModal')">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>
    <script src="assets/js/main.js"></script>
    <script>
        // Modal Functions
        function showAddUserModal() {
            document.getElementById('addUserModal').style.display = 'block';
            document.getElementById('username').focus();
                    showAlert(data.message, 'success');
                    setTimeout(() => location.reload(), 1500);
                } else {
                    showAlert(data.message, 'danger');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showAlert('Error deleting user', 'danger');
            });
        }
    }

    // Close modal when clicking outside
    window.onclick = function(event) {
        if (event.target.className === 'modal') {
            event.target.style.display = 'none';
        }
    }

    // Handle form submissions
    document.getElementById('addUserForm').addEventListener('submit', async function(e) {
        e.preventDefault();
        try {
            const formData = new FormData(this);
            const response = await fetch('includes/add_user.php', {
                method: 'POST',
                body: formData
            });
            const data = await response.json();
            if (data.success) {
                showAlert(data.message, 'success');
                setTimeout(() => location.reload(), 1500);
            } else {
                showAlert(data.message, 'danger');
            }
        } catch (error) {
            console.error('Error:', error);
            showAlert('Error adding user', 'danger');
        }
    });

    document.getElementById('editUserForm').addEventListener('submit', async function(e) {
        e.preventDefault();
        try {
            const formData = new FormData(this);
            const response = await fetch('includes/edit_user.php', {
                method: 'POST',
                body: formData
            });
            const data = await response.json();
            if (data.success) {
                showAlert(data.message, 'success');
                setTimeout(() => location.reload(), 1500);
            } else {
                showAlert(data.message, 'danger');
            }
        } catch (error) {
            console.error('Error:', error);
            showAlert('Error updating user', 'danger');
        }
    });
    </script>
</body>
</html>

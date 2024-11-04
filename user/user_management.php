<?php
include '../backend/db_connection.php';

// Function to fetch users
function fetchUsers($conn) {
    $sql = "SELECT * FROM users";
    return mysqli_query($conn, $sql);
}

// Handle user deletion
if (isset($_GET['delete'])) {
    $userId = $_GET['delete'];
    $sql = "DELETE FROM users WHERE id = $userId";
    if (mysqli_query($conn, $sql)) {
        echo "<div class='alert alert-success'>User deleted successfully!</div>";
    } else {
        echo "<div class='alert alert-danger'>Error deleting user: " . mysqli_error($conn) . "</div>";
    }
}

// Handle user approval
if (isset($_GET['approve'])) {
    $userId = $_GET['approve'];
    $sql = "UPDATE users SET status = 'approved' WHERE id = $userId";
    if (mysqli_query($conn, $sql)) {
        echo "<div class='alert alert-success'>User approved successfully!</div>";
    } else {
        echo "<div class='alert alert-danger'>Error approving user: " . mysqli_error($conn) . "</div>";
    }
}

// Fetch users for display
$users = fetchUsers($conn);

// Close the connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management | VirusGuard</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body {
            background-color: #f8f9fa;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }
        .container {
            margin-top: 50px;
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            flex: 1;
        }
        h2 {
            color: #007bff;
        }
        footer {
            padding: 10px 0;
            background-color: #007bff;
            color: white;
            position: relative;
            bottom: 0;
            width: 100%;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2 class="text-center">User Management Dashboard</h2>

        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Username</th>
                    <th>Email</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (mysqli_num_rows($users) > 0): ?>
                    <?php while ($user = mysqli_fetch_assoc($users)): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($user['id']); ?></td>
                            <td><?php echo htmlspecialchars($user['username']); ?></td>
                            <td><?php echo htmlspecialchars($user['email']); ?></td>
                            <td><?php echo htmlspecialchars($user['status']); ?></td>
                            <td>
                                <a href="?delete=<?php echo $user['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this user?');">Delete</a>
                                <?php if ($user['status'] == 'pending'): ?>
                                    <a href="?approve=<?php echo $user['id']; ?>" class="btn btn-success btn-sm" onclick="return confirm('Are you sure you want to approve this user?');">Approve</a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" class="text-center">No users found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
        <a href="register.php" class="btn btn-primary">Add New User</a>
    </div>

    <footer class="text-center">
        <p>&copy; 2024 VirusGuard. All rights reserved.</p>
    </footer>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>

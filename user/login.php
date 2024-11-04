<?php
include '../backend/db_connection.php';
session_start();

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set session timeout duration (e.g., 30 minutes)
define('TIMEOUT_DURATION', 1800); // 30 minutes

// Check if the session has timed out
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > TIMEOUT_DURATION)) {
    session_unset(); // Unset session variables
    session_destroy(); // Destroy session
    header("Location: login.php?message=session_timeout");
    exit();
}
$_SESSION['last_activity'] = time(); // Update last activity time

$error_message = ''; // Initialize error message variable

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = $_POST['password'];

    // Use prepared statements to fetch user based on username
    $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
    if ($stmt === false) {
        die('Prepare failed: ' . htmlspecialchars($conn->error));
    }

    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    
    // Check if user exists
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();

        // Check if user is approved
        if ($user['status'] === 'approved') {
            // Verify password
            if (password_verify($password, $user['password'])) {
                session_regenerate_id(true); // Regenerate session ID
                $_SESSION['username'] = $username; // Store username in session
                $_SESSION['user_id'] = $user['id']; // Store user ID in session

                // Redirect based on username
                $redirect_url = ($username === 'elias') ? '../dashboard.php' : '../vaccination/vaccination_tracker.php';
                header("Location: $redirect_url");
                exit();
            } else {
                $error_message = "Invalid credentials. Please try again.";
            }
        } else {
            $error_message = "Your account is not approved yet. Please contact the administrator.";
        }
    } else {
        $error_message = "Invalid credentials. Please try again.";
    }

    // Close statement
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | VirusGuard</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body {
            background: linear-gradient(to right, #007bff, #6a82fb);
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-container {
            background: white;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 4px 30px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 400px;
        }
        h2 {
            margin-bottom: 20px;
            text-align: center;
            color: #007bff;
        }
        .btn-primary {
            width: 100%;
        }
        .alert {
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <h2>Login</h2>
        
        <?php if (!empty($error_message)): ?>
            <div class="alert alert-danger">
                <?php echo htmlspecialchars($error_message); ?>
            </div>
        <?php endif; ?>
        
        <?php if (isset($_GET['message']) && $_GET['message'] == 'session_timeout'): ?>
            <div class="alert alert-warning">
                Your session has timed out. Please log in again.
            </div>
        <?php endif; ?>
        
        <form action="login.php" method="POST">
            <div class="form-group">
                <label for="username">Username:</label>
                <input type="text" class="form-control" name="username" required>
            </div>
            <div class="form-group">
                <label for="password">Password:</label>
                <input type="password" class="form-control" name="password" required>
            </div>
            <button type="submit" class="btn btn-primary">Login</button>
        </form>
        <p class="text-center mt-3">
            <a href="register.php">Don't have an account? Register here</a>
        </p>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>

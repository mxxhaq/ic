<?php
session_start();

// Include the database connection
require_once 'db_connect.php'; 

$loginError = '';  // Variable to store any login error messages

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Retrieve the username and password from the form submission
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    // Validate if both fields are filled
    if (empty($username) || empty($password)) {
        $loginError = "Please enter both username and password.";
    } else {
        // Prepare and execute SQL query to check user credentials
        $query = $pdo->prepare("SELECT id, username, password, role FROM users WHERE username = ?");
        $query->execute([$username]);
        $user = $query->fetch();

        // Check if the user exists and the password is correct
        if ($user) {
            // Check the password using password_verify
            if (password_verify($password, $user['password'])) {
                // Set session variables on successful login
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role'];

                // Redirect to the dashboard page
                header("Location: dashboard.php");
                exit;
            } else {
                // Invalid password
                $loginError = "Invalid username or password.";
            }
        } else {
            // Invalid username
            $loginError = "Invalid username or password.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Grades System - Login</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css">
</head>
<body class="bg-light">
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white">
                        <h3 class="mb-0">Student Grades System</h3>
                    </div>
                    <div class="card-body">
                        <!-- Display login error message if any -->
                        <?php if (!empty($loginError)): ?>
                            <div class="alert alert-danger"><?php echo $loginError; ?></div>
                        <?php endif; ?>

                        <!-- Login form -->
                        <form method="POST" action="">
                            <div class="mb-3">
                                <label for="username" class="form-label">Username</label>
                                <input type="text" class="form-control" id="username" name="username" required>
                            </div>
                            <div class="mb-3">
                                <label for="password" class="form-label">Password</label>
                                <input type="password" class="form-control" id="password" name="password" required>
                            </div>
                            <button type="submit" class="btn btn-primary">Login</button>
                        </form>
                    </div>
                    <div class="card-footer text-muted">
                        <p class="mb-0">Default credentials: admin / admin123</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>

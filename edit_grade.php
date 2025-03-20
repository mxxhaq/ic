<?php
session_start();

// Check if the user is logged in, if not, redirect to the login page
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

// Include the database connection
require_once 'db_connect.php';

// Ensure an ID is provided for the grade record
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: dashboard.php");
    exit;
}

$recordId = $_GET['id'];  // Store the grade record ID for later use
$errorMessage = '';  // Variable to store error messages
$successMessage = '';  // Variable to store success messages

// Fetch the grade record based on the provided ID
try {
    $query = $pdo->prepare("SELECT c.*, n.student_name 
                            FROM course_table c 
                            JOIN name_table n ON c.student_id = n.student_id 
                            WHERE c.id = ?");
    $query->execute([$recordId]);
    $gradeRecord = $query->fetch();
    
    if (!$gradeRecord) {
        header("Location: dashboard.php");
        exit;
    }
} catch (PDOException $e) {
    $errorMessage = "Error fetching record: " . $e->getMessage();
}

// Handle form submission to update grades
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $grade1 = $_POST['grade1'];
    $grade2 = $_POST['grade2'];
    $grade3 = $_POST['grade3'];
    $grade4 = $_POST['grade4'];
    
    // Validate the grades
    if (!is_numeric($grade1) || $grade1 < 0 || $grade1 > 100 ||
        !is_numeric($grade2) || $grade2 < 0 || $grade2 > 100 ||
        !is_numeric($grade3) || $grade3 < 0 || $grade3 > 100 ||
        !is_numeric($grade4) || $grade4 < 0 || $grade4 > 100) {
        $errorMessage = "Grades must be numeric values between 0 and 100.";
    } else {
        try {
            // Update the grade record in the database
            $updateQuery = $pdo->prepare("UPDATE course_table 
                                          SET grade1 = ?, grade2 = ?, grade3 = ?, grade4 = ? 
                                          WHERE id = ?");
            $updateQuery->execute([$grade1, $grade2, $grade3, $grade4, $recordId]);
            
            $successMessage = "Grade updated successfully!";
            
            // Fetch the updated grade record to reflect changes
            $query->execute([$recordId]);
            $gradeRecord = $query->fetch();
        } catch (PDOException $e) {
            $errorMessage = "Error updating record: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Grade - Student Grades System</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css">
</head>
<body class="bg-light">
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary mb-4">
        <div class="container">
            <a class="navbar-brand" href="dashboard.php">Student Grades System</a>
            <div class="collapse navbar-collapse">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <span class="nav-link">Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?></span>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="logout.php">Logout</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container">
        <div class="row mb-4">
            <div class="col">
                <h2>Edit Grade Record</h2>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Edit Grade</li>
                    </ol>
                </nav>
            </div>
        </div>

        <!-- Display error message if any -->
        <?php if (!empty($errorMessage)): ?>
            <div class="alert alert-danger"><?php echo $errorMessage; ?></div>
        <?php endif; ?>

        <!-- Display success message if any -->
        <?php if (!empty($successMessage)): ?>
            <div class="alert alert-success"><?php echo $successMessage; ?></div>
        <?php endif; ?>

        <div class="card shadow">
            <div class="card-body">
                <form method="POST" action="">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Student ID</label>
                            <input type="text" class="form-control" value="<?php echo htmlspecialchars($gradeRecord['student_id']); ?>" readonly>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Student Name</label>
                            <input type="text" class="form-control" value="<?php echo htmlspecialchars($gradeRecord['student_name']); ?>" readonly>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Course Code</label>
                            <input type="text" class="form-control" value="<?php echo htmlspecialchars($gradeRecord['course_code']); ?>" readonly>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-3">
                            <label for="grade1" class="form-label">Grade 1</label>
                            <input type="number" class="form-control" id="grade1" name="grade1" value="<?php echo htmlspecialchars($gradeRecord['grade1']); ?>" min="0" max="100" required>
                        </div>
                        <div class="col-md-3">
                            <label for="grade2" class="form-label">Grade 2</label>
                            <input type="number" class="form-control" id="grade2" name="grade2" value="<?php echo htmlspecialchars($gradeRecord['grade2']); ?>" min="0" max="100" required>
                        </div>
                        <div class="col-md-3">
                            <label for="grade3" class="form-label">Grade 3</label>
                            <input type="number" class="form-control" id="grade3" name="grade3" value="<?php echo htmlspecialchars($gradeRecord['grade3']); ?>" min="0" max="100" required>
                        </div>
                        <div class="col-md-3">
                            <label for="grade4" class="form-label">Grade 4</label>
                            <input type="number" class="form-control" id="grade4" name="grade4" value="<?php echo htmlspecialchars($gradeRecord['grade4']); ?>" min="0" max="100" required>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col">
                            <button type="submit" class="btn btn-primary">Update Grade</button>
                            <a href="dashboard.php" class="btn btn-secondary">Cancel</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>


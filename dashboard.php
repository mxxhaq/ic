<?php
session_start();

// Check if user is logged in, if not, redirect to login page
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

// Include the database connection
require_once 'db_connect.php';

// Function to calculate the average of four grades
function calculateAverageGrade($grade1, $grade2, $grade3, $grade4) {
    return round(($grade1 + $grade2 + $grade3 + $grade4) / 4);
}

// Function to get the corresponding letter grade based on the final average
function determineLetterGrade($averageGrade) {
    if ($averageGrade >= 90) return 'A+';
    elseif ($averageGrade >= 85) return 'A';
    elseif ($averageGrade >= 80) return 'A-';
    elseif ($averageGrade >= 77) return 'B+';
    elseif ($averageGrade >= 73) return 'B';
    elseif ($averageGrade >= 70) return 'B-';
    elseif ($averageGrade >= 67) return 'C+';
    elseif ($averageGrade >= 63) return 'C';
    elseif ($averageGrade >= 60) return 'C-';
    elseif ($averageGrade >= 57) return 'D+';
    elseif ($averageGrade >= 53) return 'D';
    elseif ($averageGrade >= 50) return 'D-';
    else return 'F';
}

// Get search parameter from the query string
$searchQuery = isset($_GET['search']) ? $_GET['search'] : '';

// Prepare SQL to fetch student data with their grades
$query = "SELECT n.student_id, n.student_name, c.id AS course_row_id, c.course_code, 
                 c.grade1, c.grade2, c.grade3, c.grade4
          FROM name_table n
          LEFT JOIN course_table c ON n.student_id = c.student_id
          WHERE 1=1";

// Add search condition if search query is provided
if (!empty($searchQuery)) {
    $query .= " AND (n.student_id LIKE :search OR n.student_name LIKE :search OR c.course_code LIKE :search)";
}

// Order the results by student ID
$query .= " ORDER BY n.student_id ASC";

// Prepare and execute the query
$stmt = $pdo->prepare($query);

if (!empty($searchQuery)) {
    $searchParam = "%$searchQuery%";
    $stmt->bindParam(':search', $searchParam);
}

$stmt->execute();
$studentsData = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Grades System - Dashboard</title>
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
            <div class="col-md-6">
                <h2>Student Grade Records</h2>
            </div>
            <div class="col-md-6">
                <form action="" method="GET" class="d-flex">
                    <input type="text" name="search" class="form-control me-2" placeholder="Search by ID, name, or course" value="<?php echo htmlspecialchars($searchQuery); ?>">
                    <button type="submit" class="btn btn-primary">Search</button>
                    <?php if (!empty($searchQuery)): ?>
                        <a href="dashboard.php" class="btn btn-secondary ms-2">Clear</a>
                    <?php endif; ?>
                </form>
            </div>
        </div>

        <div class="card shadow">
            <div class="card-body">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>Student ID</th>
                            <th>Name</th>
                            <th>Course</th>
                            <th>Grade 1</th>
                            <th>Grade 2</th>
                            <th>Grade 3</th>
                            <th>Grade 4</th>
                            <th>Final Grade</th>
                            <th>Letter Grade</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $previousStudentId = null;
                        foreach ($studentsData as $student):
                            // Calculate the final grade
                            $finalGrade = calculateAverageGrade($student['grade1'], $student['grade2'], $student['grade3'], $student['grade4']);
                            $letterGrade = determineLetterGrade($finalGrade);

                            // Track whether this is a new student
                            $isNewStudent = $previousStudentId !== $student['student_id'];
                            $previousStudentId = $student['student_id'];
                        ?>
                        <tr>
                            <td><?php echo htmlspecialchars($student['student_id']); ?></td>
                            <td><?php echo htmlspecialchars($student['student_name']); ?></td>
                            <td><?php echo htmlspecialchars($student['course_code']); ?></td>
                            <td><?php echo htmlspecialchars($student['grade1']); ?></td>
                            <td><?php echo htmlspecialchars($student['grade2']); ?></td>
                            <td><?php echo htmlspecialchars($student['grade3']); ?></td>
                            <td><?php echo htmlspecialchars($student['grade4']); ?></td>
                            <td><?php echo $finalGrade; ?></td>
                            <td><?php echo $letterGrade; ?></td>
                            <td>
                                <a href="edit_grade.php?id=<?php echo $student['course_row_id']; ?>" class="btn btn-sm btn-primary">Edit</a>
                                <button type="button" class="btn btn-sm btn-danger" 
                                        onclick="confirmDeletion(<?php echo $student['course_row_id']; ?>)">Delete</button>
                            </td>
                        </tr>
                        <?php endforeach; ?>

                        <?php if (empty($studentsData)): ?>
                        <tr>
                            <td colspan="10" class="text-center">No records found.</td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        function confirmDeletion(recordId) {
            if (confirm('Are you sure you want to delete this grade record?')) {
                window.location.href = 'delete_grade.php?id=' + recordId;
            }
        }
    </script>
</body>
</html>

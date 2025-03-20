<?php
// Start the session to access session variables
session_start();

// Check if the user is logged in; if not, redirect them to the login page
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;  // Exit to prevent further code execution
}

// Include the database connection
require_once 'db_connect.php';

// Ensure an ID is provided in the query string
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: dashboard.php");  // Redirect if no ID is provided
    exit;
}

$recordId = $_GET['id'];  // Store the provided ID for later use

// Attempt to delete the record from the course_table
try {
    // Prepare and execute the SQL statement to delete the record
    $deleteQuery = $pdo->prepare("DELETE FROM course_table WHERE id = ?");
    $deleteQuery->execute([$recordId]);

    // Set a success message to indicate the record was deleted
    $_SESSION['success_message'] = "The grade record was deleted successfully!";
} catch (PDOException $e) {
    // If an error occurs, set an error message
    $_SESSION['error_message'] = "There was an issue deleting the record: " . $e->getMessage();
}

// Redirect the user back to the dashboard page
header("Location: dashboard.php");
exit;  // Exit to stop any further code execution
?>

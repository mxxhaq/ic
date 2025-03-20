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

$gradeRecordId = $_GET['id'];  // Store the grade record ID for later use

// Attempt to delete the grade record
try {
    $deleteStmt = $pdo->prepare("DELETE FROM course_table WHERE id = ?");
    $deleteStmt->execute([$gradeRecordId]);
    
    // Set a success message if the deletion is successful
    $_SESSION['success_message'] = "Grade record successfully deleted!";
} catch (PDOException $e) {
    // If an error occurs, set an error message
    $_SESSION['error_message'] = "Error deleting grade record: " . $e->getMessage();
}

// Redirect the user back to the dashboard
header("Location: dashboard.php");
exit;
?>


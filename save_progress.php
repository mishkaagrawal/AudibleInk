<?php
session_start();
include 'config.php';

if (!isset($_SESSION['user_id'])) {
    die("Unauthorized access.");
}

$user_name = $_SESSION['user_name'];  // Assuming you store the username in session
$book_id = $_POST['book_id'];
$time_spent = $_POST['time_spent'];
$completed = $_POST['completed_book_status'];

// Check if the user already has progress on this book
$sql_check = "SELECT * FROM user_activity WHERE user_name = ? AND book_id = ?";
$stmt_check = $conn->prepare($sql_check);
$stmt_check->bind_param("si", $user_name, $book_id);
$stmt_check->execute();
$result = $stmt_check->get_result();

if ($result->num_rows > 0) {
    // Update existing progress
    $sql_update = "UPDATE user_activity SET time_spent = time_spent + ?, completed_book_status = ?, activity_timestamp = NOW() WHERE user_name = ? AND book_id = ?";
    $stmt_update = $conn->prepare($sql_update);
    $stmt_update->bind_param("issi", $time_spent, $completed, $user_name, $book_id);
    $stmt_update->execute();
    echo "Progress updated successfully.";
} else {
    // Insert new progress record
    $sql_insert = "INSERT INTO user_activity (user_name, book_id, time_spent, completed_book_status, activity_timestamp) VALUES (?, ?, ?, ?, NOW())";
    $stmt_insert = $conn->prepare($sql_insert);
    $stmt_insert->bind_param("siss", $user_name, $book_id, $time_spent, $completed);
    $stmt_insert->execute();
    echo "Progress saved successfully.";
}
?>

<?php
session_start();
include 'config.php';

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch completed books and time spent
$query = "SELECT COUNT(*) AS books_completed, IFNULL(SUM(time_spent), 0) AS total_time_spent, IFNULL(SUM(reward_points), 0) AS total_points 
          FROM user_activity WHERE user_id = ? AND completed_book_status = 'completed'";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$progress = $result->fetch_assoc();
$stmt->close();

// Fetch books uploaded by user
$query = "SELECT COUNT(*) AS books_uploaded FROM books WHERE uploaded_by = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$upload_data = $result->fetch_assoc();
$stmt->close();

// Default values if no data exists
$books_completed = $progress['books_completed'] ?? 0;
$total_time_spent = $progress['total_time_spent'] ?? 0;
$books_uploaded = $upload_data['books_uploaded'] ?? 0;
$total_points = $progress['total_points'] ?? 0;

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Progress | AudibleInk</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap');

        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(to right, #ffdde1, #ee9ca7);
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .container {
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
            width: 400px;
            text-align: center;
        }
        h1 {
            font-size: 26px;
            font-weight: 700;
            color: #d72638;
        }
        .stats {
            font-size: 18px;
            margin: 20px 0;
            padding: 15px;
            background: #ffdde1;
            border-radius: 10px;
        }
        .points {
            font-size: 22px;
            font-weight: bold;
            color: #d72638;
        }
        button {
            padding: 12px 20px;
            border: none;
            border-radius: 6px;
            background: #d72638;
            color: white;
            font-size: 16px;
            cursor: pointer;
            transition: 0.3s;
        }
        button:hover {
            background: #a61c2b;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>📈 My Progress</h1>
        <div class="stats">
            <p><strong>📚 Books Completed:</strong> <?php echo $books_completed; ?></p>
            <p><strong>📤 Books Uploaded:</strong> <?php echo $books_uploaded; ?></p>
            <p><strong>⏳ Total Time Spent:</strong> <?php echo round($total_time_spent, 2); ?> minutes</p>
            <p class="points">🏆 Points Earned: <?php echo $total_points; ?></p>
        </div>
        <button onclick="window.location.href='user_dashboard.php'">🔙 Back to Dashboard</button>
    </div>
</body>
</html>

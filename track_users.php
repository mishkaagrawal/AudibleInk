<?php
session_start();
include 'config.php';

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    die("Unauthorized access");
}

// Fetch all users (excluding admins) and their stats
$query = "SELECT 
            users.id, users.name, users.email, 
            COUNT(DISTINCT books.bookid) AS books_uploaded,
            COUNT(DISTINCT CASE WHEN user_activity.completed_book_status = 'completed' THEN user_activity.book_id END) AS books_completed,
            IFNULL(SUM(user_activity.time_spent), 0) AS total_time_spent,
            IFNULL(SUM(user_activity.reward_points), 0) AS total_points
          FROM users
          LEFT JOIN books ON users.id = books.uploaded_by
          LEFT JOIN user_activity ON users.id = user_activity.user_id
          WHERE users.role = 'user'
          GROUP BY users.id";

$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Track Users | Admin</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap');

        body {
            font-family: 'Poppins', sans-serif;
            margin: 0;
            padding: 0;
            background: #eef5ff;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            flex-direction: column;
        }
        .container {
            width: 90%;
            max-width: 900px;
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
            text-align: center;
        }
        h1 {
            color: #3f51b5;
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 10px;
        }
        p {
            font-size: 16px;
            color: #555;
            margin-bottom: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            padding: 12px;
            border: 1px solid #ddd;
            text-align: center;
        }
        th {
            background: #3f51b5;
            color: white;
        }
        tr:nth-child(even) {
            background: #f2f2f2;
        }
        .points {
            font-weight: bold;
            color: #d72638;
        }
        .back-button {
            background: #ffafcc;
            color: white;
            font-weight: bold;
            text-decoration: none;
            padding: 12px 20px;
            display: inline-block;
            border-radius: 8px;
            margin-top: 20px;
            transition: 0.3s ease;
        }
        .back-button:hover {
            background: #ff7698;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Track Users</h1>
        <p>Monitor user activity, uploaded books, time spent reading, and earned points.</p>
        
        <table>
            <tr>
                <th>Name</th>
                <th>Email</th>
                <th>Books Uploaded</th>
                <th>Books Completed</th>
                <th>Total Time Spent (minutes)</th>
                <th>Points</th>
            </tr>
            <?php while ($row = $result->fetch_assoc()) { ?>
            <tr>
                <td><?php echo htmlspecialchars($row['name']); ?></td>
                <td><?php echo htmlspecialchars($row['email']); ?></td>
                <td><?php echo $row['books_uploaded']; ?></td>
                <td><?php echo $row['books_completed']; ?></td>
                <td><?php echo round($row['total_time_spent'], 2); ?></td>
                <td class="points">🏆 <?php echo $row['total_points']; ?></td>
            </tr>
            <?php } ?>
        </table>

        <!-- Back to Dashboard Button -->
        <a href="admin_dashboard.php" class="back-button">🔙 Back to Dashboard</a>
    </div>
</body>
</html>

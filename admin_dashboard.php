<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | AUDIBLEINK</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap');

        body {
            font-family: 'Poppins', sans-serif;
            margin: 0;
            padding: 0;
            background: #fef6e4; /* Soft pastel peach */
            display: flex;
            height: 100vh;
        }

        .sidebar {
            width: 250px;
            background: #a0c4ff; /* Pastel blue */
            color: white;
            padding: 20px;
            height: 100vh;
            position: fixed;
            display: flex;
            flex-direction: column;
            align-items: center;
            box-shadow: 2px 0 10px rgba(0, 0, 0, 0.1);
        }

        .sidebar h2 {
            text-align: center;
            font-weight: 600;
            margin-bottom: 20px;
        }

        .sidebar a {
            display: block;
            width: 100%;
            color: white;
            text-decoration: none;
            padding: 12px;
            margin: 5px 0;
            border-radius: 8px;
            transition: 0.3s ease-in-out;
            font-weight: 500;
            text-align: center;
            background: #bdb2ff; /* Pastel purple */
        }

        .sidebar a:hover {
            background: #ffafcc; /* Pastel pink */
        }

        .content {
            margin-left: 260px;
            padding: 40px;
            flex-grow: 1;
            text-align: center;
            background: #fbc3bc; /* Pastel coral */
            border-radius: 10px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
        }

        .content h1 {
            font-size: 28px;
            color: #444;
            margin-bottom: 10px;
        }

        .content p {
            font-size: 18px;
            color: #666;
        }

        .button {
            padding: 12px 24px;
            background: #ffcad4; /* Soft pastel pink */
            color: white;
            border: none;
            cursor: pointer;
            margin-top: 20px;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            transition: 0.3s ease-in-out;
            text-decoration: none;
            display: inline-block;
        }

        .button:hover {
            background: #ff8fab; /* Pastel rose */
        }

    </style>
</head>
<body>

<div class="sidebar">
    <h2>Admin Dashboard</h2>
    <a href="upload.php">Upload Book</a>
    <a href="delete.php">Delete Book</a>
    <a href="update_book.php">Update Book</a>
    <a href="library.php">View Library</a>  <!-- ✅ NEW: View Library Option -->
    <a href="track_users.php">Track Users</a>
    <a href="logout.php">Logout</a>
</div>

<div class="content">
    <h1>Welcome, <?php echo htmlspecialchars($_SESSION['name']); ?>!</h1>
    <p>Manage books and track user activities efficiently.</p>
    <a href="library.php" class="button">📚 View Library</a> <!-- ✅ NEW: Direct Button to Library -->
</div>

</body>
</html>

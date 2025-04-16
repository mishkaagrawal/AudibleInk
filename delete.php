<?php
session_start();
include 'config.php';

// Ensure user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    die("Unauthorized access.");
}

$message = "";

// Handle book deletion
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete'])) {
    $book_id = $_POST['book_id'];

    // Check if the book exists before deleting
    $check_sql = "SELECT file_path FROM books WHERE bookid = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("i", $book_id);
    $check_stmt->execute();
    $result = $check_stmt->get_result();

    if ($result->num_rows > 0) {
        $book = $result->fetch_assoc();
        $file_path = $book['file_path']; // Get the file path

        // Delete file from the server (optional)
        if (file_exists($file_path)) {
            unlink($file_path); // Delete the file from the uploads folder
        }

        // Delete book from database
        $delete_sql = "DELETE FROM books WHERE bookid = ?";
        $delete_stmt = $conn->prepare($delete_sql);
        $delete_stmt->bind_param("i", $book_id);

        if ($delete_stmt->execute()) {
            $message = "✅ Book deleted successfully!";
        } else {
            $message = "❌ Error deleting book: " . $conn->error;
        }
        $delete_stmt->close();
    } else {
        $message = "⚠️ Book not found!";
    }
    $check_stmt->close();
}

// Fetch all books for selection
$sql = "SELECT * FROM books";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delete Book | AUDIBLEINK</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap');
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(to right, #ffb3b3, #ff9999);
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .container {
            background: rgba(255, 255, 255, 0.9);
            padding: 25px;
            border-radius: 15px;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
            width: 400px;
            text-align: center;
        }
        h2 {
            color: #d32f2f;
            font-weight: 600;
        }
        select, button {
            width: 100%;
            padding: 12px;
            margin: 8px 0;
            border: none;
            border-radius: 6px;
            font-size: 14px;
        }
        select {
            background: #ffcccc;
            outline: none;
        }
        button {
            background: #d32f2f;
            color: white;
            font-weight: 600;
            cursor: pointer;
            transition: 0.3s;
        }
        button:hover {
            background: #b71c1c;
        }
        a {
            display: block;
            margin-top: 15px;
            text-decoration: none;
            color: #d32f2f;
            font-weight: 500;
        }
        a:hover {
            color: #b71c1c;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Delete Book</h2>
        <?php if (!empty($message)) echo "<p style='color:green;'>$message</p>"; ?>
        <form method="POST">
            <select name="book_id" required>
                <option value="">Select Book</option>
                <?php while ($row = $result->fetch_assoc()) { ?>
                    <option value="<?= $row['bookid']; ?>"><?= $row['title']; ?></option>
                <?php } ?>
            </select>
            <button type="submit" name="delete">Delete Book</button>
        </form>
        <a href="admin_dashboard.php">Back to Dashboard</a>
    </div>
</body>
</html>

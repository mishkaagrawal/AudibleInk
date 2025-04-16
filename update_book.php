<?php
session_start();
include 'config.php';

// Ensure only admins can access
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    die("Unauthorized access.");
}

$message = "";

// Fetch all books for updating
$sql = "SELECT * FROM books";
$result = $conn->query($sql);

// Handle book update
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update'])) {
    $book_id = $_POST['book_id'];
    $new_title = trim($_POST['title']);
    $new_author = trim($_POST['author']);

    if (!empty($new_title) && !empty($new_author)) {
        $update_sql = "UPDATE books SET title = ?, author = ? WHERE bookid = ?";
        $stmt = $conn->prepare($update_sql);
        $stmt->bind_param("ssi", $new_title, $new_author, $book_id);

        if ($stmt->execute()) {
            $message = "✅ Book updated successfully!";
        } else {
            $message = "❌ Error updating book: " . $conn->error;
        }
        $stmt->close();
    } else {
        $message = "⚠️ Title and Author cannot be empty!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Book | AUDIBLEINK</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap');
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(to right, #b2f7ef, #a6e3e9);
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
            color: #036666;
            margin-bottom: 15px;
            font-weight: 600;
        }
        select, input {
            width: 100%;
            padding: 12px;
            margin: 8px 0;
            border: none;
            border-radius: 6px;
            background: #dffcf4;
            outline: none;
            font-size: 14px;
        }
        button {
            width: 100%;
            padding: 12px;
            border: none;
            border-radius: 6px;
            background: #36c9c6;
            color: white;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: 0.3s;
        }
        button:hover {
            background: #1ea4a0;
        }
        a {
            display: block;
            margin-top: 15px;
            text-decoration: none;
            color: #036666;
            font-weight: 500;
        }
        a:hover {
            color: #1ea4a0;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Update Book</h2>
        <?php if (!empty($message)) echo "<p style='color:green;'>$message</p>"; ?>
        <form method="POST">
            <select name="book_id" required>
                <option value="">Select Book</option>
                <?php while ($row = $result->fetch_assoc()) { ?>
                    <option value="<?= $row['bookid']; ?>"><?= $row['title']; ?></option>
                <?php } ?>
            </select>
            <input type="text" name="title" placeholder="New Title" required>
            <input type="text" name="author" placeholder="New Author" required>
            <button type="submit" name="update">Update Book</button>
        </form>
        <a href="admin_dashboard.php">Back to Dashboard</a>
    </div>
</body>
</html>

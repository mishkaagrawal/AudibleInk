<?php
session_start();
include 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_FILES["book"])) {
    $book_name = $_POST['book_name'];
    $author_name = $_POST['author_name']; 
    $book_file = $_FILES["book"]["name"];
    $book_tmp = $_FILES["book"]["tmp_name"];
    $upload_dir = "uploads/"; 

    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }

    // Extract book name + extension only
    $final_file_name = basename($book_file); 

    $file_path = $final_file_name; 

    if (move_uploaded_file($book_tmp, $file_path)) {
        $uploaded_by = $_SESSION['user_id']; 

        // Ensure the user exists in 'users' table
        $user_check = $conn->prepare("SELECT id FROM users WHERE id = ?");
        $user_check->bind_param("i", $uploaded_by);
        $user_check->execute();
        $user_check->store_result();

        if ($user_check->num_rows > 0) {
            // Save only the file name (not the full path)
            $stmt = $conn->prepare("INSERT INTO books (uploaded_by, title, author, file_path) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("isss", $uploaded_by, $book_name, $author_name, $final_file_name);

            if ($stmt->execute()) {
                $message = "📚 Book uploaded successfully!";
            } else {
                $message = "❌ Error: " . $conn->error;
            }
            $stmt->close();
        } else {
            $message = "⚠️ Error: The user does not exist.";
        }
        $user_check->close();
    } else {
        $message = "⚠️ Failed to upload book.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Upload Book | AUDIBLEINK</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap');
        
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(to right, #a6e3e9, #b2f7ef);
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            flex-direction: column;
        }
        .container {
            background: white;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
            text-align: center;
            width: 350px;
        }
        input, button {
            width: 100%;
            padding: 12px;
            margin: 8px 0;
            border: none;
            border-radius: 6px;
            font-size: 14px;
        }
        input {
            background: #dffcf4;
        }
        button {
            background: #36c9c6;
            color: white;
            font-weight: bold;
            cursor: pointer;
            transition: 0.3s;
        }
        button:hover {
            background: #1ea4a0;
        }
        .message {
            color: green;
            font-size: 14px;
        }
        .back-button {
            background: #ffafcc;
            color: white;
            font-weight: bold;
            text-decoration: none;
            padding: 12px 20px;
            display: inline-block;
            border-radius: 8px;
            margin-top: 15px;
            transition: 0.3s ease;
        }
        .back-button:hover {
            background: #ff7698;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>📤 Upload a Book</h2>
        <?php if (!empty($message)) echo "<p class='message'>$message</p>"; ?>
        <form method="POST" enctype="multipart/form-data">
            <input type="text" name="book_name" placeholder="Enter Book Name" required>
            <input type="text" name="author_name" placeholder="Enter Author Name" required>
            <input type="file" name="book" accept=".pdf,.txt" required>
            <button type="submit">Upload</button>
        </form>

        <!-- Back to Dashboard Button -->
        <a href="<?php echo ($_SESSION['role'] === 'admin') ? 'admin_dashboard.php' : 'user_dashboard.php'; ?>" class="back-button">🔙 Back to Dashboard</a>
    </div>
</body>
</html>

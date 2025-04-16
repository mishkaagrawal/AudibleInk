<?php
session_start();

// Database Connection
$servername = "localhost"; 
$username = "root"; 
$password = ""; 
$database = "sepm"; 

$conn = new mysqli($servername, $username, $password, $database);

// Check Connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Redirect to login if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Check if user is admin or normal user
$is_admin = ($_SESSION['role'] === 'admin');
$dashboard_link = $is_admin ? "admin_dashboard.php" : "user_dashboard.php";

// Fetch all books
$sql = "SELECT books.bookid, books.title, books.author, books.file_path, books.uploaded_by, books.uploaded_at, users.name AS uploader_name 
        FROM books 
        JOIN users ON books.uploaded_by = users.id
        ORDER BY books.uploaded_at DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Library | AUDIBLEINK</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap');

        body {
            font-family: 'Poppins', sans-serif;
            margin: 0;
            padding: 20px;
            background: #fefae0;
            text-align: center;
        }

        h1 {
            color: #444;
            margin-bottom: 20px;
        }

        table {
            width: 90%;
            margin: auto;
            border-collapse: collapse;
            background: #ffffff;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
            overflow: hidden;
        }

        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        th {
            background: #90dbf4;
            color: white;
        }

        tr:nth-child(even) {
            background: #f9f9f9;
        }

        tr:hover {
            background: #ffd6a5;
        }

        .button {
            padding: 8px 16px;
            background: #ffafcc;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            transition: 0.3s;
            display: inline-block;
            margin: 5px;
        }

        .button:hover {
            background: #ff8fab;
        }

    </style>
</head>
<body>

<h1>📚 Library - Uploaded Books</h1>

<table>
    <tr>
        <th>Title</th>
        <th>Author</th>
        <th>Uploaded By</th>
        <th>Upload Date</th>
        <th>Action</th>
    </tr>
    <?php 
    if ($result->num_rows > 0): 
        while ($row = $result->fetch_assoc()): 
    ?>
    <tr>
        <td><?php echo htmlspecialchars($row['title']); ?></td>
        <td><?php echo htmlspecialchars($row['author']); ?></td>
        <td><?php echo htmlspecialchars($row['uploader_name']); ?></td>
        <td><?php echo htmlspecialchars($row['uploaded_at']); ?></td>
        <td>
            <a href="view_document.php?file=<?php echo urlencode($row['file_path']); ?>" class="button">📖 View</a>
        </td>
    </tr>
    <?php 
        endwhile; 
    else: 
    ?>
    <tr>
        <td colspan="5">No books uploaded yet.</td>
    </tr>
    <?php endif; ?>
</table>

<a href="<?php echo $dashboard_link; ?>" class="button">🔙 Return to Dashboard</a>

</body>
</html>

<?php $conn->close(); ?>

<?php
session_start();
include 'config.php';

$email = "";
$show_reset_fields = false;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['verify_email'])) {
        // User enters email to verify
        $email = trim($_POST['email']);

        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $show_reset_fields = true; // Show password reset fields
        } else {
            $error = "No account found with this email.";
        }
        $stmt->close();
    } elseif (isset($_POST['reset_password'])) {
        // User submits new password
        $email = trim($_POST['email']);
        $new_password = $_POST['password'];
        $confirm_password = $_POST['confirm_password'];

        if (empty($email)) {
            $error = "Email verification failed. Try again.";
        } elseif ($new_password !== $confirm_password) {
            $error = "Passwords do not match.";
            $show_reset_fields = true;
        } else {
            // Hash new password and update database
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $update_stmt = $conn->prepare("UPDATE users SET password = ? WHERE email = ?");
            $update_stmt->bind_param("ss", $hashed_password, $email);

            if ($update_stmt->execute()) {
                $success = "Password reset successful! <a href='login.php'>Login here</a>";
            } else {
                $error = "Something went wrong. Try again.";
            }
            $update_stmt->close();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Forgot Password - AudibleInk</title>
    <style>
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
        }

        input {
            width: 100%;
            padding: 12px;
            margin: 8px 0;
            border: none;
            border-radius: 6px;
            background: #dffcf4;
            outline: none;
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
    </style>
</head>
<body>
    <div class="container">
        <h2>Forgot Password</h2>
        <?php if(isset($success)) echo "<p style='color:green;'>$success</p>"; ?>
        <?php if(isset($error)) echo "<p style='color:red;'>$error</p>"; ?>

        <?php if (!$show_reset_fields): ?>
            <!-- Ask for email -->
            <form method="POST">
                <input type="email" name="email" placeholder="Enter Your Email" required>
                <button type="submit" name="verify_email">Verify Email</button>
            </form>
        <?php else: ?>
            <!-- Show password reset fields if email exists -->
            <form method="POST">
                <input type="hidden" name="email" value="<?php echo htmlspecialchars($email); ?>">
                <input type="password" name="password" placeholder="Enter New Password" required>
                <input type="password" name="confirm_password" placeholder="Confirm New Password" required>
                <button type="submit" name="reset_password">Reset Password</button>
            </form>
        <?php endif; ?>
        
        <a href="login.php">Back to Login</a>
    </div>
</body>
</html>

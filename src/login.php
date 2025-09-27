<?php
session_start();
include 'db_connect.php';

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);

    if (!empty($email)) {
        // Check if user exists
        $stmt = $conn->prepare("SELECT id, name, role FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result && $result->num_rows === 1) {
            $user = $result->fetch_assoc();

            // Save user session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['name'] = $user['name'];
            $_SESSION['role'] = $user['role'];

            header("Location: index.php"); // Redirect to homepage
            exit;
        } else {
            $message = "Invalid email. Try again.";
        }
        $stmt->close();
    } else {
        $message = "Please enter your email.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Login - Library System</title>
</head>

<body>
    <h1>🔑 Library Login</h1>

    <?php if ($message): ?>
        <p style="color:red;"><?php echo $message; ?></p>
    <?php endif; ?>

    <form method="POST" action="login.php">
        <label>Email:</label><br>
        <input type="email" name="email" required><br><br>
        <button type="submit">Login</button>
    </form>

    <p>Try with:</p>
    <ul>
        <li>Librarian → <code>librarian@library.com</code></li>
        <li>User → <code>user@example.com</code></li>
    </ul>
</body>

</html>
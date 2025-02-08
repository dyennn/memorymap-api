<?php
    require_once '../config.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
</head>
<body>
    <form id="loginForm" method="POST" action="../api/login.php"></form>
        <label for="tEmail">Email</label>
        <input type="email" name="email" id="tEmail" required>
        <label for="tPassword">Password</label>
        <input type="password" name="password" id="tPassword" required>
        <button type="submit">Login</button>
    </form>

    <?php
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $email = $_POST['email'];
        $password = $_POST['password'];

        if ($loginSuccessful) {
            // Redirect to dashboard
            header('Location: dashboard.php');
            exit();
        } else {
            // Display error message
            echo '<script>alert("Invalid email or password.");</script>';
        }
    }
    ?>
</body>
</html>
</body>
</html>
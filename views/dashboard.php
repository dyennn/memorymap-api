<?php
require_once '../config.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
</head>
<body>
    <script>
        // Check if token exists in local storage
        const token = localStorage.getItem('token');
        if (!token) {
            // Redirect to login page if token is not found
            window.location.href = 'login.php';
        }
    </script>
        <h1>Dashboard</h1>
</body>
</html>
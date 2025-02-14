<?php
require_once '../config.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="styles/styles.css">
    <title>Dashboard</title>
</head>
<body>
    <!-- Authentication -->
    <script src="../js/auth.js"></script>

    <!-- Nav bar -->
    <?php include 'components/navigation.php';?>

    <script> document.querySelector('a[href="dashboard.php"]').classList.add('active'); </script>
        <h1>Dashboard</h1>
</body>
</html>
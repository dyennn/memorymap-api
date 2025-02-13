<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="styles/styles.css">
    <title>Map</title>
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
    <?php
    include 'components/navigation.php';
    ?>
    <script>
        document.querySelector('a[href="map.php"]').classList.add('active');
    </script>
    <h1>Map</h1>
</body>
</html>
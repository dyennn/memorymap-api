<?php
require_once '../config.php'; // Database credentials
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="styles/login-register.css">
    <title>Document</title>
</head>
<body>
    
    
<div class="logo-container">
        <img src="Group 226.png">
        <p class="MemoryMap"> MemoryMap</p>

        <div class="form-container">

        <h4>Create account</h4>

        <form id="registerForm">
            <label for="name">Name</label><br>
            <input type="text" name="name" id="name" required style="width: 100%"><br>
            <label for="email">Email</label><br>
            <input type="email" name="email" id="email" required style="width: 100%"><br>
            <label for="password">Password</label><br>
            <input type="password" name="password" id="password" required style="width: 100%"><br>
            <label for="confirmPassword">Confirm Password</label><br>
            <input type="password" name="confirmPassword" id="confirmPassword" required style="width: 100%"><br>
            
            <button type="submit">Register</button>
            <p id="goTologin" onclick="location.href='http://localhost/memorymap/views/login.php'">Go to Login</p>
        </form>
    </div>
    </div>
    <script>
        document.getElementById('registerForm').addEventListener('submit', async function(event) {
            event.preventDefault();

            const name = document.getElementById('name').value;
            const email = document.getElementById('email').value;
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirmPassword').value;

            const response = await fetch('../api/register.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ name, email, password, confirmPassword })
            });

            const result = await response.json();

            if (response.ok) {
                // Registration successful, redirect to login
                window.location.href = 'login.php';
            } else {
                // Display error message
                alert(result.message);
            }
        });
    </script>
</body>
</html>

<script>
    document.getElementById('registerForm').addEventListener('submit', async function(event) {
        event.preventDefault();

        const name = document.getElementById('name').value;
        const email = document.getElementById('email').value;
        const password = document.getElementById('password').value;
        const confirmPassword = document.getElementById('confirmPassword').value;

        const response = await fetch('../api/register.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ name, email, password, confirmPassword })
        });

        const result = await response.json();

        if (response.ok) {
            // Registration successful, redirect to login
            window.location.href = 'login.php';
        } else {
            // Display error message
            alert(result.message);
        }
    });
</script>
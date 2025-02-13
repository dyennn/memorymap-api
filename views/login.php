<?php
require_once '../config.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="styles/login-register.css">
    <title>Login</title>
</head>
<body>
<h4>Create account</h4>

<div class="logo-container">
        <img src="../res/Group 226.png">
        <p class="MemoryMap"> MemoryMap</p>

        <div class="form-container">

<form id="LoginForm">
    <label for="email">Email</label><br>
    <input type="email" name="email" id="email" required style="width: 100%"><br>
    <label for="password">Password</label><br>
    <input type="password" name="password" id="password" required style="width: 100%"><br>
    <br>
    <div class="button-container">
        <button type="submit">Register</button>
    </div>
    <p id="register" onclick="location.href='http://localhost/memorymap/views/Register.php'">Register Account</p>
</form>
</div>
</div>  

    <script>
        const form = document.getElementById('loginForm');

        form.addEventListener('submit', (event) => {
            event.preventDefault(); // Prevent default form submission

            const email = document.getElementById('tEmail').value;
            const password = document.getElementById('tPassword').value;

            fetch('../api/login.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ email, password })
            })
            .then(response => {
                if (!response.ok) {
                    return response.json().then(err => {throw new Error(err.message)}); // Throw error with message from the API
                }
                return response.json();
            })
            .then(data => {
                if (data.status === 'Success') {
                    // Store the token and caregiver_id in local storage or a cookie
                    localStorage.setItem('token', data.token);
                    localStorage.setItem('caregiver_id', data.caregiver_id);

                    // Redirect to dashboard
                    window.location.href = 'dashboard.php'; // Or wherever your dashboard is
                } else {
                    alert(data.message); // Display error message from API
                    if (data.hint) { // Display hints, if any.
                        let hints = "";
                        for (const key in data.hint) {
                            hints += data.hint[key] + "\n";
                        }
                        alert(hints);
                    }
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert(error.message); // Display error message to the user
            });
        });
    </script>
</body>
</html>
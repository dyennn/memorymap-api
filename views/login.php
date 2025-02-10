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
    <form id="loginForm">
        <label for="tEmail">Email</label>
        <input type="email" name="email" id="tEmail" required>
        <label for="tPassword">Password</label>
        <input type="password" name="password" id="tPassword" required>
        <button type="submit">Login</button>
        <a href="forgot_password.php">Forgot password?</a>
    </form>

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
<?php
require_once '../config.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password</title>
</head>
<body>
    <form id="forgotPasswordForm">
        <label for="tEmail">Email</label>
        <input type="email" name="email" id="tEmail" required>
        <button type="submit">Send Code</button>
    </form>

    <form id="verifyCodeForm" style="display:none;">
        <label for="tCode">Code</label>
        <input type="text" name="code" id="tCode" required>
        <button type="submit" id="verifyCodeButton" disabled>Verify Code</button>
    </form>

    <button onclick="window.location.href='login.php'">Back to Login</button>

    <script>
        const forgotPasswordForm = document.getElementById('forgotPasswordForm');
        const verifyCodeForm = document.getElementById('verifyCodeForm');
        const verifyCodeButton = document.getElementById('verifyCodeButton');

        forgotPasswordForm.addEventListener('submit', (event) => {
            event.preventDefault(); // Prevent default form submission

            const email = document.getElementById('tEmail').value;

            fetch('../api/send_reset_code.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ email })
            })
            .then(response => {
                if (!response.ok) {
                    return response.json().then(err => {throw new Error(err.message)}); // Throw error with message from the API
                }
                return response.json();
            })
            .then(data => {
                if (data.status === 'Success') {
                    alert(data.message); // Display success message from API
                    forgotPasswordForm.style.display = 'none';
                    verifyCodeForm.style.display = 'block';
                    verifyCodeButton.disabled = false;
                } else {
                    alert(data.message); // Display error message from API
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert("An error occurred: " + error.message); // Display error message to the user
            });
        });

        verifyCodeForm.addEventListener('submit', (event) => {
            event.preventDefault(); // Prevent default form submission

            const email = document.getElementById('tEmail').value;
            const code = document.getElementById('tCode').value;

            fetch('../api/verify_reset_code.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ email, code })
            })
            .then(response => {
                if (!response.ok) {
                    return response.json().then(err => {throw new Error(err.message)}); // Throw error with message from the API
                }
                return response.json();
            })
            .then(data => {
                if (data.status === 'Success') {
                    alert(data.message); // Display success message from API
                    window.location.href = `reset_password.php?email=${encodeURIComponent(email)}&code=${encodeURIComponent(code)}`; // Redirect to reset_password.php with email and code
                } else {
                    alert(data.message); // Display error message from API
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert("An error occurred: " + error.message); // Display error message to the user
            });
        });
    </script>
</body>
</html>
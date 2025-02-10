<?php
require_once '../config.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password</title>
</head>
<body>
    <form id="resetPasswordForm">
        <input type="hidden" name="email" id="tEmail" value="<?php echo htmlspecialchars($_GET['email']); ?>" required>
        <input type="hidden" name="code" id="tCode" value="<?php echo htmlspecialchars($_GET['code']); ?>" required>

        <label for="tPassword">New Password</label>
        <input type="password" name="password" id="tPassword" required>

        <label for="tConfirmPassword">Confirm New Password</label>
        <input type="password" name="confirmPassword" id="tConfirmPassword" required>

        <button type="submit">Reset Password</button>
    </form>

    <button onclick="window.location.href='login.php'">Back to Login</button>

    <script>
        const resetPasswordForm = document.getElementById('resetPasswordForm');

        resetPasswordForm.addEventListener('submit', (event) => {
            event.preventDefault(); // Prevent default form submission

            const email = document.getElementById('tEmail').value;
            const code = document.getElementById('tCode').value;
            const password = document.getElementById('tPassword').value;
            const confirmPassword = document.getElementById('tConfirmPassword').value;

            fetch('../api/reset_password.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ email, code, password, confirmPassword })
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
                    window.location.href = 'login.php'; // Redirect to login page
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
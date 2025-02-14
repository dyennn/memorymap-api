<?php
require_once '../config.php';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="styles/styles.css">
    <title>Login</title>
</head>

<body>
<div class="main-container">
    <div class="logo-container">
        <img src="../res/Group 226.png" alt="MemoryMap Logo">
        <h1>MemoryMap</h1>
    </div>

    <div class="right-container">
        <div class="form-container">
            <h1>Login</h1><br><br>
            <form id="loginForm">
                <div class="input-container">
                    <label for="tEmail">Email</label>
                    <div class="input-button-group">
                        <input type="email" name="email" id="tEmail" required>
                    </div>
                    <label for="tPassword">Password</label>
                    <div class="input-button-group">
                        <input type="password" name="password" id="tPassword" required>
                    </div>
                    <a href="forgot_password.php" class="to-forgot-password" style="float: left;">Forgot Password?</a>
                </div>
                <button type="submit" class="back-to-login">Login</button> <br><br><br>
                <div id="loadingSpinner" class="loading-spinner" style="display: none; margin: 0 auto;"></div>
            </form> 
        
            <a href="register.php" class="to-register-login">Register</a>
        </div>
    </div>
</div>

<!-- Toast Container -->
<div id="toastContainer" class="toast-container"></div>

<script>
    const form = document.getElementById('loginForm');
    const loadingSpinner = document.getElementById('loadingSpinner');
    const toastContainer = document.getElementById('toastContainer');

    function showLoadingSpinner() {
        loadingSpinner.style.display = 'block';
    }

    function hideLoadingSpinner() {
        loadingSpinner.style.display = 'none';
    }

    function showToast(message) {
        const toast = document.createElement('div');
        toast.className = 'toast';
        toast.innerText = message;
        toastContainer.appendChild(toast);
        setTimeout(() => {
            toast.remove();
        }, 3000);
    }

    form.addEventListener('submit', (event) => {
        event.preventDefault(); // Prevent default form submission
        showLoadingSpinner();

        const email = document.getElementById('tEmail').value;
        const password = document.getElementById('tPassword').value;

        fetch('../api/login.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                email,
                password
            })
        })
        .then(response => {
            hideLoadingSpinner();
            if (!response.ok) {
                return response.json().then(err => {
                    throw new Error(err.message)
                }); // Throw error with message from the API
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
                showToast(data.message); // Display error message from API
                if (data.hint) { // Display hints, if any.
                    let hints = "";
                    for (const key in data.hint) {
                        hints += data.hint[key] + "\n";
                    }
                    showToast(hints);
                }
            }
        })
        .catch(error => {
            hideLoadingSpinner();
            console.error('Error:', error);
            showToast(error.message); // Display error message to the user
        });
    });
</script>
</body>

</html>
<?php
require_once '../config.php'; // Database credentials
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="styles/styles.css">
    <title>Register</title>
</head>

<body>
    <div class="main-container">
        <div class="logo-container">
            <img src="../res/Group 226.png" alt="MemoryMap Logo">
            <h1>MemoryMap</h1>
        </div>

        <div class="right-container">
            <div class="form-container">
                <h1>Register</h1><br>
                <form id="registerForm">
                    <div class="input-container">
                        <label for="name">Name</label>
                        <div class="input-button-group">
                            <input type="text" name="name" id="name" placeholder="Enter your name..." required selected>
                        </div>
                        <label for="tEmail">Email</label>
                        <div class="input-button-group">
                            <input type="email" name="email" id="tEmail" placeholder="Ex. email@example.com" required>
                        </div>
                        <label for="tPassword">Password</label>
                        <div class="input-button-group">
                            <input type="password" name="password" id="tPassword" placeholder="Enter your password..." required>
                            <button type="button" class="toggle-password" onclick="togglePasswordVisibility('tPassword')">
                                <img src="../res/show.svg" alt="Show" id="togglePasswordIcon_tPassword">
                            </button>
                        </div>
                        <label for="confirmPassword">Confirm Password</label>
                        <div class="input-button-group">
                            <input type="password" name="confirmPassword" id="confirmPassword" placeholder="Re-enter your password..." required>
                            <button type="button" class="toggle-password" onclick="togglePasswordVisibility('confirmPassword')">
                                <img src="../res/show.svg" alt="Show" id="togglePasswordIcon_confirmPassword">
                            </button>
                        </div>
                    </div>
                    <button type="submit" class="back-to-login" style="margin: 10px;">Register</button> <br><br>
                    <div id="loadingSpinner" class="loading-spinner" style="display: none; margin: 0 auto;"></div>
                </form>

                <a href="login.php" class="to-register-login">Login</a>
            </div>
        </div>
    </div>

    <!-- Toast Container -->
    <div id="toastContainer" class="toast-container"></div>

    <script>
        const form = document.getElementById('registerForm');
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

        function togglePasswordVisibility(id) {
            const input = document.getElementById(id);
            const icon = document.getElementById(`togglePasswordIcon_${id}`);
            if (input.type === 'password') {
                input.type = 'text';
                icon.src = '../res/hide.svg';
                icon.alt = 'Hide';
            } else {
                input.type = 'password';
                icon.src = '../res/show.svg';
                icon.alt = 'Show';
            }
        }

        form.addEventListener('submit', async function(event) {
            event.preventDefault();
            showLoadingSpinner();

            const name = document.getElementById('name').value;
            const email = document.getElementById('tEmail').value;
            const password = document.getElementById('tPassword').value;
            const confirmPassword = document.getElementById('confirmPassword').value;

            const response = await fetch('../api/register.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    name,
                    email,
                    password,
                    confirmPassword
                })
            });

            const result = await response.json();
            hideLoadingSpinner();

            if (response.ok) {
                // Registration successful, show toast and redirect to login
                showToast('Verification email sent to ' + email);
                setTimeout(() => {
                    window.location.href = 'login.php';
                }, 3000); // Redirect after 3 seconds
            } else {
                // Display error message
                showToast(result.message);
            }
        });
    </script>
</body>

</html>
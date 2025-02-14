<?php
require_once '../config.php';

/**
 * Forgot Password Page
 *
 * This page allows users to request a password reset by entering their registered email address.
 * It includes a form to send a reset code to the user's email and another form to verify the code.
 * Upon successful verification, the user is redirected to the reset password page.
 *
 * @file forgot_password.php
 *
 * @requires ../config.php
 * @requires ../api/send_reset_code.php
 * @requires ../api/verify_reset_code.php
 *
 * @section HTML
 * - Main container with logo and form sections
 * - Form to request a reset code
 * - Form to verify the reset code
 * - Back to login button
 * - Toast container for displaying messages
 *
 * @section JavaScript
 * - Event listeners for form submissions and resend code button
 * - Functions to show/hide loading spinner and display toast messages
 * - Fetch API calls to send and verify reset codes
 *
 * @section CSS
 * - Linked stylesheet: styles/forgot_password.css
 *
 * @section Images
 * - Logo image: ../res/Group 226.png
 */
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password</title>
    <link rel="stylesheet" href="styles/styles.css">
</head>
<body>
<div class="main-container">
    <div class="logo-container">
        <img src="../res/Group 226.png" alt="MemoryMap Logo">
        <h1>MemoryMap</h1>
    </div>

    <div class="right-container">
        <div class="form-container">
            <h1>Forgot Password</h1>
            <p>Please enter your registered email address.<br> We will get back to you with the reset password link and confirmation OTP.</p>
            <form id="forgotPasswordForm">
                <div class="input-container">
                    <label for="tEmail">Email</label>
                    <div class="input-button-group">
                        <input type="email" name="email" id="tEmail" required>
                        <button type="submit">
                            <span>Send Code</span>
                        </button>
                        <div id="loadingSpinner" class="loading-spinner" style="display: none;"></div>
                    </div>
                </div>
            </form>
        
            <form id="verifyCodeForm" style="display:none;">
                <div class="input-container">
                    <label for="tCode">Code</label>
                    <div class="input-button-group">
                        <input type="text" name="code" id="tCode" required>
                        <button type="submit" id="verifyCodeButton" disabled>Verify Code</button>
                    </div>
                    <button type="button" id="resendCodeButton" disabled>Resend Code</button>
                </div>
            </form>
        
            <button class="back-to-login" onclick="window.location.href='login.php'">Back to Login</button>
        </div>
    </div>
</div>

<!-- Toast Container -->
<div id="toastContainer" class="toast-container"></div>

<script>
    const forgotPasswordForm = document.getElementById('forgotPasswordForm');
    const verifyCodeForm = document.getElementById('verifyCodeForm');
    const verifyCodeButton = document.getElementById('verifyCodeButton');
    const resendCodeButton = document.getElementById('resendCodeButton');
    const loadingSpinner = document.getElementById('loadingSpinner');
    const toastContainer = document.getElementById('toastContainer');

    let canResendCode = true;

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

    forgotPasswordForm.addEventListener('submit', (event) => {
        event.preventDefault(); // Prevent default form submission
        showLoadingSpinner();

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
            hideLoadingSpinner();
            showToast(data.message); // Display message using toast
            if (data.status === 'Success') {
                forgotPasswordForm.style.display = 'none';
                verifyCodeForm.style.display = 'block';
                verifyCodeButton.disabled = false;
                resendCodeButton.disabled = false;
            }
        })
        .catch(error => {
            hideLoadingSpinner();
            console.error('Error:', error);
            showToast("An error occurred: " + error.message); // Display error message using toast
        });
    });

    verifyCodeForm.addEventListener('submit', (event) => {
        event.preventDefault(); // Prevent default form submission
        showLoadingSpinner();

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
            hideLoadingSpinner();
            if (!response.ok) {
                return response.json().then(err => {throw new Error(err.message)}); // Throw error with message from the API
            }
            return response.json();
        })
        .then(data => {
            showToast(data.message); // Display message using toast
            if (data.status === 'Success') {
                window.location.href = `reset_password.php?email=${encodeURIComponent(email)}&code=${encodeURIComponent(code)}`; // Redirect to reset_password.php with email and code
            }
        })
        .catch(error => {
            hideLoadingSpinner();
            console.error('Error:', error);
            showToast("An error occurred: " + error.message); // Display error message using toast
        });
    });

    resendCodeButton.addEventListener('click', () => {
        if (!canResendCode) return;
        showLoadingSpinner();

        const email = document.getElementById('tEmail').value;

        fetch('../api/send_reset_code.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ email })
        })
        .then(response => {
            hideLoadingSpinner();
            if (!response.ok) {
                return response.json().then(err => {throw new Error(err.message)}); // Throw error with message from the API
            }
            return response.json();
        })
        .then(data => {
            hideLoadingSpinner();
            showToast(data.message); // Display message using toast
            if (data.status === 'Success') {
                canResendCode = false;
                resendCodeButton.disabled = true;
                setTimeout(() => {
                    canResendCode = true;
                    resendCodeButton.disabled = false;
                }, 60000); // Enable the button after 1 minute
            }
        })
        .catch(error => {
            hideLoadingSpinner();
            console.error('Error:', error);
            showToast("An error occurred: " + error.message); // Display error message using toast
        });
    });
</script>
</body>
</html>
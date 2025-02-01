<?php
require_once '../../config.php';

$token = $_GET['token'] ?? '';

// Verify token and update user
$stmt = $conn->prepare("UPDATE caregivers SET is_verified = 1 WHERE verification_token = :token");
$stmt->bindParam(':token', $token);
$stmt->execute();

if ($stmt->rowCount() > 0) {
    echo "<h1>Email verified successfully!</h1>";
} else {
    echo "<h1>Invalid or expired token.</h1>";
}
<?php
require_once('../config.php');

session_start();

// Unset all session variables
$_SESSION = array();

// Destroy the session
session_destroy();

// Remove the authentication token from the database if you are using one
if (isset($_SESSION['token'])) {
    $token = $_SESSION['token'];

    try {
        $stmt = $conn->prepare('DELETE FROM personal_access_tokens WHERE token = :token');
        $stmt->bindParam(':token', $token);
        $stmt->execute();
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
}

// Redirect to login page
header("Location: ../views/login.php");
exit();
?>
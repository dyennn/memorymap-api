<?php
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *"); // Adjust as needed
header("Access-Control-Allow-Methods: GET"); // Changed to GET as we're expecting the token in the URL
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

require_once '../config.php'; // Database credentials

// Database connection
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(['status' => 'Failed', 'message' => 'Database connection failed']);
    exit;
}

if (!isset($_GET['token'])) {
    http_response_code(400);
    echo json_encode(['status' => 'Failed', 'message' => 'Verification token is missing']);
    exit;
}

$token = $_GET['token'];

try {
    // Check if the token exists and is not expired in email_verifications table
    $stmt = $conn->prepare("SELECT caregiver_id, expires_at FROM email_verifications WHERE verification_token =?");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $stmt->bind_result($caregiverId, $expiresAt);
    $stmt->fetch();
    $stmt->close();

    if ($caregiverId && $expiresAt > date('Y-m-d H:i:s')) {
        // Update the caregivers table to set is_verified to true
        $stmt = $conn->prepare("UPDATE caregivers SET is_verified = 1 WHERE caregiver_id =?");
        $stmt->bind_param("i", $caregiverId);
        $stmt->execute();

        if ($stmt->affected_rows > 0) {
            // Delete the verification token from email_verifications
            $stmt = $conn->prepare("DELETE FROM email_verifications WHERE verification_token =?");
            $stmt->bind_param("s", $token);
            $stmt->execute();
            $stmt->close();

            echo "<center><br><br><br><br><br><br><br><br><br><br><h1>Email verified successfully</h1></center>";
            http_response_code(200);
            echo json_encode(['status' => 'Success', 'message' => 'Email verified successfully']);
        } else {
            echo "<center><br><br><br><br><br><br><br><br><br><br><h1>Failed to update verification status</h1></center>";
            http_response_code(500);
            echo json_encode(['status' => 'Failed', 'message' => 'Failed to update verification status']);
        }

    } else {
        echo "<center><br><br><br><br><br><br><br><br><br><br><h1>Invalid or expired verification token</h1></center>";

        http_response_code(400);
        echo json_encode(['status' => 'Failed', 'message' => 'Invalid or expired verification token']);
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => 'Failed', 'message' => 'An error occurred: '. $e->getMessage()]);
} finally {
    $conn->close();
}?>
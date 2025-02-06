<?php
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

require_once '../config.php';

$data = json_decode(file_get_contents("php://input"));
$email = $data->email?? null;
$code = $data->code?? null;
$password = $data->password?? null;
$confirmPassword = $data->confirmPassword?? null;

// Validate input
if (empty($email) ||!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['status' => 'Failed', 'message' => 'Invalid email format']);
    exit;
}
if (empty($code)) {
    http_response_code(400);
    echo json_encode(['status' => 'Failed', 'message' => 'Code is required']);
    exit;
}
if (empty($password) || strlen($password) < 8) {
    http_response_code(400);
    echo json_encode(['status' => 'Failed', 'message' => 'Password must be at least 8 characters']);
    exit;
}
if ($password!== $confirmPassword) {
    http_response_code(400);
    echo json_encode(['status' => 'Failed', 'message' => 'Passwords do not match']);
    exit;
}

// Database connection
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(['status' => 'Failed', 'message' => 'Database connection failed']);
    exit;
}

try {
    // Check if code is valid and not expired
    $stmt = $conn->prepare("SELECT expires_at FROM password_reset_code WHERE email =? AND code =?");
    $stmt->bind_param("ss", $email, $code);
    $stmt->execute();
    $stmt->bind_result($expiresAt);
    $stmt->fetch();
    $stmt->close();

    if (empty($expiresAt) || $expiresAt < date('Y-m-d H:i:s')) {
        http_response_code(400);
        echo json_encode(['status' => 'Failed', 'message' => 'Invalid or expired code']);
        exit;
    }

    // Hash the new password
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    // Update the password in the caregivers table
    $stmt = $conn->prepare("UPDATE caregivers SET password =? WHERE email =?");
    $stmt->bind_param("ss", $hashedPassword, $email);
    if ($stmt->execute()) {
        // Delete the used code from password_reset_code table
        $stmt = $conn->prepare("DELETE FROM password_reset_code WHERE email =? AND code =?");
        $stmt->bind_param("ss", $email, $code);
        $stmt->execute();

        http_response_code(200);
        echo json_encode(['status' => 'Success', 'message' => 'Password changed successfully']);
    } else {
        http_response_code(500);
        echo json_encode(['status' => 'Failed', 'message' => 'Failed to change password']);
    }
    $stmt->close();

} catch (mysqli_sql_exception $e) {
    http_response_code(500);
    echo json_encode(['status' => 'Failed', 'message' => 'Database error: '. $e->getMessage()]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => 'Failed', 'message' => 'An error occurred: '. $e->getMessage()]);
} finally {
    $conn->close();
}?>
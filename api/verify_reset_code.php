<?php
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

require_once '../config.php';
require '../vendor/autoload.php';

use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable("../");
$dotenv->load();

$data = json_decode(file_get_contents("php://input"));
$email = $data->email ?? null;
$code = $data->code ?? null;

// Validate email and code
if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL) || empty($code)) {
    http_response_code(400);
    echo json_encode(['status' => 'Failed', 'message' => 'Invalid email or code']);
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
    // Verify code
    $stmt = $conn->prepare("SELECT expires_at FROM password_reset_code WHERE email = ? AND code = ?");
    $stmt->bind_param("ss", $email, $code);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows === 0) {
        http_response_code(400);
        echo json_encode(['status' => 'Failed', 'message' => 'Invalid or expired code']);
        exit;
    }
    $stmt->bind_result($expiresAt);
    $stmt->fetch();
    $stmt->close();

    // Check if code is expired
    if (strtotime($expiresAt) < time()) {
        http_response_code(400);
        echo json_encode(['status' => 'Failed', 'message' => 'Code has expired']);
        exit;
    }

    http_response_code(200);
    echo json_encode(['status' => 'Success', 'message' => 'Code verified successfully']);

} catch (mysqli_sql_exception $e) {
    http_response_code(500);
    echo json_encode(['status' => 'Failed', 'message' => 'Database error: ' . $e->getMessage()]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => 'Failed', 'message' => 'An error occurred: ' . $e->getMessage()]);
} finally {
    $conn->close();
}
?>
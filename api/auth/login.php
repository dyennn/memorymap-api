<?php
/**
 * Handles user login requests.
 *
 * This script processes a POST request to authenticate a user based on their email and password.
 * It expects a JSON payload with 'email' and 'password' fields.
 *
 * @file /C:/xampp/htdocs/memorymap/api/auth/login.php
 *
 * @requires ../../config.php
 *
 * @header Content-Type: application/json; charset=UTF-8
 * @header Access-Control-Allow-Origin: *
 * @header Access-Control-Allow-Methods: POST
 * @header Access-Control-Max-Age: 3600
 * @header Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With
 *
 * @param string $data->email The email address of the user.
 * @param string $data->password The password of the user.
 *
 * @return void Outputs a JSON response with the status and message of the login attempt.
 *
 * @throws PDOException If there is a database error.
 * @throws Exception If there is a general error.
 *
 * @response
 * 200 OK - Login successful, returns user data.
 * 400 Bad Request - Email and password required.
 * 401 Unauthorized - User not found or invalid credentials.
 * 500 Internal Server Error - Database error or general error.
 */
require_once '../../config.php';

header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");


$data = json_decode(file_get_contents("php://input"));
$response = [];

try {
    if (!isset($data->email) || !isset($data->password)) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Email and password required']);
        exit();
    }

    if (!filter_var($data->email, FILTER_VALIDATE_EMAIL)) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Invalid email format']);
        exit();
    }

    if (strlen(trim($data->password)) < 6) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Password must be at least 6 characters long']);
        exit();
    }

    $stmt = $conn->prepare("SELECT * FROM caregivers WHERE email = :email");
    $email = trim($data->email);
    $stmt->bindParam(':email', $email);
    $stmt->execute();

    if ($stmt->rowCount() === 0) {
        http_response_code(401);
        echo json_encode(['status' => 'error', 'message' => 'User not found']);
        exit();
    }

    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!password_verify(trim($data->password), $user['password'])) {
        http_response_code(401);
        echo json_encode(['status' => 'error', 'message' => 'Invalid credentials']);
        exit();
    }

    http_response_code(200);
    echo json_encode([
        'status' => 'success',
        'message' => 'Login successful',
        'user' => [
            'id' => $user['id'],
            'name' => $user['name'],
            'email' => $user['email']
        ]
    ]);

} catch(PDOException $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
} catch(Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Max-Age: 3600');
header('Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With');

require_once('../config.php'); // Assuming this contains your database credentials

// Check if the request method is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['status' => 'Failed', 'message' => 'Method not allowed']);
    exit;
}

// Get POST data
$data = json_decode(file_get_contents('php://input'), true);
$email = $data['email'] ?? null;
$password = $data['password'] ?? null;

$hints = [];

// Validate email
if (empty($email)) {
    $hints['email'] = 'Email is required';
} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $hints['email'] = 'Not a valid email';
}

// Validate password
if (empty($password)) {
    $hints['password'] = 'Password is required';
}

// Return validation errors if any
if (!empty($hints)) {
    http_response_code(400);
    echo json_encode([
        'status' => 'Failed',
        'message' => 'Failed to login',
        'hint' => $hints
    ]);
    exit;
}

// Database connection
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(['status' => 'Failed', 'message' => 'Database connection failed']);
    exit;
}

// Check if user exists
$stmt = $conn->prepare('SELECT caregiver_id, password, is_verified FROM caregivers WHERE email = ?'); // Include is_verified check
$stmt->bind_param('s', $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    http_response_code(401);
    echo json_encode([
        'status' => 'Failed',
        'message' => 'Invalid credentials' // More generic message
    ]);
    exit;
}

$user = $result->fetch_assoc();

// Verify password and is_verified
if (!password_verify($password, $user['password'])) {
    http_response_code(401);
    echo json_encode([
        'status' => 'Failed',
        'message' => 'Invalid credentials'
    ]);
    exit;
}

if (!$user['is_verified']) {
    http_response_code(401);
    echo json_encode([
        'status' => 'Failed',
        'message' => 'Account not verified. Please check your email.'
    ]);
    exit;
}


// Generate a unique token
$token = bin2hex(random_bytes(32));

// Store the token in the database (personal_access_tokens table)
$stmt = $conn->prepare('INSERT INTO personal_access_tokens (token, caregiver_id) VALUES (?, ?)');
$stmt->bind_param('si', $token, $user['caregiver_id']); // Assuming caregiver_id is an integer
$stmt->execute();

if ($stmt->affected_rows === 0) { // Check if token insertion was successful
    http_response_code(500);
    echo json_encode([
        'status' => 'Failed',
        'message' => 'Failed to generate token'
    ]);
    exit;
}



// Return success response
http_response_code(200);
echo json_encode([
    'status' => 'Success',
    'message' => 'Logged in successfully',
    'token' => $token,
    'caregiver_id' => $user['caregiver_id'] // Include caregiver_id in the response
]);

// Close connections
$stmt->close();
$conn->close();

?>
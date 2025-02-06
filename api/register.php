<?php
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

require_once '../config.php'; // Database credentials
require '../vendor/autoload.php'; // PHPMailer

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;
use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable("../"); // Use __DIR__ for the current directory
$dotenv->load();

$data = json_decode(file_get_contents("php://input"));
$hints = [];

// Validate required fields (presence check)
$requiredFields = ['name', 'email', 'password', 'confirmPassword'];
foreach ($requiredFields as $field) {
    if (!isset($data->$field) || empty(trim($data->$field))) {
        $key = ($field === 'confirmPassword') ? 'confirm_password' : $field;
        $hints[$key] = ucfirst($key) . ' is required'; // More user-friendly message
    }
}

if (!empty($hints)) {
    http_response_code(400);
    echo json_encode(['status' => 'Failed', 'message' => 'Validation errors', 'hint' => $hints]);
    exit();
}

// Assign variables after the initial required field check.
$name = trim($data->name);
$email = trim($data->email);
$password = trim($data->password);
$confirmPassword = trim($data->confirmPassword);

// Field Validations (format and other rules)
if (!preg_match("/^[a-zA-Z-' ]*$/", $name)) {
    $hints['name'] = 'Invalid name format';
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $hints['email'] = 'Invalid email format';
}

if (strlen($password) < 8) {
    $hints['password'] = 'Password must be at least 8 characters';
}

if ($password !== $confirmPassword) {
    $hints['confirm_password'] = 'Passwords do not match';
}

if (!empty($hints)) {
    http_response_code(400);
    echo json_encode(['status' => 'Failed', 'message' => 'Validation errors', 'hint' => $hints]);
    exit();
}

// Database connection
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(['status' => 'Failed', 'message' => 'Database connection failed']);
    exit;
}

try {
    // Check existing email (using prepared statement)
    $stmt = $conn->prepare("SELECT email FROM caregivers WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result(); // Important to use store_result() before num_rows
    if ($stmt->num_rows > 0) {
        http_response_code(409);
        echo json_encode(['status' => 'Failed', 'message' => 'Email already exists']);
        exit();
    }
    $stmt->close(); // Close the statement

    // Hash password and generate token
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    // Insert user (using prepared statement)
    $stmt = $conn->prepare("INSERT INTO caregivers (name, email, password) VALUES (?,?,?)");
    $stmt->bind_param("sss", $name, $email, $hashedPassword);
    $stmt->execute();
    $caregiverId = $conn->insert_id; // Get the ID of the inserted caregiver
    $stmt->close();

    $verificationToken = bin2hex(random_bytes(32)); // Use 32 bytes (64 hex characters)
    
    $expiresAt = date('Y-m-d H:i:s', strtotime('+1 day')); // Set expiration time (e.g., 24 hours)
    
    // Insert verification token into email_verifications table
    $stmt = $conn->prepare("INSERT INTO email_verifications (caregiver_id, verification_token, expires_at) VALUES (?,?,?)");
    $stmt->bind_param("iss", $caregiverId, $verificationToken, $expiresAt);
    
    if ($stmt->execute()) {
        $mail = new PHPMailer(true);
        
        $mail->isSMTP();
        $mail->Host = $_ENV['SMTP_HOST'];
        $mail->SMTPAuth = true;
        $mail->Username = $_ENV['SMTP_USERNAME'];
        $mail->Password = $_ENV['SMTP_PASSWORD'];
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; // Or PHPMailer::ENCRYPTION_SMTPS if required
        $mail->Port = $_ENV['SMTP_PORT'];

        // ... (PHPMailer configuration as before, but with improved error handling)
        $mail->setFrom($_ENV['SMTP_FROM_EMAIL'], $_ENV['SMTP_FROM_NAME']);
        $mail->addAddress($email, $name);
    
        $mail->isHTML(true);
        $mail->Subject = 'Verify Your Email Address';
        $verificationLink = "http://192.168.33.149/memorymap/api/verify-email.php?token=$verificationToken"; // Update with your actual domain
        $mail->Body = "
            <!DOCTYPE html><html><head>...</head><body>
            <div class='container'>
                <h2>Email Verification</h2>
                <p>Please click the button below to verify your email address:</p>
                <a href='$verificationLink' class='button'>Verify Email</a>
                <p>If you didn't create an account, you can safely ignore this email.</p>
            </div></body></html>";
    
        $mail->send();
        
        // Return success response
        http_response_code(200);
        echo json_encode(['status' => 'Success', 'message' => 'Registration successful. Please check your email to verify your account.']);
        exit();
    } else {
        // Log the error (you might want to use a more robust logging mechanism)
        error_log("Failed to insert verification token for caregiver ID: ". $caregiverId. " - Error: ". $stmt->error);

        // Return an error response
        http_response_code(500);
        echo json_encode(['status' => 'Failed', 'message' => 'Failed to create verification token']);
        exit;
    }
} catch (mysqli_sql_exception $e) { // Catch MySQL specific exceptions
    http_response_code(500);
    $errorMessage = ($e->getCode() == 1062) ? "Email already exists" : "Database error: " . $e->getMessage();
    echo json_encode(['status' => 'Failed', 'message' => $errorMessage]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => 'Failed', 'message' => "Email sending failed: {$mail->ErrorInfo}"]); // More specific error message
} finally {
    if (isset($conn)) {
        $conn->close();
    }
}
?>
<?php
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

require_once '../config.php';
require '../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable("../");
$dotenv->load();

$data = json_decode(file_get_contents("php://input"));
$email = $data->email ?? null;

// Validate email
if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['status' => 'Failed', 'message' => 'Invalid email format']);
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
    // Check if email exists
    $stmt = $conn->prepare("SELECT email FROM caregivers WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows === 0) {
        http_response_code(404);
        echo json_encode(['status' => 'Failed', 'message' => 'Email not found']);
        exit;
    }
    $stmt->close();

    // Generate 6-character alphanumeric code
    $code = bin2hex(random_bytes(3)); // 3 bytes = 6 hex characters

    // Calculate expiry time (5 minutes from now)
    $expiresAt = date('Y-m-d H:i:s', strtotime('+5 minutes'));

    // Insert code into password_reset_code table
    $stmt = $conn->prepare("INSERT INTO password_reset_code (email, code, expires_at) VALUES (?,?,?)");
    $stmt->bind_param("sss", $email, $code, $expiresAt);
    if (!$stmt->execute()) {
        // Log the error
        error_log("Failed to insert password reset code for email: " . $email . " - Error: " . $stmt->error);

        // Return an error response
        http_response_code(500);
        echo json_encode(['status' => 'Failed', 'message' => 'Failed to generate reset code']);
        exit;
    }
    $stmt->close();

    // Send email with the code (using PHPMailer)
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host = $_ENV['SMTP_HOST'];
        $mail->SMTPAuth = true;
        $mail->Username = $_ENV['SMTP_USERNAME'];
        $mail->Password = $_ENV['SMTP_PASSWORD'];
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = $_ENV['SMTP_PORT'];

        $mail->setFrom($_ENV['SMTP_FROM_EMAIL'], $_ENV['SMTP_FROM_NAME']);
        $mail->addAddress($email);

        $mail->isHTML(true);
        $mail->Subject = 'Password Reset Code';
        $mail->Body = "
            <!DOCTYPE html><html><head>...</head><body>
            <div class='container'>
                <h2>Password Reset Code</h2>
                <p>Your password reset code is: <b>$code</b></p>
                <p>This code will expire in 5 minutes.</p>
            </div></body></html>";

        $mail->send();

        http_response_code(200);
        echo json_encode(['status' => 'Success', 'message' => 'Password reset code sent to your email']);

    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['status' => 'Failed', 'message' => "Email sending failed: {$mail->ErrorInfo}"]);
    }

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
    <?php
    /*** This script handles user registration for the caregivers.
     * 
     * It expects a JSON payload with the following fields:
     * - name: The name of the user (required)
     * - email: The email address of the user (required)
     * - password: The password for the user account (required)
     * - confirmPassword: Confirmation of the password (required)
     * 
     * The script performs the following actions:
     * 1. Validates that all required fields are present and not empty.
     * 2. Checks that the password and confirmPassword fields match.
     * 3. Checks if the email address already exists in the caregivers table.
     * 4. If the email does not exist, it hashes the password and inserts a new record into the caregivers table.
     * 5. Returns a JSON response indicating success or failure.
     * 
     * Response Codes:
     * - 201: Registration successful
     * - 400: Bad request (e.g., missing fields, passwords do not match)
     * - 409: Conflict (e.g., email already exists)
     * - 500: Internal server error (e.g., database error)
     * 
     * Headers:
     * - Content-Type: application/json; charset=UTF-8
     * - Access-Control-Allow-Origin: *
     * - Access-Control-Allow-Methods: POST
     * - Access-Control-Max-Age: 3600
     * - Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With
     * 
     * @throws PDOException If there is a database error.
     * @throws Exception If there is a general error.
     */
    require_once '../../config.php';
    require '../../vendor/autoload.php'; // Include Composer's autoloader

    use PHPMailer\PHPMailer\PHPMailer;
    use PHPMailer\PHPMailer\Exception;
    use Dotenv\Dotenv;

    header("Content-Type: application/json; charset=UTF-8");
    header("Access-Control-Allow-Origin: *");
    header("Access-Control-Allow-Methods: POST");
    header("Access-Control-Max-Age: 3600");
    header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

    $dotenv = Dotenv::createImmutable(__DIR__ . '/../../');
    $dotenv->load();

    $data = json_decode(file_get_contents("php://input"));
    $response = [];

    try {
        $required = ['name', 'email', 'password', 'confirmPassword'];
        foreach ($required as $field) {
            if (!isset($data->$field) || empty(trim($data->$field))) {
                http_response_code(400);
                echo json_encode(['status' => 'error', 'message' => "$field is required"]);
                exit();
            }
        }

        $errors = [];

        // Validate name
        if (!preg_match("/^[a-zA-Z-' ]*$/", $data->name)) {
            $errors['name'] = 'Invalid name format';
        }

        // Validate email
        if (!filter_var($data->email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Invalid email format';
        }

        // Validate password length
        if (strlen($data->password) < 8) {
            $errors['password'] = 'Password must be at least 8 characters long';
        }

        // Check if passwords match
        if ($data->password !== $data->confirmPassword) {
            $errors['confirmPassword'] = 'Passwords do not match';
        }

        // If there are any errors, return them
        if (!empty($errors)) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'messages' => $errors]);
            exit();
        }

        if ($data->password !== $data->confirmPassword) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Passwords do not match']);
            exit();
        }

        $email = trim($data->email);
        $name = trim($data->name);
        $password = trim($data->password);

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Invalid email format']);
            exit();
        }

        // Check existing email
        $stmt = $conn->prepare("SELECT email FROM caregivers WHERE email = :email");
        $stmt->bindParam(':email', $email);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            http_response_code(409);
            echo json_encode(['status' => 'error', 'message' => 'Email already exists']);
            exit();
        }

        $verificationToken = bin2hex(random_bytes(16));

        // Insert new user
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("INSERT INTO caregivers (name, email, password, verification_token) VALUES (:name, :email, :password, :verification_token)");
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':password', $hashedPassword);
        $stmt->bindParam(':verification_token', $verificationToken);
        $stmt->execute();

        // Log user registration
        error_log("User registered: $email");

        $mail = new PHPMailer(true);
        // Send verification email
        try {
            $mail->isSMTP();
            $mail->Host = $_ENV['SMTP_HOST']; // Set the SMTP server to send through
            $mail->SMTPAuth = true;
            $mail->Username = $_ENV['SMTP_USERNAME']; // SMTP username
            $mail->Password = $_ENV['SMTP_PASSWORD']; // SMTP password
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = $_ENV['SMTP_PORT'];

            //Recipients
            $mail->setFrom('no-reply@yourdomain.com', 'MemoryMap');
            $mail->addAddress($email, $name);

            // Content
            $mail->isHTML(true);
            $mail->Subject = 'Verify Your Email Address';
            $verificationLink = "http://localhost/memorymap/api/auth/verify-email.php?token=$verificationToken";
            $mail->Body = "Hello $name,<br><br>Please verify your email by clicking <a href='$verificationLink'>here</a>.";

            $mail->send();

            // Log email sent
            error_log("Email sent to: $email");
        } catch (Exception $e) {
            error_log("Verification email failed to send to: $email. Mailer Error: {$mail->ErrorInfo}");
        }

        http_response_code(201);
        echo json_encode(['status' => 'success', 'message' => 'Registration successful']);

    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }

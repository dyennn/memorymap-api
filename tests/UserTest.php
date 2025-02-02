<?php
use PHPUnit\Framework\TestCase;

require_once __DIR__. '/../config.php'; // Adjust the path if needed

class UserTest extends TestCase
{
    private $conn;

    // Set up the database connection before each test
    protected function setUp(): void
    {
        $this->conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        if ($this->conn->connect_error) {
            die("Connection failed: ". $this->conn->connect_error);
        }
    }

    // Close the connection after each test
    protected function tearDown(): void
    {
        $this->conn->close();
    }

    // Test for successful registration
    public function testSuccessfulRegistration()
    {
        // Simulate a POST request to register.php
        $data = [
            'name' => 'Test User',
            'email' => 'testuser@example.com',
            'password' => 'testpassword',
            'confirmPassword' => 'testpassword'
        ];

        $response = $this->postRequest('register.php', $data);
        $responseData = json_decode($response, true);

        $this->assertEquals(201, http_response_code()); // Check for 201 Created status code
        $this->assertEquals('Success', $responseData['status']);
        $this->assertEquals('Registered successfully. Check your email to verify.', $responseData['message']);

        // Optionally, you can add assertions to check if the user and verification token were added to the database

        // Clean up: Delete the test user from the database
        $this->deleteUser('testuser@example.com');
    }

    // Test for duplicate email registration
    public function testDuplicateEmailRegistration()
    {
        //... (Similar to testSuccessfulRegistration, but try to register with the same email twice)
    }

    // Test for invalid email format
    public function testInvalidEmailFormat()
    {
        //... (Send a request with an invalid email format)
    }

    // Test for password mismatch
    public function testPasswordMismatch()
    {
        //... (Send a request with mismatched passwords)
    }

    // Test for successful login
    public function testSuccessfulLogin()
    {
        // First, register a test user
        $this->testSuccessfulRegistration();

        // Simulate a POST request to login.php
        $data = [
            'email' => 'testuser@example.com',
            'password' => 'testpassword'
        ];

        $response = $this->postRequest('login.php', $data);
        $responseData = json_decode($response, true);

        $this->assertEquals(200, http_response_code()); // Check for 200 OK status code
        $this->assertEquals('Success', $responseData['status']);
        $this->assertEquals('Logged in successfully', $responseData['message']);
        $this->assertArrayHasKey('token', $responseData); // Check if a token is returned

        // Optionally, you can add assertions to check if the token was added to the database

        // Clean up: Delete the test user and token from the database
        $this->deleteUser('testuser@example.com');
    }

    // Test for login with incorrect password
    public function testLoginWithIncorrectPassword()
    {
        //... (Similar to testSuccessfulLogin, but use an incorrect password)
    }

    // Test for login with unverified account
    public function testLoginWithUnverifiedAccount()
    {
        //... (Register a user but don't verify the email, then try to log in)
    }

    // Helper function to simulate a POST request
    private function postRequest($url, $data)
    {
        $options = [
            'http' => [
                'header'  => "Content-type: application/json\r\n",
                'method'  => 'POST',
                'content' => json_encode($data)
            ]
        ];
        $context  = stream_context_create($options);
        return file_get_contents($url, false, $context);
    }

    // Helper function to delete a test user
    private function deleteUser($email)
    {
        $stmt = $this->conn->prepare("DELETE FROM caregivers WHERE email =?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
    }
}
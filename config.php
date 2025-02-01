<?php
/**
 * Configuration file for database connection.
 *
 * This file contains the database credentials and sets up a connection
 * to the database using PDO (PHP Data Objects).
 *
 * Variables:
 * @var string $host The hostname of the database server.
 * @var string $dbname The name of the database.
 * @var string $username The username for the database connection.
 * @var string $password The password for the database connection.
 *
 * Exceptions:
 * @throws PDOException If there is an error connecting to the database.
 * @throws Exception If there is a general error.
 *
 * Usage:
 * This file should be included wherever a database connection is required.
 * Example:
 * include 'config.php';
 * $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
 */

// Database credentials
$host = "localhost";
$dbname = "memorymap";
$username = "root";
$password = "";

// Set up a connection to the database
try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo "Database connection failed: " . $e->getMessage();
} catch (Exception $e) {
    echo "General error: " . $e->getMessage();
} 
?>

<?php
/**
 * This script handles the API request to read all caregivers from the database.
 * 
 * It sets the appropriate headers for JSON response and CORS, then executes a SQL query
 * to retrieve all caregivers from the database. The results are returned as a JSON response.
 * 
 * @file /c:/xampp/htdocs/memorymap/api/read.php
 * 
 * @requires ../../config.php
 * 
 * @header Content-Type: application/json; charset=UTF-8
 * @header Access-Control-Allow-Origin: *
 * @header Access-Control-Allow-Methods: GET
 * @header Access-Control-Max-Age: 3600
 * @header Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With
 * 
 * @throws Exception If there is an error executing the SQL query.
 * 
 * @return void Outputs a JSON response with the status and data or error message.
 */
require_once '../../config.php';

header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

try {
    // SQL query to get all caregivers
    $sql = "SELECT * FROM caregivers";
    $result = $conn->query($sql);

    if ($result === false) {
        $errorInfo = $conn->errorInfo();
        throw new Exception("Query error: " . $errorInfo[2]);
    }

    $caregivers = array();

    if ($result->rowCount() > 0) {
        // Fetch all rows as associative array
        while($row = $result->fetch(PDO::FETCH_ASSOC)) {
            $caregivers[] = $row;
        }
        
        // Return JSON response with caregivers
        echo json_encode(array(
            "status" => "success",
            "data" => $caregivers
        ));
    } else {
        echo json_encode(array(
            "status" => "success",
            "message" => "No caregivers found",
            "data" => []
        ));
    }
} catch (Exception $e) {
    // Handle exceptions
    echo json_encode(array(
        "status" => "error",
        "message" => $e->getMessage()
    ));
}
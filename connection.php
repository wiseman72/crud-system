<?php
$host = "sql7.freesqldatabase.com";
$username = "sql7801719";
$password = "9esJa4c32a";
$database = "sql7801719";
$port = 3306; // Make sure to include the port

try {
    $conn = new mysqli($host, $username, $password, $database, $port);
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }
    // Optional: Uncomment to confirm connection
    // echo "Connected successfully!";
} catch (Exception $e) {
    error_log("Database connection error: " . $e->getMessage());
    die("Error connecting to the database");
}
?>

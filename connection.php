<?php
$host = "localhost";
$username = "root";
$password = "";
$database = "user_system";

try {
    $conn = new mysqli($host, $username, $password, $database);
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }
} catch (Exception $e) {
    error_log("Database connection error: " . $e->getMessage());
    die("Error connecting to the database");
}
?>

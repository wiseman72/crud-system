
<?php 
session_start(); 
require_once("connection.php"); 

if (!$conn) { 
    die("Connection is not established"); 
} 

if (isset($_GET['id'])) { 
    $id = $_GET['id']; 
    $query = "DELETE FROM staff WHERE id = ?"; 
    $stmt = $conn->prepare($query); 
    $stmt->bind_param("i", $id); 
    if ($stmt->execute()) { 
        echo "Staff member deleted successfully"; 
        echo "<p><a href='staff.php'>Back to Staff List</a></p>"; 
        echo "<p><a href='index.php'>Back to Menu</a></p>"; 
    } else { 
        echo "Error: " . $conn->error; 
    } 
    $stmt->close(); 
} else { 
    echo "Invalid request"; 
    echo "<p><a href='staff.php'>Back to Staff List</a></p>"; 
    echo "<p><a href='index.php'>Back to Menu</a></p>"; 
} 
$conn->close(); 
?>
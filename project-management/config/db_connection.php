<?php
$host = 'localhost';
$db = 'project_management';
$user = 'root';
$pass = 'root';  // Adjust if needed based on your local XAMPP or WAMP settings
$port = '3307';
try {
    $conn = new PDO("mysql:host=$host;port=$port;dbname=$db", $user, $pass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    //echo "Connected successfully"; // Debug line to check connection
} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}
?>

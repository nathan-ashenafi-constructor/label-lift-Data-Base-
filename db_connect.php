<?php
// These are your exact database credentials
$host = 'localhost'; // Use localhost! PHP connects locally via a socket.
$user = 'mznaien';
$pass = 'YDrknvGJINrcbATt';
$db   = 'db_mznaien';

// Create connection
$conn = new mysqli($host, $user, $pass, $db);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
// You can now include this file in all your other scripts!
?>
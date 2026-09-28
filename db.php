<?php
// 'localhost'/ '127.0.0.1'
$host = '127.0.0.1';
$user = 'root';
$pass = '';
$db   = 'heritagelink_db';

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
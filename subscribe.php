<?php
header('Content-Type: application/json');
require_once 'db.php';

$email = isset($_POST['email']) ? trim($_POST['email']) : '';

if (!preg_match('/^.+@.+\..+$/', $email)) {
    echo json_encode(['status' => 'error', 'message' => 'Please enter a valid email address.']);
    exit;
}

$clean_email = $conn->real_escape_string($email);

$check_query = "SELECT id FROM subscribers WHERE email = '$clean_email'";
$result = $conn->query($check_query);

if ($result && $result->num_rows > 0) {
    echo json_encode(['status' => 'warning', 'message' => 'This email is already registered.']);
    exit;
}

$insert_query = "INSERT INTO subscribers (email) VALUES ('$clean_email')";
if ($conn->query($insert_query)) {
    echo json_encode(['status' => 'success', 'message' => 'Thank you for subscribing!']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Could not save email. Please try again.']);
}

$conn->close();
?>
<?php
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 400, 'message' => 'Invalid request method.']);
    exit();
}

require_once '../Database.php';

// Collect and sanitize input
define('REQUIRED_FIELDS', ['name', 'email', 'password', 'cpassword']);
foreach (REQUIRED_FIELDS as $field) {
    if (empty($_POST[$field])) {
        echo json_encode(['status' => 303, 'message' => ucfirst($field) . ' is required.']);
        exit();
    }
}

$name = trim(mysqli_real_escape_string($conn, $_POST['name']));
$email = trim(mysqli_real_escape_string($conn, $_POST['email']));
$password = $_POST['password'];
$cpassword = $_POST['cpassword'];

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['status' => 303, 'message' => 'Invalid email format.']);
    exit();
}

if ($password !== $cpassword) {
    echo json_encode(['status' => 303, 'message' => 'Passwords do not match.']);
    exit();
}

if (strlen($password) < 6) {
    echo json_encode(['status' => 303, 'message' => 'Password must be at least 6 characters.']);
    exit();
}

// Check for duplicate email
$check = mysqli_query($conn, "SELECT id FROM admin WHERE email='$email' LIMIT 1");
if (mysqli_num_rows($check) > 0) {
    echo json_encode(['status' => 303, 'message' => 'Email already registered.']);
    exit();
}

// Hash the password
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

// Insert new admin
$query = "INSERT INTO admin (name, email, password, is_active) VALUES ('$name', '$email', '$hashed_password', '1')";
if (mysqli_query($conn, $query)) {
    echo json_encode(['status' => 202, 'message' => 'Admin registered successfully.']);
} else {
    echo json_encode(['status' => 500, 'message' => 'Database error: ' . mysqli_error($conn)]);
}

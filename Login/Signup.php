<?php
session_start();
require_once __DIR__ . '/../inc/db_connect.php';

$first = trim($_POST['first_name'] ?? '');
$last  = trim($_POST['last_name'] ?? '');
$username = trim($_POST['username'] ?? '');
$email = trim($_POST['email'] ?? '');
$mobile = trim($_POST['mobile_number'] ?? '');
$pass = $_POST['password'] ?? '';
$pass2 = $_POST['password2'] ?? '';

// basic checks
if (!$first || !$last || !$username || !$pass) {
    die('Missing required fields.');
}
if ($pass !== $pass2) {
    die('Passwords do not match.');
}
if (empty($email) && empty($mobile)) {
    die('Provide either email or mobile number.');
}

// check username/email uniqueness
$sql = "SELECT user_id FROM users WHERE username = ? OR email = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $username, $email);
$stmt->execute();
$stmt->store_result();
if ($stmt->num_rows > 0) {
    die('Username or email already taken.');
}
$stmt->close();

// insert user (hash password)
$hash = password_hash($pass, PASSWORD_DEFAULT);
$ins = "INSERT INTO users (first_name,last_name,username,mobile_number,email,password) VALUES (?,?,?,?,?,?)";
$stmt = $conn->prepare($ins);
$stmt->bind_param("ssssss", $first, $last, $username, $mobile, $email, $hash);
if ($stmt->execute()) {
    $_SESSION['user_id'] = $stmt->insert_id;
    $_SESSION['username'] = $username;
    $_SESSION['role'] = 'user';
    header("Location: dashboard.php");
    exit;
} else {
    die('Signup failed: ' . $conn->error);
}

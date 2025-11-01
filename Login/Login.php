<?php
session_start();
require_once __DIR__ . '/../inc/db_connect.php';

$cred = trim($_POST['username_or_email'] ?? '');
$pass = $_POST['password'] ?? '';
if (!$cred || !$pass) die('Missing credentials.');

// we allow username OR email OR mobile login
$sql = "SELECT user_id, username, password, role FROM users WHERE username = ? OR email = ? OR mobile_number = ? LIMIT 1";
$stmt = $conn->prepare($sql);
$stmt->bind_param("sss", $cred, $cred, $cred);
$stmt->execute();
$stmt->bind_result($user_id, $username, $hash, $role);
if ($stmt->fetch()) {
    if (password_verify($pass, $hash)) {
        // login success
        $_SESSION['user_id'] = $user_id;
        $_SESSION['username'] = $username;
        $_SESSION['role'] = $role;
        header("Location: dashboard.php");
        exit;
    } else {
        die('Invalid password.');
    }
} else {
    die('User not found.');
}

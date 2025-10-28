<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require 'db.php';
session_start();

$mail = trim($_POST['school_mail'] ?? '');
$pass = $_POST['password'] ?? '';

$sql = "SELECT user_id, username, password, role FROM users WHERE school_mail=?";
$stmt = $mysqli->prepare($sql);
if (!$stmt) {
    die('SQL prepare failed: '.$mysqli->error.' | SQL: '.$sql);
}
$stmt->bind_param('s', $mail);
if (!$stmt->execute()) {
    die('SQL execute failed: '.$stmt->error);
}
$stmt->bind_result($uid,$uname,$hash,$role);

if ($stmt->fetch() && password_verify($pass, $hash)) {
    $_SESSION['user_id'] = (int)$uid;
    $_SESSION['username'] = $uname;
    $_SESSION['role'] = $role;
    header('Location: post_view.php');
    exit;
} else {
    die('Nesprávne prihlasovacie údaje alebo používateľ neexistuje.');
}

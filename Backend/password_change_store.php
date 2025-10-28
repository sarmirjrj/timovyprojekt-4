<?php
require 'auth.php'; require_login(); require 'db.php';
$uid = current_user_id();
$old = $_POST['old'] ?? '';
$new = $_POST['new'] ?? '';
$q = $mysqli->prepare("SELECT password FROM users WHERE user_id=?");
$q->bind_param('i',$uid); $q->execute(); $q->bind_result($hash); $q->fetch();
if (!password_verify($old, $hash)) { die('Staré heslo je nesprávne'); }
$nh = password_hash($new, PASSWORD_BCRYPT);
$u = $mysqli->prepare("UPDATE users SET password=? WHERE user_id=?");
$u->bind_param('si',$nh,$uid); $u->execute();
header('Location: profile.php');

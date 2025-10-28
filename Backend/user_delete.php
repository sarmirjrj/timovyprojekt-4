<?php
require 'auth.php'; require_login(); require 'db.php';
if (!is_admin()) { die('Len admin'); }

$uid = (int)($_POST['user_id'] ?? 0);
if ($uid <= 0) die('Neplatné ID');

if ($uid === (int)current_user_id()) { die('Nemôžeš zmazať sám seba'); }

$del = $mysqli->prepare("DELETE FROM users WHERE user_id=?");
$del->bind_param('i', $uid);
$del->execute();

header('Location: users_list.php');

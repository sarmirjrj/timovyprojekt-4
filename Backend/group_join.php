<?php
require 'auth.php'; require_login(); require 'db.php';
$gid = (int)($_POST['group_id'] ?? 0);
$uid = current_user_id();
if ($gid<=0) die('Neplatné ID');
$ins = $mysqli->prepare("INSERT IGNORE INTO group_members (group_id, user_id, role) VALUES (?,?, 'member')");
$ins->bind_param('ii',$gid,$uid); $ins->execute();
header('Location: group_detail.php?id='.$gid);

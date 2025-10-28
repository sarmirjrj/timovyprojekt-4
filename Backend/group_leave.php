<?php
require 'auth.php'; require_login(); require 'db.php';
$gid = (int)($_POST['group_id'] ?? 0);
$uid = current_user_id();
if ($gid<=0) die('Neplatné ID');
$del = $mysqli->prepare("DELETE FROM group_members WHERE group_id=? AND user_id=? AND role<>'owner'");
$del->bind_param('ii',$gid,$uid); $del->execute();
header('Location: group_detail.php?id='.$gid);

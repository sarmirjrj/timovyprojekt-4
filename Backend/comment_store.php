<?php
require 'auth.php'; require_login(); require 'db.php';
$post_id = (int)($_POST['post_id'] ?? 0);
$content = trim($_POST['content'] ?? '');
if ($post_id<=0 || $content==='') die('Chýbajú dáta');
$uid = current_user_id();
$ins = $mysqli->prepare("INSERT INTO comments (post_id, user_id, content) VALUES (?,?,?)");
$ins->bind_param('iis',$post_id,$uid,$content); $ins->execute();
header('Location: post_view.php?id='.$post_id);

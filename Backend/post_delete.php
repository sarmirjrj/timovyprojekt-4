<?php
require 'auth.php'; require_login(); require 'db.php';
$post_id = (int)($_POST['post_id'] ?? 0);
if ($post_id<=0) die('Neplatné ID');
$own = $mysqli->prepare("SELECT user_id FROM posts WHERE post_id=?");
$own->bind_param('i',$post_id); $own->execute(); $own->bind_result($author);
if (!$own->fetch()) die('Post neexistuje'); $own->close();
if ($author !== current_user_id() && !is_admin()) { http_response_code(403); die('Nemáš oprávnenie'); }
$del = $mysqli->prepare("DELETE FROM posts WHERE post_id=?");
$del->bind_param('i',$post_id); $del->execute();
header('Location: post_view.php');

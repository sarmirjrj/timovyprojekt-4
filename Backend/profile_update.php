<?php
require 'auth.php'; require_login(); require 'db.php';
$uid = current_user_id();
$u = trim($_POST['username'] ?? '');
$l = trim($_POST['legal_name'] ?? '');
$y = (int)($_POST['study_year'] ?? 1);
if ($u==='' || $l==='') die('Chýbajú údaje');
$upd = $mysqli->prepare("UPDATE users SET username=?, legal_name=?, study_year=? WHERE user_id=?");
$upd->bind_param('ssii', $u, $l, $y, $uid);
$upd->execute();
$_SESSION['username'] = $u;
header('Location: profile.php');

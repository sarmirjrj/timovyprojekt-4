<?php
require 'auth.php'; require_login(); require 'db.php';
if (!is_admin() && current_role()!=='teacher') { die('Len učiteľ alebo admin'); }
$name = trim($_POST['name'] ?? '');
$year = (int)($_POST['year'] ?? 0);
$desc = trim($_POST['description'] ?? '');
if ($name==='' || $year<=0) die('Chýbajú dáta');
$owner = current_user_id();
$ins = $mysqli->prepare("INSERT INTO groups (name, year, description, owner_user_id) VALUES (?,?,?,?)");
$ins->bind_param('sisi', $name, $year, $desc, $owner);
$ins->execute();
$gid = $ins->insert_id;
/* automaticky pridať vlastníka ako člena */
$mem = $mysqli->prepare("INSERT INTO group_members (group_id, user_id, role) VALUES (?,?, 'owner')");
$mem->bind_param('ii', $gid, $owner);
$mem->execute();
header('Location: group_detail.php?id='.$gid);

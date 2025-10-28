<?php
require 'auth.php'; require_login(); require 'db.php';

$gid = (int)($_POST['group_id'] ?? 0);
$mail = trim($_POST['school_mail'] ?? '');
$role = ($_POST['role'] ?? 'member') === 'owner' ? 'owner' : 'member';
if ($gid<=0 || $mail==='') { die('Chýbajú údaje'); }

/* oprávnenie */
$gq = $mysqli->prepare("SELECT owner_user_id FROM groups WHERE group_id=?");
$gq->bind_param('i',$gid); $gq->execute(); $gq->bind_result($owner_id); $gq->fetch(); $gq->close();
if (!($owner_id && (is_admin() || current_role()==='teacher' || current_user_id()==$owner_id))) { die('Nemáš oprávnenie'); }

/* nájdi používateľa podľa mailu */
$uq = $mysqli->prepare("SELECT user_id FROM users WHERE school_mail=?");
$uq->bind_param('s',$mail); $uq->execute(); $uq->bind_result($uid);
if (!$uq->fetch()) { die('Používateľ s týmto e-mailom neexistuje'); }
$uq->close();

/* vlož členstvo */
$ins = $mysqli->prepare("INSERT IGNORE INTO group_members (group_id, user_id, role) VALUES (?,?,?)");
$ins->bind_param('iis', $gid, $uid, $role);
$ins->execute();

header('Location: group_manage_members.php?id='.$gid);

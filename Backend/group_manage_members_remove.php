<?php
require 'auth.php'; require_login(); require 'db.php';

$gid = (int)($_POST['group_id'] ?? 0);
$uid = (int)($_POST['user_id'] ?? 0);
if ($gid<=0 || $uid<=0) { die('Chýbajú údaje'); }

/* oprávnenie */
$gq = $mysqli->prepare("SELECT owner_user_id FROM groups WHERE group_id=?");
$gq->bind_param('i',$gid); $gq->execute(); $gq->bind_result($owner_id); $gq->fetch(); $gq->close();
if (!($owner_id && (is_admin() || current_role()==='teacher' || current_user_id()==$owner_id))) { die('Nemáš oprávnenie'); }

/* neodstraňuj vlastníka */
$chk = $mysqli->prepare("SELECT role FROM group_members WHERE group_id=? AND user_id=?");
$chk->bind_param('ii',$gid,$uid); $chk->execute(); $chk->bind_result($role); $chk->fetch(); $chk->close();
if ($role === 'owner') { die('Nemôžeš odstrániť vlastníka'); }

/* odstráň členstvo */
$del = $mysqli->prepare("DELETE FROM group_members WHERE group_id=? AND user_id=?");
$del->bind_param('ii',$gid,$uid);
$del->execute();

header('Location: group_manage_members.php?id='.$gid);

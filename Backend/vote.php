<?php
require 'auth.php'; require_login(); require 'db.php';

$uid     = current_user_id();
$post_id = (int)($_POST['post_id'] ?? 0);
$action  = $_POST['action'] ?? '';  // 'up' | 'down'

if ($post_id <= 0 || ($action !== 'up' && $action !== 'down')) {
  die('Neplatná požiadavka.');
}

$val = ($action === 'up') ? 1 : -1;

/* Zisti, či už má používateľ hlas na tomto poste */
$sel = $mysqli->prepare("SELECT value FROM post_votes WHERE post_id=? AND user_id=?");
$sel->bind_param('ii', $post_id, $uid);
$sel->execute();
$sel->bind_result($cur);
$has = $sel->fetch();
$sel->close();

if (!$has) {
  /* prvý hlas */
  $ins = $mysqli->prepare("INSERT INTO post_votes (post_id, user_id, value) VALUES (?,?,?)");
  $ins->bind_param('iii', $post_id, $uid, $val);
  $ins->execute();
} else {
  if ((int)$cur === $val) {
    /* klik na rovnaký hlas = zrušenie hlasu (neutral) */
    $del = $mysqli->prepare("DELETE FROM post_votes WHERE post_id=? AND user_id=?");
    $del->bind_param('ii', $post_id, $uid);
    $del->execute();
  } else {
    /* zmena smeru hlasu */
    $upd = $mysqli->prepare("UPDATE post_votes SET value=? WHERE post_id=? AND user_id=?");
    $upd->bind_param('iii', $val, $post_id, $uid);
    $upd->execute();
  }
}

/* prepočítať agregáty do posts */
$countUp = $mysqli->prepare("SELECT COUNT(*) FROM post_votes WHERE post_id=? AND value=1");
$countUp->bind_param('i', $post_id);
$countUp->execute();
$countUp->bind_result($likes);
$countUp->fetch();
$countUp->close();

$countDown = $mysqli->prepare("SELECT COUNT(*) FROM post_votes WHERE post_id=? AND value=-1");
$countDown->bind_param('i', $post_id);
$countDown->execute();
$countDown->bind_result($dislikes);
$countDown->fetch();
$countDown->close();

$agg = $mysqli->prepare("UPDATE posts SET likes=?, dislikes=? WHERE post_id=?");
$agg->bind_param('iii', $likes, $dislikes, $post_id);
$agg->execute();

header('Location: post_view.php?id='.$post_id);
exit;

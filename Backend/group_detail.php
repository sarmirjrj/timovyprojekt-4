<?php
require 'db.php'; require 'auth.php'; include 'header.php';

$gid = (int)($_GET['id'] ?? 0);
if ($gid<=0) { echo 'Neplatné ID skupiny'; include 'footer.php'; exit; }

/* načítaj skupinu + vlastníka (owner_user_id) */
$gq = $mysqli->prepare("SELECT name, year, description, owner_user_id FROM groups WHERE group_id=?");
$gq->bind_param('i',$gid);
$gq->execute();
$gq->bind_result($name,$year,$desc,$owner_id);
if (!$gq->fetch()) { echo 'Skupina nenájdená'; include 'footer.php'; exit; }
$gq->close();

/* členstvo aktuálne prihláseného */
$is_member = false; 
$member_role = null;
$uid = null;

if (is_logged_in()) {
  $uid = current_user_id();
  $mq = $mysqli->prepare("SELECT role FROM group_members WHERE group_id=? AND user_id=?");
  $mq->bind_param('ii',$gid,$uid);
  $mq->execute();
  $mq->bind_result($member_role);
  if ($mq->fetch()) { $is_member = true; }
  $mq->close();
}

/* môže spravovať členov? (admin / teacher / vlastník skupiny) */
$can_manage = is_logged_in() && (is_admin() || current_role()==='teacher' || $uid===$owner_id);
?>
<h2><?= htmlspecialchars($name) ?> (ročník <?= (int)$year ?>)</h2>
<p><?= nl2br(htmlspecialchars($desc)) ?></p>

<?php if ($can_manage): ?>
  <a class="btn btn-outline-primary mb-3" href="group_manage_members.php?id=<?= $gid ?>">Správa členov</a>
<?php endif; ?>

<?php if (is_logged_in()): ?>
  <?php if ($is_member): ?>
    <form method="post" action="group_leave.php" class="d-inline">
      <input type="hidden" name="group_id" value="<?= $gid ?>">
      <button class="btn btn-outline-secondary">Opustiť skupinu</button>
    </form>
    <a class="btn btn-primary" href="post_create.php?group_id=<?= $gid ?>">Nový príspevok do skupiny</a>
  <?php else: ?>
    <form method="post" action="group_join.php" class="d-inline">
      <input type="hidden" name="group_id" value="<?= $gid ?>">
      <button class="btn btn-success">Pridať sa do skupiny</button>
    </form>
  <?php endif; ?>
<?php endif; ?>

<hr>
<h4>Príspevky v skupine</h4>
<?php
$ps = $mysqli->prepare("SELECT p.post_id, p.title, LEFT(p.content,160) AS preview, u.username, p.created_at
                        FROM posts p JOIN users u ON u.user_id=p.user_id
                        WHERE p.group_id=? ORDER BY p.created_at DESC");
$ps->bind_param('i',$gid);
$ps->execute();
$res = $ps->get_result();
?>
<div class="list-group">
<?php while($r = $res->fetch_assoc()): ?>
  <a class="list-group-item list-group-item-action" href="post_view.php?id=<?= $r['post_id'] ?>">
    <div class="d-flex w-100 justify-content-between">
      <h5 class="mb-1"><?= htmlspecialchars($r['title']) ?></h5>
      <small><?= $r['created_at'] ?></small>
    </div>
    <p class="mb-1"><?= htmlspecialchars($r['preview']) ?>…</p>
    <small>@<?= htmlspecialchars($r['username']) ?></small>
  </a>
<?php endwhile; ?>
</div>
<?php include 'footer.php'; ?>

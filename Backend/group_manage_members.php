<?php
require 'auth.php'; require_login(); require 'db.php'; include 'header.php';

$gid = (int)($_GET['id'] ?? 0);
if ($gid <= 0) { echo "Neplatné ID skupiny."; include 'footer.php'; exit; }

/* načítaj skupinu a over oprávnenie */
$gq = $mysqli->prepare("SELECT group_id, name, year, owner_user_id FROM groups WHERE group_id=?");
$gq->bind_param('i', $gid); $gq->execute(); $gq->bind_result($group_id, $name, $year, $owner_id);
if (!$gq->fetch()) { echo "Skupina nenájdená."; include 'footer.php'; exit; }
$gq->close();

$can_manage = is_admin() || current_role()==='teacher' || current_user_id()==$owner_id;
if (!$can_manage) { echo "Nemáš oprávnenie spravovať členov tejto skupiny."; include 'footer.php'; exit; }

/* zoznam členov */
$mem = $mysqli->prepare("SELECT u.user_id, u.username, u.school_mail, m.role
                         FROM group_members m
                         JOIN users u ON u.user_id = m.user_id
                         WHERE m.group_id=?
                         ORDER BY (m.role='owner') DESC, u.username ASC");
$mem->bind_param('i', $gid);
$mem->execute();
$members = $mem->get_result();
?>
<h2>Členovia skupiny: <?= htmlspecialchars($name) ?> (<?= (int)$year ?>)</h2>

<h5>Pridať člena podľa školského e-mailu</h5>
<form method="post" action="group_manage_members_add.php" class="row g-3 mb-4">
  <input type="hidden" name="group_id" value="<?= $gid ?>">
  <div class="col-md-6">
    <input class="form-control" type="email" name="school_mail" placeholder="meno@ucm.sk" required>
  </div>
  <div class="col-md-3">
    <select class="form-select" name="role">
      <option value="member">member</option>
      <option value="owner">owner</option>
    </select>
  </div>
  <div class="col-md-3">
    <button class="btn btn-primary w-100">Pridať</button>
  </div>
</form>

<table class="table table-striped">
  <tr><th>Username</th><th>E-mail</th><th>Rola</th><th></th></tr>
  <?php while($m = $members->fetch_assoc()): ?>
    <tr>
      <td>@<?= htmlspecialchars($m['username']) ?></td>
      <td><?= htmlspecialchars($m['school_mail']) ?></td>
      <td><?= htmlspecialchars($m['role']) ?></td>
      <td>
        <?php if ($m['user_id'] != $owner_id): ?>
          <form method="post" action="group_manage_members_remove.php" onsubmit="return confirm('Odstrániť člena?');" class="d-inline">
            <input type="hidden" name="group_id" value="<?= $gid ?>">
            <input type="hidden" name="user_id" value="<?= (int)$m['user_id'] ?>">
            <button class="btn btn-sm btn-outline-danger">Odstrániť</button>
          </form>
        <?php endif; ?>
      </td>
    </tr>
  <?php endwhile; ?>
</table>

<p><a class="btn btn-secondary" href="group_detail.php?id=<?= $gid ?>">Späť na skupinu</a></p>
<?php include 'footer.php'; ?>

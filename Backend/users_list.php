<?php
require 'auth.php'; require_login();
if (!is_admin()) { die('Len admin'); }
require 'db.php'; include 'header.php';

$res = $mysqli->query("SELECT user_id, username, legal_name, school_mail, role, study_year, created_at FROM users ORDER BY created_at DESC");
?>
<h2>Používatelia</h2>
<table class="table table-striped">
  <tr>
    <th>ID</th><th>Username</th><th>Meno</th><th>E-mail</th><th>Rola</th><th>Ročník</th><th>Vytvorený</th><th></th>
  </tr>
  <?php while($u = $res->fetch_assoc()): ?>
    <tr>
      <td><?= (int)$u['user_id'] ?></td>
      <td><?= htmlspecialchars($u['username']) ?></td>
      <td><?= htmlspecialchars($u['legal_name']) ?></td>
      <td><?= htmlspecialchars($u['school_mail']) ?></td>
      <td><?= htmlspecialchars($u['role']) ?></td>
      <td><?= htmlspecialchars($u['study_year']) ?></td>
      <td><?= htmlspecialchars($u['created_at']) ?></td>
      <td>
        <?php if ((int)$u['user_id'] !== (int)current_user_id()): ?>
        <form method="post" action="user_delete.php" onsubmit="return confirm('Zmazať používateľa aj s obsahom?');">
          <input type="hidden" name="user_id" value="<?= (int)$u['user_id'] ?>">
          <button class="btn btn-sm btn-danger">Zmazať</button>
        </form>
        <?php endif; ?>
      </td>
    </tr>
  <?php endwhile; ?>
</table>
<?php include 'footer.php'; ?>

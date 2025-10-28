<?php
require 'auth.php'; require_login();
require 'db.php';
include 'header.php';

$uid = current_user_id();
$q = $mysqli->prepare("SELECT username, legal_name, school_mail, role, study_year FROM users WHERE user_id=?");
$q->bind_param('i',$uid);
$q->execute();
$q->bind_result($u,$l,$m,$r,$y);
if ($q->fetch()):
?>
<h2>Môj profil</h2>
<table class="table">
  <tr><th>Username</th><td><?= htmlspecialchars($u) ?></td></tr>
  <tr><th>Meno</th><td><?= htmlspecialchars($l) ?></td></tr>
  <tr><th>Školský email</th><td><?= htmlspecialchars($m) ?></td></tr>
  <tr><th>Rola</th><td><?= htmlspecialchars($r) ?></td></tr>
  <tr><th>Ročník</th><td><?= (int)$y ?></td></tr>
</table>
<a class="btn btn-secondary" href="profile_edit.php">Upraviť profil</a>
<a class="btn btn-outline-secondary" href="password_change.php">Zmeniť heslo</a>
<?php else: ?>
<p>Profil nenájdený.</p>
<?php endif; include 'footer.php'; ?>

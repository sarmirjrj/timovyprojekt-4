<?php
require 'auth.php'; require_login(); require 'db.php'; include 'header.php';
$uid = current_user_id();
$q = $mysqli->prepare("SELECT username, legal_name, study_year FROM users WHERE user_id=?");
$q->bind_param('i',$uid); $q->execute(); $q->bind_result($u,$l,$y); $q->fetch();
?>
<h2>Upraviť profil</h2>
<form method="post" action="profile_update.php" class="row g-3">
  <div class="col-md-6">
    <label class="form-label">Username</label>
    <input class="form-control" type="text" name="username" value="<?= htmlspecialchars($u) ?>" required>
  </div>
  <div class="col-md-6">
    <label class="form-label">Meno</label>
    <input class="form-control" type="text" name="legal_name" value="<?= htmlspecialchars($l) ?>" required>
  </div>
  <div class="col-md-3">
    <label class="form-label">Ročník</label>
    <input class="form-control" type="number" min="1" max="6" name="study_year" value="<?= (int)$y ?>" required>
  </div>
  <div class="col-12">
    <button class="btn btn-primary">Uložiť</button>
  </div>
</form>
<?php include 'footer.php'; ?>

<?php
require 'db.php'; require 'auth.php'; include 'header.php'; 
$year = isset($_GET['year']) ? (int)$_GET['year'] : 0;
if ($year>0) {
  $stmt = $mysqli->prepare("SELECT group_id, name, year, description FROM groups WHERE year=? ORDER BY name");
  $stmt->bind_param('i',$year);
} else {
  $stmt = $mysqli->prepare("SELECT group_id, name, year, description FROM groups ORDER BY year, name");
}
$stmt->execute();
$res = $stmt->get_result();
?>
<h2>Skupiny (predmety/ročníky)</h2>
<form class="row g-3 mb-3">
  <div class="col-auto">
    <label class="col-form-label">Filtrovať ročník</label>
  </div>
  <div class="col-auto">
    <input class="form-control" type="number" name="year" value="<?= $year ?: '' ?>" min="1" max="6">
  </div>
  <div class="col-auto">
    <button class="btn btn-secondary">Filtrovať</button>
  </div>
</form>
<table class="table table-striped">
  <tr><th>Názov</th><th>Ročník</th><th>Popis</th><th></th></tr>
  <?php while($g = $res->fetch_assoc()): ?>
    <tr>
      <td><?= htmlspecialchars($g['name']) ?></td>
      <td><?= (int)$g['year'] ?></td>
      <td><?= htmlspecialchars($g['description']) ?></td>
      <td><a class="btn btn-sm btn-primary" href="group_detail.php?id=<?= $g['group_id'] ?>">Otvoriť</a></td>
    </tr>
  <?php endwhile; ?>
</table>
<?php if (is_admin()): ?>
  <a class="btn btn-success" href="group_create.php">Vytvoriť skupinu</a>
<?php endif; ?>
<?php include 'footer.php'; ?>

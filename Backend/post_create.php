<?php
require 'auth.php'; require_login(); require 'db.php'; include 'header.php';
$gid = isset($_GET['group_id']) ? (int)$_GET['group_id'] : 0;

/* načítaj všetky skupiny (predmety/ročníky) bez ohľadu na členstvo.
   Členstvo sa aj tak overí až v post_store.php. */
$groups = $mysqli->query("SELECT group_id, name, year FROM groups ORDER BY year, name");
?>
<h2>Nový príspevok</h2>

<?php if ($groups && $groups->num_rows === 0): ?>
  <div class="alert alert-warning">
    Zatiaľ nemáš žiadne skupiny v systéme.  
    <?php if (is_admin() || current_role()==='teacher'): ?>
      Ako učiteľ/admin môžeš <a href="group_create.php" class="alert-link">vytvoriť skupinu</a>.
    <?php else: ?>
      Požiadaj učiteľa alebo admina, nech skupinu vytvorí.
    <?php endif; ?>
  </div>
<?php endif; ?>

<form method="post" action="post_store.php" enctype="multipart/form-data" class="row g-3">
  <div class="col-md-8">
    <label class="form-label">Názov</label>
    <input class="form-control" type="text" name="title" required>
  </div>
  <div class="col-md-4">
    <label class="form-label">Skupina (predmet/ročník)</label>
    <select class="form-select" name="group_id" required>
      <option value="">-- vyber --</option>
      <?php if ($groups): while($g = $groups->fetch_assoc()): ?>
        <option value="<?= (int)$g['group_id'] ?>" <?= ($gid==$g['group_id'])?'selected':'' ?>>
          <?= htmlspecialchars($g['name']) ?> (<?= (int)$g['year'] ?>)
        </option>
      <?php endwhile; endif; ?>
    </select>
    <div class="form-text">Pozn.: Pridať príspevok môžeš len do skupiny, ktorej si členom.</div>
  </div>

  <div class="col-12">
    <label class="form-label">Obsah</label>
    <textarea class="form-control" name="content" rows="5" required></textarea>
  </div>

  <div class="col-12">
    <label class="form-label">Príloha (pdf/jpg/png) – voliteľné</label>
    <input class="form-control" type="file" name="file">
  </div>

  <div class="col-12">
    <button class="btn btn-primary">Uložiť</button>
    <?php if (is_admin() || current_role()==='teacher'): ?>
      <a class="btn btn-outline-secondary ms-2" href="group_create.php">Vytvoriť novú skupinu</a>
    <?php endif; ?>
  </div>
</form>
<?php include 'footer.php'; ?>

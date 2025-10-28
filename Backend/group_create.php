<?php
require 'auth.php'; require_login();
if (!is_admin() && current_role()!=='teacher') { die('Len učiteľ alebo admin môže vytvoriť skupinu'); }
include 'header.php'; ?>
<h2>Vytvoriť skupinu</h2>
<form method="post" action="group_store.php" class="row g-3">
  <div class="col-md-6">
    <label class="form-label">Názov predmetu</label>
    <input class="form-control" type="text" name="name" required>
  </div>
  <div class="col-md-3">
    <label class="form-label">Ročník</label>
    <input class="form-control" type="number" name="year" min="1" max="6" required>
  </div>
  <div class="col-12">
    <label class="form-label">Popis</label>
    <textarea class="form-control" name="description" rows="3"></textarea>
  </div>
  <div class="col-12">
    <button class="btn btn-primary">Vytvoriť</button>
  </div>
</form>
<?php include 'footer.php'; ?>

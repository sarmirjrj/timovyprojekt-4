<?php
require 'auth.php'; require_login(); include 'header.php'; ?>
<h2>Zmena hesla</h2>
<form method="post" action="password_change_store.php" class="row g-3">
  <div class="col-md-6">
    <label class="form-label">Staré heslo</label>
    <input class="form-control" type="password" name="old" required>
  </div>
  <div class="col-md-6">
    <label class="form-label">Nové heslo</label>
    <input class="form-control" type="password" name="new" required>
  </div>
  <div class="col-12">
    <button class="btn btn-primary">Zmeniť</button>
  </div>
</form>
<?php include 'footer.php'; ?>

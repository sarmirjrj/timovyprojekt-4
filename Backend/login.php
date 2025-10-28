<?php include 'header.php'; ?>
<h2>Prihlásenie</h2>
<form method="post" action="login_check.php" class="row g-3">
  <div class="col-md-6">
    <label class="form-label">Školský email</label>
    <input class="form-control" type="email" name="school_mail" required>
  </div>
  <div class="col-md-6">
    <label class="form-label">Heslo</label>
    <input class="form-control" type="password" name="password" required>
  </div>
  <div class="col-12">
    <button class="btn btn-primary">Prihlásiť</button>
  </div>
</form>
<?php include 'footer.php'; ?>

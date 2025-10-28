<?php include 'header.php'; ?>
<h2>Registrácia</h2>
<form method="post" action="register_store.php" class="row g-3">
  <div class="col-md-6">
    <label class="form-label">Username (@meno)</label>
    <input class="form-control" type="text" name="username" required>
  </div>
  <div class="col-md-6">
    <label class="form-label">Celé meno (legal name)</label>
    <input class="form-control" type="text" name="legal_name" required>
  </div>
  <div class="col-md-6">
    <label class="form-label">Školský email (login)</label>
    <input class="form-control" type="email" name="school_mail" required>
  </div>
  <div class="col-md-3">
    <label class="form-label">Ročník</label>
    <input class="form-control" type="number" min="1" max="6" name="study_year" required>
  </div>
  <div class="col-md-3">
    <label class="form-label">Rola</label>
    <select class="form-select" name="role" required>
      <option value="student">študent</option>
      <option value="teacher">učiteľ</option>
      <option value="admin">admin</option>
    </select>
  </div>
  <div class="col-md-6">
    <label class="form-label">Heslo</label>
    <input class="form-control" type="password" name="password" required>
  </div>
  <div class="col-12">
    <button class="btn btn-primary">Vytvoriť účet</button>
  </div>
</form>
<?php include 'footer.php'; ?>

<?php
// DEBUG režim – dočasne:
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
ini_set('log_errors', 1);
error_reporting(E_ALL);
ini_set('error_log', __DIR__.'/php-error.log');

register_shutdown_function(function(){
  $e = error_get_last();
  if ($e && in_array($e['type'], [E_ERROR,E_PARSE,E_CORE_ERROR,E_COMPILE_ERROR])) {
    echo "<pre>PHP FATAL: {$e['message']} in {$e['file']}:{$e['line']}</pre>";
    echo "<p>Pozri aj súbor <code>php-error.log</code> v priečinku studdit.</p>";
  }
});

require 'db.php';

function fail($msg){
  echo "<pre>Chyba: $msg</pre>";
  if (function_exists('mysqli_error')) { global $mysqli; echo "<pre>MySQL: ".$mysqli->error."</pre>"; }
  exit;
}

// Načítaj vstupy
$req = ['username','legal_name','school_mail','study_year','role','password'];
foreach ($req as $r) { if (empty($_POST[$r])) fail('Chýba pole: '.$r); }

$username = trim($_POST['username']);
$legal    = trim($_POST['legal_name']);
$mail     = trim($_POST['school_mail']);
$year     = (int)$_POST['study_year'];
$role     = in_array($_POST['role'], ['student','teacher','admin'], true) ? $_POST['role'] : 'student';
$hash     = password_hash($_POST['password'], PASSWORD_BCRYPT);

$neededCols = ['user_id','username','legal_name','school_mail','password','role','study_year'];
$cols = [];
$res = $mysqli->query("SHOW COLUMNS FROM users");
if (!$res) fail('SHOW COLUMNS FROM users zlyhalo');
while($c = $res->fetch_assoc()){ $cols[] = $c['Field']; }
$missing = array_diff($neededCols, $cols);
if ($missing) {
  echo "<pre>V tabuľke users chýbajú stĺpce: ".implode(', ', $missing)."</pre>";
  echo "<p>Spusti SQL migráciu (migration_add_groups_and_filters.sql) a/alebo zarovnávacie SQL nižšie.</p>";
  exit;
}

// Registrácia / aktualizácia
$check = $mysqli->prepare("SELECT user_id FROM users WHERE school_mail=?");
if (!$check) fail('prepare(check) zlyhalo');
$check->bind_param('s', $mail);
$check->execute();
$check->store_result();

if ($check->num_rows === 0) {
  $ins = $mysqli->prepare("INSERT INTO users (username, legal_name, school_mail, password, role, study_year) VALUES (?,?,?,?,?,?)");
  if (!$ins) fail('prepare(insert) zlyhalo');
  $ins->bind_param('sssssi', $username, $legal, $mail, $hash, $role, $year);
  if (!$ins->execute()) fail('execute(insert) zlyhalo');
} else {
  $upd = $mysqli->prepare("UPDATE users SET username=?, legal_name=?, role=?, study_year=?, password=? WHERE school_mail=?");
  if (!$upd) fail('prepare(update) zlyhalo');
  $upd->bind_param('sssiss', $username, $legal, $role, $year, $hash, $mail);
  if (!$upd->execute()) fail('execute(update) zlyhalo');
}

header('Location: login.php');
exit;

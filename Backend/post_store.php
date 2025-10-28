<?php
require 'auth.php'; require_login(); require 'db.php';

$uid = current_user_id();
$title = trim($_POST['title'] ?? '');
$content = trim($_POST['content'] ?? '');
$gid = (int)($_POST['group_id'] ?? 0);

if ($title === '' || $content === '' || $gid <= 0) {
  die('Chýbajú údaje (názov/obsah/skupina).');
}

/* kontrola členstva v skupine */
$chk = $mysqli->prepare("SELECT 1 FROM group_members WHERE group_id=? AND user_id=?");
$chk->bind_param('ii', $gid, $uid);
$chk->execute();
$chk->store_result();
if ($chk->num_rows === 0) {
  die('Nie si členom skupiny – príspevok nemôžeš pridať.');
}

/* uloženie príspevku */
$ins = $mysqli->prepare("INSERT INTO posts (user_id, group_id, title, content) VALUES (?,?,?,?)");
$ins->bind_param('iiss', $uid, $gid, $title, $content);
$ins->execute();
$post_id = $ins->insert_id;

/* upload súboru (voliteľné) */
if (!empty($_FILES['file']['name'])) {
  if ($_FILES['file']['error'] === UPLOAD_ERR_OK) {
    $name = $_FILES['file']['name'];
    $tmp  = $_FILES['file']['tmp_name'];
    $ext  = strtolower(pathinfo($name, PATHINFO_EXTENSION));
    $safe = preg_replace('/[^a-z0-9\._-]/i','_', $name);
    $dest = 'uploads/'.time().'_'.$safe;
    $allowed = ['pdf','jpg','jpeg','png'];
    if (!in_array($ext, $allowed, true)) { die('Nepodporovaný formát súboru.'); }
    if (!move_uploaded_file($tmp, $dest)) { die('Chyba pri ukladaní súboru.'); }

    $insf = $mysqli->prepare("INSERT INTO post_files (post_id, file_name, file_path, file_type) VALUES (?,?,?,?)");
    $file_type = $ext;
    $insf->bind_param('isss', $post_id, $name, $dest, $file_type);
    $insf->execute();
  }
}

header('Location: post_view.php?id='.$post_id);

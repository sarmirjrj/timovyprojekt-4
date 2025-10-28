<?php if (session_status()===PHP_SESSION_NONE){ session_start(); } ?>
<!DOCTYPE html>
<html lang="sk">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Studdit</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
  <div class="container">
    <a class="navbar-brand" href="index.html">Studdit</a>
    <div class="collapse navbar-collapse">
      <ul class="navbar-nav ms-auto">
        <li class="nav-item"><a class="nav-link" href="group_list.php">Skupiny</a></li>
        <li class="nav-item"><a class="nav-link" href="post_view.php">Feed</a></li>
        <?php if (!empty($_SESSION['user_id'])): ?>
          <li class="nav-item"><a class="nav-link" href="post_create.php">Nový príspevok</a></li>
          <?php if (!empty($_SESSION['user_id'])): ?>
            <?php if ($_SESSION['role'] === 'admin'): ?>
            <li class="nav-item"><a class="nav-link" href="users_list.php">Používatelia</a></li>
            <?php endif; ?>
          <?php endif; ?>
          <li class="nav-item"><a class="nav-link" href="profile.php">Môj profil</a></li>
          <li class="nav-item"><a class="nav-link" href="logout.php">Odhlásiť</a></li>
        <?php else: ?>
          <li class="nav-item"><a class="nav-link" href="register.php">Registrácia</a></li>
          <li class="nav-item"><a class="nav-link" href="login.php">Prihlásiť</a></li>
        <?php endif; ?>
      </ul>
    </div>
  </div>
</nav>
<div class="container my-4">

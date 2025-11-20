<?php if (session_status() === PHP_SESSION_NONE) { session_start(); } ?>
<!DOCTYPE html>
<html lang="sk">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Studdit</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    * { box-sizing: border-box; margin: 0; padding: 0; }
    body {
      margin: 0;
      background: #0e0e10;
      font-family: "Segoe UI", sans-serif;
      color: #fff;
      transition: background 0.3s, color 0.3s;
    }

    body.light-mode {
      background: #f5f5f5;
      color: #333;
    }

    .main-header {
      position: relative;
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 14px 60px;
      background: linear-gradient(90deg, #111 0%, #191919 100%);
      border-bottom: 1px solid rgba(255, 255, 255, 0.1);
      overflow: hidden;
      transition: background 0.3s, border-color 0.3s;
    }

    body.light-mode .main-header {
      background: linear-gradient(90deg, #ffffff 0%, #f0f0f0 100%);
      border-bottom: 1px solid rgba(0, 0, 0, 0.1);
    }

    .main-header::after {
      content: "";
      position: absolute;
      top: var(--y, 0);
      left: var(--x, 0);
      width: 200px;
      height: 200px;
      background: radial-gradient(circle at center, rgba(0, 132, 255, 0.35), transparent 70%);
      transform: translate(-50%, -50%);
      pointer-events: none;
      transition: top 0.1s, left 0.1s;
    }

    .header-content {
      display: flex;
      justify-content: space-between;
      align-items: center;
      width: 100%;
      position: relative;
      z-index: 1;
    }

    .left-section {
      display: flex;
      align-items: center;
      gap: 40px;
    }

    .logo a {
      font-size: 26px;
      color: #00b3ff;
      font-weight: bold;
      text-decoration: none;
      transition: color 0.3s, text-shadow 0.3s;
    }

    .logo a:hover {
      color: #66d9ff;
      text-shadow: 0 0 10px #00b3ff;
    }

    .banner_logo {
      height: 60px;
      width: auto;
      transition: transform 0.3s, filter 0.3s;
    }

    .banner_logo:hover {
      transform: scale(1.1);
      filter: drop-shadow(0 0 8px rgba(0, 179, 255, 0.6));
    }

    .nav-links {
      display: flex;
      align-items: center;
    }

    .nav-links a {
      margin: 0 18px;
      text-decoration: none;
      color: #ccc;
      position: relative;
      transition: color 0.3s;
      font-weight: 500;
    }

    body.light-mode .nav-links a {
      color: #555;
    }

    .nav-links a::before {
      content: "";
      position: absolute;
      left: 50%;
      bottom: -5px;
      width: 0%;
      height: 2px;
      background: #00b3ff;
      transition: width 0.3s, left 0.3s;
    }

    .nav-links a:hover {
      color: #fff;
    }

    body.light-mode .nav-links a:hover {
      color: #000;
    }

    .nav-links a:hover::before {
      width: 100%;
      left: 0;
    }

    .theme-toggle {
      background: rgba(255, 255, 255, 0.1);
      border: 1px solid rgba(255, 255, 255, 0.2);
      border-radius: 50px;
      width: 60px;
      height: 30px;
      position: relative;
      cursor: pointer;
      transition: all 0.3s;
      margin-left: 20px;
    }

    body.light-mode .theme-toggle {
      background: rgba(0, 0, 0, 0.1);
      border: 1px solid rgba(0, 0, 0, 0.2);
    }

    .theme-toggle:hover {
      border-color: #00b3ff;
      box-shadow: 0 0 10px rgba(0, 179, 255, 0.3);
    }

    .theme-toggle::before {
      content: "🌙";
      position: absolute;
      top: 50%;
      left: 4px;
      transform: translateY(-50%);
      width: 22px;
      height: 22px;
      background: #00b3ff;
      border-radius: 50%;
      transition: all 0.3s;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 12px;
    }

    body.light-mode .theme-toggle::before {
      content: "☀️";
      left: calc(100% - 26px);
      background: #ffa500;
    }

    .page-content {
      padding: 40px;
    }
  </style>
</head>
<body>

<header class="main-header" id="mainHeader">
  <div class="header-content">
    <div class="left-section">
      <div class="logo">
        <a class="navbar-brand" href="../WEB/index.html">
          <img src="../WEB/images/banner_logo.png" alt="banner" class="banner_logo">
        </a>
      </div>
      <nav class="nav-links">
        <a href="group_list.php">Skupiny</a>
        <a href="post_view.php">Feed</a>
        <?php if (!empty($_SESSION['user_id'])): ?>
          <a href="post_create.php">Nový príspevok</a>
          <?php if ($_SESSION['role'] === 'admin'): ?>
            <a href="users_list.php">Používatelia</a>
          <?php endif; ?>
          <a href="profile.php">Môj profil</a>
          <a href="library.php">Knižnica súborov</a>
          <a href="logout.php">Odhlásiť</a>
        <?php else: ?>
          <a href="register.php">Registrácia</a>
          <a href="login.php">Prihlásiť</a>
        <?php endif; ?>
      </nav>
    </div>
    <div class="theme-toggle" id="themeToggle"></div>
  </div>
</header>

<div class="page-content">

<script>
// Efekt mysi na header
document.addEventListener("mousemove", function(e) {
  const header = document.getElementById("mainHeader");
  const rect = header.getBoundingClientRect();
  const x = e.clientX - rect.left;
  const y = e.clientY - rect.top;
  header.style.setProperty("--x", x + "px");
  header.style.setProperty("--y", y + "px");
});

// Prepinac temy
const themeToggle = document.getElementById('themeToggle');
const body = document.body;

// Nacitanie ulozenej temy
const savedTheme = localStorage.getItem('theme');
if (savedTheme === 'light') {
  body.classList.add('light-mode');
}

// Prepinanie temy
themeToggle.addEventListener('click', function() {
  body.classList.toggle('light-mode');
  
  // Ulozenie do localStorage
  if (body.classList.contains('light-mode')) {
    localStorage.setItem('theme', 'light');
  } else {
    localStorage.setItem('theme', 'dark');
  }
});
</script>
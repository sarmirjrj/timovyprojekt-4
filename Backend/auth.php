<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }

function is_logged_in(): bool { return isset($_SESSION['user_id']); }
function require_login(): void { if (!is_logged_in()) { header('Location: login.php'); exit; } }
function current_user_id(): ?int { return $_SESSION['user_id'] ?? null; }
function current_username(): string { return $_SESSION['username'] ?? 'guest'; }
function current_role(): string { return $_SESSION['role'] ?? 'student'; }
function is_admin(): bool { return current_role() === 'admin'; }
?>

<?php
ini_set('display_errors',1); error_reporting(E_ALL);
require 'db.php';

echo "<h3>DB spojenie</h3>";
$ok = $mysqli->query("SELECT 1")->fetch_row()[0] ?? null;
echo $ok ? "OK<br>" : "ZLYHALO<br>";

echo "<h3>Stĺpce v users</h3><pre>";
$res = $mysqli->query("SHOW COLUMNS FROM users");
while($r=$res->fetch_assoc()){ echo $r['Field']."\n"; }
echo "</pre>";

echo "<h3>Stĺpce v posts</h3><pre>";
$res = $mysqli->query("SHOW COLUMNS FROM posts");
while($r=$res->fetch_assoc()){ echo $r['Field']."\n"; }
echo "</pre>";

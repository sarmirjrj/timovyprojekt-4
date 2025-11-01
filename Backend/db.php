<?php
$host = 'database';
$db   = $_ENV['MARIADB_DATABASE'];
$user = $_ENV['MARIADB_USER'];
$pass = $_ENV['MARIADB_PASSWORD'];
$port = 3306;

$mysqli = new mysqli($host, $user, $pass, $db, $port);
if ($mysqli->connect_error) {
    die('Chyba pripojenia: ' . $mysqli->connect_error);
}
$mysqli->set_charset('utf8mb4');

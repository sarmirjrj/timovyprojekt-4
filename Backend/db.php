<?php
$host = '127.0.0.1';
$db   = 'studdit';
$user = 'root';
$pass = 'usbw';
$port = 3306;

$mysqli = new mysqli($host, $user, $pass, $db, $port);
if ($mysqli->connect_error) {
    die('Chyba pripojenia: ' . $mysqli->connect_error);
}
$mysqli->set_charset('utf8mb4');

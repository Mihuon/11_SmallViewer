<?php
require_once __DIR__ . "/../bootstrap/bootstrap.php";

$host = "127.0.0.1";
$user = "###";
$password = "###.";
$database = "c158IP3";

// $host= AppConfig::get('host');
// $user= AppConfig::get('user');
// $password = AppConfig::get('password');
// $database = AppConfig::get('database');

$connection = mysqli_connect($host, $user, $password, $database);

if (!$connection) {
	echo "Connection failed!";
}

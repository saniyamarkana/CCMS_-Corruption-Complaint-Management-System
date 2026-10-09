<?php

$host = "localhost";
$user = "root";
$password = "";
$database = "ccms";

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$conn = mysqli_connect($host, $user, $password, $database);

if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}
?>

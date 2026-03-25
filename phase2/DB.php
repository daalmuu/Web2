<?php
$host = "localhost";
$username = "root";
$password = "";
$database = "bellacucina_db";

$conn = mysqli_connect($host, $username, $password, $database);

if (!$conn) {
    die("connection failed: " . mysqli_connect_error());
}

mysqli_set_charset($conn, "utf8");
?>

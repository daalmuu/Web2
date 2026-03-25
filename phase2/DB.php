<?php
$conn = new mysqli("localhost", "root", "", "recipe_system");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
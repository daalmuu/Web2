<?php
$conn = new mysqli(
    "sql200.infinityfree.com",
    "if0_41918251",
    "dalalmohammed",
    "if0_41918251_bellacucina_db",
   8889
);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>

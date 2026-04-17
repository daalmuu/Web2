<?php
include('session.php');
include('DB.php');

if (!isset($_SESSION['usertype']) || $_SESSION['usertype'] !== 'admin') {
    header("Location: login.php?error=Access+denied");
    exit();
}
?>
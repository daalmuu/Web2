<?php
require_once("session.php");
require_once("DB.php");
if (!isset($_SESSION['usertype']) || $_SESSION['usertype'] !== 'admin') {
    header("Location: login.php?error=Access+denied");
    exit();
}
?>
<?php
include("session.php");
include("DB.php");

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: User-dashboard.php");
    exit();
}

$recipeID = intval($_GET['id']);
$userID   = $_SESSION['userid'];

$stmt = $conn->prepare("DELETE FROM favourites WHERE userid = ? AND recipeid = ?");
$stmt->bind_param("ii", $userID, $recipeID);
$stmt->execute();
$stmt->close();

header("Location: User-dashboard.php");
exit();
?>
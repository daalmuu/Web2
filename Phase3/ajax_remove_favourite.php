<?php
require_once("session.php");
require_once("DB.php");

if ($_SESSION['usertype'] != "user") {
    echo "false";
    exit();
}

$recipeID = isset($_POST['recipeID']) ? intval($_POST['recipeID']) : 0;
$userID   = $_SESSION['userid'];

if ($recipeID <= 0) {
    echo "false";
    exit();
}

$stmt = $conn->prepare("DELETE FROM favourites WHERE userid = ? AND recipeid = ?");
$stmt->bind_param("ii", $userID, $recipeID);
$stmt->execute();

echo ($stmt->affected_rows > 0) ? "true" : "false";
$stmt->close();
?>

<?php
include('session.php');
include('DB.php');

if (empty($_GET['recipe_id'])) {
    header("Location: User-dashboard.php");
    exit();
}

$recipe_id = (int)$_GET['recipe_id'];
$user_id   = (int)$_SESSION['userid'];

$check = $conn->prepare("SELECT 1 FROM likes WHERE userid=? AND recipeid=?");
$check->bind_param("ii", $user_id, $recipe_id);
$check->execute();

if ($check->get_result()->num_rows === 0) {
    $stmt = $conn->prepare("INSERT INTO likes (userid, recipeid) VALUES (?, ?)");
    $stmt->bind_param("ii", $user_id, $recipe_id);
    $stmt->execute();
    $stmt->close();
}
$check->close();

header("Location: view_recipe.php?id=$recipe_id");
exit();
?>
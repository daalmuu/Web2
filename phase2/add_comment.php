<?php
session_start();
require_once 'db_connect.php';

if (!isset($_SESSION['userID'])) {
    header("Location: index.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_POST['recipe_id']) || empty($_POST['comment_text'])) {
    header("Location: User-dashboard.php");
    exit();
}

$recipe_id = (int)$_POST['recipe_id'];
$user_id = (int)$_SESSION['userID'];
$comment_text = trim($_POST['comment_text']);

if ($comment_text === '') {
    header("Location: view_recipe.php?id=$recipe_id");
    exit();
}

$stmt = $conn->prepare("INSERT INTO comment (recipeid, userid, comment, date) VALUES (?, ?, ?, NOW())");
$stmt->bind_param("iis", $recipe_id, $user_id, $comment_text);
$stmt->execute();
$stmt->close();

header("Location: view_recipe.php?id=$recipe_id");
exit();
?>

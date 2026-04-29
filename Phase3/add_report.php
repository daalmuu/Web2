<?php
include('session.php');
include('DB.php');

$is_ajax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) &&
           strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

if (empty($_GET['recipe_id'])) {
    if ($is_ajax) {
        echo "false";
        exit();
    }
    header("Location: User-dashboard.php");
    exit();
}

$recipe_id = (int)$_GET['recipe_id'];
$user_id   = (int)$_SESSION['userid'];
$success   = false;

$check = $conn->prepare("SELECT 1 FROM report WHERE userid=? AND recipeid=?");
$check->bind_param("ii", $user_id, $recipe_id);
$check->execute();

if ($check->get_result()->num_rows === 0) {
    $stmt = $conn->prepare("INSERT INTO report (userid, recipeid) VALUES (?, ?)");
    $stmt->bind_param("ii", $user_id, $recipe_id);
    $success = $stmt->execute();
    $stmt->close();
} else {
    $success = true;
}
$check->close();

if ($is_ajax) {
    echo $success ? "true" : "false";
    exit();
}

header("Location: view_recipe.php?id=$recipe_id");
exit();
?>
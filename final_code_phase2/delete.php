<?php
require_once("session.php");
require_once("DB.php");

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: my-recipe.php");
    exit();
}

$recipeID = intval($_GET['id']);
$userID   = $_SESSION['userid'];

$stmt = $conn->prepare("SELECT photofilename, videofilepath FROM recipe WHERE id = ? AND userid = ?");
$stmt->bind_param("ii", $recipeID, $userID);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: my-recipe.php");
    exit();
}

$recipe = $result->fetch_assoc();
$stmt->close();

$conn->query("DELETE FROM ingredients  WHERE recipeid = $recipeID");
$conn->query("DELETE FROM instructions WHERE recipeid = $recipeID");
$conn->query("DELETE FROM comment      WHERE recipeid = $recipeID");
$conn->query("DELETE FROM likes        WHERE recipeid = $recipeID");
$conn->query("DELETE FROM favourites   WHERE recipeid = $recipeID");
$conn->query("DELETE FROM report       WHERE recipeid = $recipeID");
$conn->query("DELETE FROM recipe       WHERE id       = $recipeID");

if (!empty($recipe['photofilename'])) {
    $photoPath = "uploads/" . $recipe['photofilename'];
    if (file_exists($photoPath)) {
        unlink($photoPath);
    }
}

if (!empty($recipe['videofilepath'])) {
    $videoPath = "uploads/" .$recipe['videofilepath'];
    if (file_exists($videoPath)) {
        unlink($videoPath);
    }
}

header("Location: my-recipe.php");
exit();
?>

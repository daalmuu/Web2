<?php
include("session.php");
include("DB.php");

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: my-recipes.php");
    exit();
}

$recipeID = intval($_GET['id']);
$userID   = $_SESSION['userID'];

$stmt = $conn->prepare("SELECT photoFileName, videoFilePath FROM Recipe WHERE id = ? AND userID = ?");
$stmt->bind_param("ii", $recipeID, $userID);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: my-recipes.php");
    exit();
}

$recipe = $result->fetch_assoc();
$stmt->close();

$conn->query("DELETE FROM Ingredients  WHERE recipeID = $recipeID");
$conn->query("DELETE FROM Instructions WHERE recipeID = $recipeID");
$conn->query("DELETE FROM Comment      WHERE recipeID = $recipeID");
$conn->query("DELETE FROM Likes        WHERE recipeID = $recipeID");
$conn->query("DELETE FROM Favourites   WHERE recipeID = $recipeID");
$conn->query("DELETE FROM Report       WHERE recipeID = $recipeID");
$conn->query("DELETE FROM Recipe       WHERE id       = $recipeID");

if (!empty($recipe['photoFileName'])) {
    $photoPath = "images/" . $recipe['photoFileName'];
    if (file_exists($photoPath)) {
        unlink($photoPath);
    }
}

if (!empty($recipe['videoFilePath'])) {
    $videoPath = $recipe['videoFilePath'];
    if (file_exists($videoPath)) {
        unlink($videoPath);
    }
}

header("Location: my-recipes.php");
exit();
?>
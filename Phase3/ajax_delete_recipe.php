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

// Verify ownership
$stmt = $conn->prepare("SELECT photofilename, videofilepath FROM recipe WHERE id = ? AND userid = ?");
$stmt->bind_param("ii", $recipeID, $userID);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "false";
    exit();
}

$recipe = $result->fetch_assoc();
$stmt->close();

// Delete all associated data
$conn->query("DELETE FROM ingredients  WHERE recipeid = $recipeID");
$conn->query("DELETE FROM instructions WHERE recipeid = $recipeID");
$conn->query("DELETE FROM comment      WHERE recipeid = $recipeID");
$conn->query("DELETE FROM likes        WHERE recipeid = $recipeID");
$conn->query("DELETE FROM favourites   WHERE recipeid = $recipeID");
$conn->query("DELETE FROM report       WHERE recipeid = $recipeID");
$conn->query("DELETE FROM recipe       WHERE id       = $recipeID");

// Delete associated files
if (!empty($recipe['photofilename'])) {
    $photoPath = "uploads/" . $recipe['photofilename'];
    if (file_exists($photoPath)) {
        unlink($photoPath);
    }
}

if (!empty($recipe['videofilepath'])) {
    $videoPath = "uploads/" . $recipe['videofilepath'];
    if (file_exists($videoPath)) {
        unlink($videoPath);
    }
}

echo "true";
?>

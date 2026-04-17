<?php
require_once "admin_session.php";
require_once "DB.php";

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: admin.php");
    exit();
}

$reportid  = isset($_POST['reportid']) ? (int) $_POST['reportid'] : 0;
$recipeid  = isset($_POST['recipeid']) ? (int) $_POST['recipeid'] : 0;
$creatorid = isset($_POST['creatorid']) ? (int) $_POST['creatorid'] : 0;
$action    = isset($_POST['action']) ? trim($_POST['action']) : '';

if ($reportid <= 0 || $recipeid <= 0 || $creatorid <= 0 || !in_array($action, ['block', 'dismiss'])) {
    header("Location: admin.php?error=Invalid+request");
    exit();
}

    // If the action is dismiss simply delete the report and go back.
if ($action === 'dismiss') {
    $sqlDeleteReport = "DELETE FROM report WHERE id = ?";
    $stmtDeleteReport = mysqli_prepare($conn, $sqlDeleteReport);
    mysqli_stmt_bind_param($stmtDeleteReport, "i", $reportid);
    mysqli_stmt_execute($stmtDeleteReport);
    mysqli_stmt_close($stmtDeleteReport);

    header("Location: admin.php?success=Report+dismissed");
    exit();
}

mysqli_begin_transaction($conn);

try {
    // Retrieve user data
    $sqlUser = "SELECT id, firstname, lastname, emailaddress, usertype
                FROM user
                WHERE id = ?";
    $stmtUser = mysqli_prepare($conn, $sqlUser);
    mysqli_stmt_bind_param($stmtUser, "i", $creatorid);
    mysqli_stmt_execute($stmtUser);
    $resultUser = mysqli_stmt_get_result($stmtUser);
    $userData = mysqli_fetch_assoc($resultUser);
    mysqli_stmt_close($stmtUser);

    if (!$userData) {
        throw new Exception("User not found.");
    }

    // Prevent admin ban
    if ($userData['usertype'] !== 'user') {
        throw new Exception("You cannot block an admin.");
    }

    // Retrieve all user recipes so we can delete associated files
    $sqlRecipes = "SELECT id, photofilename, videofilepath
                   FROM recipe
                   WHERE userid = ?";
    $stmtRecipes = mysqli_prepare($conn, $sqlRecipes);
    mysqli_stmt_bind_param($stmtRecipes, "i", $creatorid);
    mysqli_stmt_execute($stmtRecipes);
    $resultRecipes = mysqli_stmt_get_result($stmtRecipes);

    $recipes = [];
    while ($row = mysqli_fetch_assoc($resultRecipes)) {
        $recipes[] = $row;
    }
    mysqli_stmt_close($stmtRecipes);

    // delete all the data associated with each recipe owned by this user
    $sqlDeleteIngredients = "DELETE FROM ingredients WHERE recipeid = ?";
    $stmtDeleteIngredients = mysqli_prepare($conn, $sqlDeleteIngredients);

    $sqlDeleteInstructions = "DELETE FROM instructions WHERE recipeid = ?";
    $stmtDeleteInstructions = mysqli_prepare($conn, $sqlDeleteInstructions);

    $sqlDeleteCommentsByRecipe = "DELETE FROM comment WHERE recipeid = ?";
    $stmtDeleteCommentsByRecipe = mysqli_prepare($conn, $sqlDeleteCommentsByRecipe);

    $sqlDeleteLikesByRecipe = "DELETE FROM likes WHERE recipeid = ?";
    $stmtDeleteLikesByRecipe = mysqli_prepare($conn, $sqlDeleteLikesByRecipe);

    $sqlDeleteFavouritesByRecipe = "DELETE FROM favourites WHERE recipeid = ?";
    $stmtDeleteFavouritesByRecipe = mysqli_prepare($conn, $sqlDeleteFavouritesByRecipe);

    $sqlDeleteReportsByRecipe = "DELETE FROM report WHERE recipeid = ?";
    $stmtDeleteReportsByRecipe = mysqli_prepare($conn, $sqlDeleteReportsByRecipe);

    $sqlDeleteRecipe = "DELETE FROM recipe WHERE id = ?";
    $stmtDeleteRecipe = mysqli_prepare($conn, $sqlDeleteRecipe);

    foreach ($recipes as $recipe) {
        $singleRecipeID = (int) $recipe['id'];

        mysqli_stmt_bind_param($stmtDeleteIngredients, "i", $singleRecipeID);
        mysqli_stmt_execute($stmtDeleteIngredients);

        mysqli_stmt_bind_param($stmtDeleteInstructions, "i", $singleRecipeID);
        mysqli_stmt_execute($stmtDeleteInstructions);

        mysqli_stmt_bind_param($stmtDeleteCommentsByRecipe, "i", $singleRecipeID);
        mysqli_stmt_execute($stmtDeleteCommentsByRecipe);

        mysqli_stmt_bind_param($stmtDeleteLikesByRecipe, "i", $singleRecipeID);
        mysqli_stmt_execute($stmtDeleteLikesByRecipe);

        mysqli_stmt_bind_param($stmtDeleteFavouritesByRecipe, "i", $singleRecipeID);
        mysqli_stmt_execute($stmtDeleteFavouritesByRecipe);

        mysqli_stmt_bind_param($stmtDeleteReportsByRecipe, "i", $singleRecipeID);
        mysqli_stmt_execute($stmtDeleteReportsByRecipe);

        mysqli_stmt_bind_param($stmtDeleteRecipe, "i", $singleRecipeID);
        mysqli_stmt_execute($stmtDeleteRecipe);
    }

    mysqli_stmt_close($stmtDeleteIngredients);
    mysqli_stmt_close($stmtDeleteInstructions);
    mysqli_stmt_close($stmtDeleteCommentsByRecipe);
    mysqli_stmt_close($stmtDeleteLikesByRecipe);
    mysqli_stmt_close($stmtDeleteFavouritesByRecipe);
    mysqli_stmt_close($stmtDeleteReportsByRecipe);
    mysqli_stmt_close($stmtDeleteRecipe);

    //  delete all the data related to the user
    $sqlDeleteUserComments = "DELETE FROM comment WHERE userid = ?";
    $stmtDeleteUserComments = mysqli_prepare($conn, $sqlDeleteUserComments);
    mysqli_stmt_bind_param($stmtDeleteUserComments, "i", $creatorid);
    mysqli_stmt_execute($stmtDeleteUserComments);
    mysqli_stmt_close($stmtDeleteUserComments);

    $sqlDeleteUserLikes = "DELETE FROM likes WHERE userid = ?";
    $stmtDeleteUserLikes = mysqli_prepare($conn, $sqlDeleteUserLikes);
    mysqli_stmt_bind_param($stmtDeleteUserLikes, "i", $creatorid);
    mysqli_stmt_execute($stmtDeleteUserLikes);
    mysqli_stmt_close($stmtDeleteUserLikes);

    $sqlDeleteUserFavourites = "DELETE FROM favourites WHERE userid = ?";
    $stmtDeleteUserFavourites = mysqli_prepare($conn, $sqlDeleteUserFavourites);
    mysqli_stmt_bind_param($stmtDeleteUserFavourites, "i", $creatorid);
    mysqli_stmt_execute($stmtDeleteUserFavourites);
    mysqli_stmt_close($stmtDeleteUserFavourites);

    $sqlDeleteUserReports = "DELETE FROM report WHERE userid = ?";
    $stmtDeleteUserReports = mysqli_prepare($conn, $sqlDeleteUserReports);
    mysqli_stmt_bind_param($stmtDeleteUserReports, "i", $creatorid);
    mysqli_stmt_execute($stmtDeleteUserReports);
    mysqli_stmt_close($stmtDeleteUserReports);

    // add the user to the blocked list
    $sqlInsertBlocked = "INSERT INTO blockeduser (firstname, lastname, emailaddress)
                         VALUES (?, ?, ?)";
    $stmtInsertBlocked = mysqli_prepare($conn, $sqlInsertBlocked);
    mysqli_stmt_bind_param(
        $stmtInsertBlocked,
        "sss",
        $userData['firstname'],
        $userData['lastname'],
        $userData['emailaddress']
    );
    mysqli_stmt_execute($stmtInsertBlocked);
    mysqli_stmt_close($stmtInsertBlocked);

    // delete the user from the table
    $sqlDeleteUser = "DELETE FROM user WHERE id = ?";
    $stmtDeleteUser = mysqli_prepare($conn, $sqlDeleteUser);
    mysqli_stmt_bind_param($stmtDeleteUser, "i", $creatorid);
    mysqli_stmt_execute($stmtDeleteUser);
    mysqli_stmt_close($stmtDeleteUser);

    // delete the files after delet it from the database
    foreach ($recipes as $recipe) {
        if (!empty($recipe['photofilename'])) {
            $photoPath = "images/" . $recipe['photofilename'];
            if (file_exists($photoPath)) {
                unlink($photoPath);
            }
        }

        if (!empty($recipe['videofilepath'])) {
            $videoPath = $recipe['videofilepath'];
            if (file_exists($videoPath)) {
                unlink($videoPath);
            }
        }
    }

    mysqli_commit($conn);

    header("Location: admin.php?success=User+blocked+successfully");
    exit();

} catch (Exception $e) {
    mysqli_rollback($conn);
    header("Location: admin.php?error=Action+failed");
    exit();
}
?>
<?php
include 'db.php';

$userid = 1; 

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: add_recipe.php");
    exit();
}

$name = trim($_POST['name'] ?? '');
$description = trim($_POST['description'] ?? '');
$categoryid = intval($_POST['categoryid'] ?? 0);
$ingredientnames = $_POST['ingredientname'] ?? [];
$ingredientquantities = $_POST['ingredientquantity'] ?? [];
$steps = $_POST['step'] ?? [];

if ($name === ''  || $description === ''  || $categoryid <= 0) {
    die("missing required fields.");
}

$upload_dir = "uploads/";
if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0777, true);
}

if (!isset($_FILES['photo']) || $_FILES['photo']['error'] !== 0) {
    die("recipe photo is required.");
}

$allowed_image_types = ['image/jpeg', 'image/png', 'image/jpg', 'image/gif', 'image/webp'];
$photo_type = mime_content_type($_FILES['photo']['tmp_name']);

if (!in_array($photo_type, $allowed_image_types)) {
    die("invalid photo file type. only image files are allowed.");
}

$photo_name = time() . "_" . basename($_FILES['photo']['name']);
$photo_path = $upload_dir . $photo_name;

if (!move_uploaded_file($_FILES['photo']['tmp_name'], $photo_path)) {
    die("failed to upload photo.");
}

$video_path = null;
if (isset($_FILES['video']) && $_FILES['video']['error'] === 0 && $_FILES['video']['name'] !== '') {
    $allowed_video_types = ['video/mp4', 'video/webm', 'video/ogg', 'video/quicktime'];
    $video_type = mime_content_type($_FILES['video']['tmp_name']);

    if (!in_array($video_type, $allowed_video_types)) {
        die("invalid video file type. only video files are allowed.");
    }

    $video_name = time() . "_" . basename($_FILES['video']['name']);
    $video_path = $upload_dir . $video_name;

    if (!move_uploaded_file($_FILES['video']['tmp_name'], $video_path)) {
        die("failed to upload video.");
    }
}

mysqli_begin_transaction($conn);

try {
    $recipe_sql = "insert into recipe (userid, categoryid, name, description, photofilename, videofilepath)
                   values (?, ?, ?, ?, ?, ?)";
    $recipe_stmt = mysqli_prepare($conn, $recipe_sql);

    if (!$recipe_stmt) {
        throw new Exception(mysqli_error($conn));
    }

    mysqli_stmt_bind_param($recipe_stmt, "iissss", $userid, $categoryid, $name, $description, $photo_path, $video_path);
    mysqli_stmt_execute($recipe_stmt);

    if (mysqli_stmt_affected_rows($recipe_stmt) <= 0) {
        throw new Exception("failed to insert recipe.");
    }

    $recipeid = mysqli_insert_id($conn);

    $ingredient_sql = "insert into ingredients (recipeid, ingredientname, ingredientquantity)
                       values (?, ?, ?)";
    $ingredient_stmt = mysqli_prepare($conn, $ingredient_sql);

    if (!$ingredient_stmt) {
        throw new Exception(mysqli_error($conn));
    }

    for ($i = 0; $i < count($ingredientnames); $i++) {
        $ingredientname = trim($ingredientnames[$i] ?? '');
        $ingredientquantity = trim($ingredientquantities[$i] ?? '');

        if ($ingredientname === '' || $ingredientquantity === '') {
            continue;
        }

        mysqli_stmt_bind_param($ingredient_stmt, "iss", $recipeid, $ingredientname, $ingredientquantity);
        mysqli_stmt_execute($ingredient_stmt);
    }

    $instruction_sql = "insert into instructions (recipeid, step, steporder)
                        values (?, ?, ?)";
    $instruction_stmt = mysqli_prepare($conn, $instruction_sql);

    if (!$instruction_stmt) {
        throw new Exception(mysqli_error($conn));
    }

    $steporder = 1;
    foreach ($steps as $steptext) {
        $steptext = trim($steptext);

        if ($steptext === '') {
            continue;
        }

        mysqli_stmt_bind_param($instruction_stmt, "isi", $recipeid, $steptext, $steporder);
        mysqli_stmt_execute($instruction_stmt);
        $steporder++;
    }

    mysqli_commit($conn);
    header("Location: my_recipes.php");
    exit();

} catch (Exception $e) {
    mysqli_rollback($conn);
    die("error: " . $e->getMessage());
}
?>

<?php
include 'db.php';

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: my_recipes.php");
    exit();
}

$recipeid = intval($_POST['recipeid'] ?? 0);
$name = trim($_POST['name'] ?? '');
$description = trim($_POST['description'] ?? '');
$categoryid = intval($_POST['categoryid'] ?? 0);
$oldphoto = $_POST['oldphoto'] ?? '';
$oldvideo = $_POST['oldvideo'] ?? '';
$ingredientnames = $_POST['ingredientname'] ?? [];
$ingredientquantities = $_POST['ingredientquantity'] ?? [];
$steps = $_POST['step'] ?? [];

if ($recipeid <= 0 || $name === '' || $description === '' || $categoryid <= 0) {
    die("missing required fields.");
}

$upload_dir = "uploads/";
if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0777, true);
}

$photo_path = $oldphoto;
if (isset($_FILES['photo']) && $_FILES['photo']['error'] === 0 && $_FILES['photo']['name'] !== '') {
    $photo_name = time() . "_" . basename($_FILES['photo']['name']);
    $photo_path = $upload_dir . $photo_name;

    if (!move_uploaded_file($_FILES['photo']['tmp_name'], $photo_path)) {
        die("failed to upload new photo.");
    }
}

$video_path = $oldvideo;
if (isset($_FILES['video']) && $_FILES['video']['error'] === 0 && $_FILES['video']['name'] !== '') {
    $video_name = time() . "_" . basename($_FILES['video']['name']);
    $video_path = $upload_dir . $video_name;

    if (!move_uploaded_file($_FILES['video']['tmp_name'], $video_path)) {
        die("failed to upload new video.");
    }
}

mysqli_begin_transaction($conn);

try {
    $update_sql = "update recipe
                   set categoryid = ?, name = ?, description = ?, photofilename = ?, videofilepath = ?
                   where id = ?";
    $update_stmt = mysqli_prepare($conn, $update_sql);

    if (!$update_stmt) {
        throw new Exception(mysqli_error($conn));
    }

    mysqli_stmt_bind_param($update_stmt, "issssi", $categoryid, $name, $description, $photo_path, $video_path, $recipeid);
    mysqli_stmt_execute($update_stmt);

    $delete_ingredients_sql = "delete from ingredients where recipeid = ?";
    $delete_ingredients_stmt = mysqli_prepare($conn, $delete_ingredients_sql);

    if (!$delete_ingredients_stmt) {
        throw new Exception(mysqli_error($conn));
    }

    mysqli_stmt_bind_param($delete_ingredients_stmt, "i", $recipeid);
    mysqli_stmt_execute($delete_ingredients_stmt);

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

    $delete_instructions_sql = "delete from instructions where recipeid = ?";
    $delete_instructions_stmt = mysqli_prepare($conn, $delete_instructions_sql);

    if (!$delete_instructions_stmt) {
        throw new Exception(mysqli_error($conn));
    }

    mysqli_stmt_bind_param($delete_instructions_stmt, "i", $recipeid);
    mysqli_stmt_execute($delete_instructions_stmt);

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

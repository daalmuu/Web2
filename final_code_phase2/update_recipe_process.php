<?php
include('session.php');
include('DB.php');

if ($_SESSION['usertype'] != "user") {
    header("Location: login.php?error=Access+denied");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: my-recipe.php");
    exit();
}

$recipeid             = intval($_POST['recipeid'] ?? 0);
$name                 = trim($_POST['name'] ?? '');
$description          = trim($_POST['description'] ?? '');
$categoryid           = intval($_POST['categoryid'] ?? 0);
$oldphoto             = $_POST['oldphoto'] ?? '';
$oldvideo             = $_POST['oldvideo'] ?? '';
$ingredientnames      = $_POST['ingredientname'] ?? [];
$ingredientquantities = $_POST['ingredientquantity'] ?? [];
$steps                = $_POST['step'] ?? [];

$errors     = [];
$upload_dir = "uploads/";

if ($recipeid <= 0)      $errors[] = "Invalid recipe ID.";
if ($name === '')        $errors[] = "Recipe name is required.";
if ($description === '') $errors[] = "Description is required.";
if ($categoryid <= 0)    $errors[] = "Please select a category.";

/* ====== PHOTO VALIDATION ====== */
$photo_path = $oldphoto;
if (isset($_FILES['photo']) && $_FILES['photo']['error'] === 0) {
    $check = @getimagesize($_FILES['photo']['tmp_name']);
    if ($check === false) {
        $errors[] = "Invalid photo. Please upload a real image file.";
    } else {
        $filename_only = $recipeid . "_" . time() . "_" . basename($_FILES['photo']['name']);
        if (move_uploaded_file($_FILES['photo']['tmp_name'], $upload_dir . $filename_only)) {
            $photo_path = $filename_only;
        }
    }
}

/* ====== VIDEO VALIDATION ====== */
$video_path = $oldvideo;
if (isset($_FILES['video']) && $_FILES['video']['error'] === 0 && $_FILES['video']['name'] !== '') {
    $tmp_video      = $_FILES['video']['tmp_name'];
    $video_ext      = strtolower(pathinfo($_FILES['video']['name'], PATHINFO_EXTENSION));
    $allowed_videos = ['mp4', 'webm', 'ogg', 'mov'];

    $is_image = @getimagesize($tmp_video);
    if ($is_image !== false) {
        $errors[] = "Security Error: The video field must contain a video file, not an image.";
    } elseif (!in_array($video_ext, $allowed_videos)) {
        $errors[] = "Invalid video extension. Supported: " . implode(', ', $allowed_videos);
    } else {
        $new_video_name = time() . "_" . basename($_FILES['video']['name']);
        if (move_uploaded_file($tmp_video, $upload_dir . $new_video_name)) {
            $video_path = $new_video_name;
        } else {
            $errors[] = "Failed to move uploaded video.";
        }
    }
}

if (
    (!isset($_FILES['video']) || $_FILES['video']['error'] == 4) &&
    !empty($_POST['video_url']) &&
    filter_var($_POST['video_url'], FILTER_VALIDATE_URL)
) {
    $url             = trim($_POST['video_url']);
    $parsed          = parse_url($url);
    $host            = $parsed['host'] ?? '';
    $valid_extension = preg_match('/\.(mp4|webm|mov|avi)$/i', $url);

    if ($valid_extension || preg_match('/youtube\.com|youtu\.be|vimeo\.com/i', $host)) {
        $video_path = $url;
    } else {
        $errors[] = "Invalid video URL — must be from YouTube, Vimeo, or a direct .mp4/.webm/.mov/.avi link.";
    }
}

if (
    isset($_POST['video_url']) &&
    trim($_POST['video_url']) === '' &&
    (empty($_FILES['video']['name']) || $_FILES['video']['error'] == 4)
) {
    $video_path = '';
    if (!empty($oldvideo) && file_exists($upload_dir . $oldvideo)) {
        @unlink($upload_dir . $oldvideo);
    }
}

/* ====== SHOW ERRORS ====== */
if (!empty($errors)) {
    echo "<div style='color:#721c24;background:#f8d7da;border:1px solid #f5c6cb;padding:20px;margin:20px;font-family:Arial;border-radius:5px;'>";
    echo "<h3>Please fix the following errors:</h3><ul>";
    foreach ($errors as $error) echo "<li>" . htmlspecialchars($error) . "</li>";
    echo "</ul><br><a href='javascript:history.back()' style='color:#721c24;font-weight:bold;'>Go Back and Edit</a></div>";
    exit();
}

/* ====== DATABASE UPDATE ====== */
mysqli_begin_transaction($conn);
try {
    $update_stmt = mysqli_prepare($conn, "UPDATE recipe SET categoryid=?, name=?, description=?, photofilename=?, videofilepath=? WHERE id=?");
    mysqli_stmt_bind_param($update_stmt, "issssi", $categoryid, $name, $description, $photo_path, $video_path, $recipeid);
    mysqli_stmt_execute($update_stmt);

    $del_ing = mysqli_prepare($conn, "DELETE FROM ingredients WHERE recipeid=?");
    mysqli_stmt_bind_param($del_ing, "i", $recipeid);
    mysqli_stmt_execute($del_ing);

    $ins_ing = mysqli_prepare($conn, "INSERT INTO ingredients (recipeid, ingredientname, ingredientquantity) VALUES (?, ?, ?)");
    for ($i = 0; $i < count($ingredientnames); $i++) {
        $iname = trim($ingredientnames[$i] ?? '');
        $iqty  = trim($ingredientquantities[$i] ?? '');
        if ($iname !== '' && $iqty !== '') {
            mysqli_stmt_bind_param($ins_ing, "iss", $recipeid, $iname, $iqty);
            mysqli_stmt_execute($ins_ing);
        }
    }

    $del_ins = mysqli_prepare($conn, "DELETE FROM instructions WHERE recipeid=?");
    mysqli_stmt_bind_param($del_ins, "i", $recipeid);
    mysqli_stmt_execute($del_ins);

    $ins_step = mysqli_prepare($conn, "INSERT INTO instructions (recipeid, step, steporder) VALUES (?, ?, ?)");
    $order = 1;
    foreach ($steps as $steptext) {
        $steptext = trim($steptext);
        if ($steptext !== '') {
            mysqli_stmt_bind_param($ins_step, "isi", $recipeid, $steptext, $order);
            mysqli_stmt_execute($ins_step);
            $order++;
        }
    }

    mysqli_commit($conn);
    header("Location: my-recipe.php");
    exit();

} catch (Exception $e) {
    mysqli_rollback($conn);
    die("Database Error: " . $e->getMessage());
}
?>
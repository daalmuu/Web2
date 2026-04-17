<?php
include('session.php');
include('DB.php');

if ($_SESSION['usertype'] != "user") {
    header("Location: login.php?error=Access+denied");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: add_recipe.php");
    exit();
}

$userid               = (int)$_SESSION['userid'];
$name                 = trim($_POST['name'] ?? '');
$description          = trim($_POST['description'] ?? '');
$categoryid           = intval($_POST['categoryid'] ?? 0);
$ingredientnames      = $_POST['ingredientname'] ?? [];
$ingredientquantities = $_POST['ingredientquantity'] ?? [];
$steps                = $_POST['step'] ?? [];

$errors     = [];
$upload_dir = "uploads/";
if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);

/* ====== STEP 1: VALIDATE EVERYTHING (no uploads yet) ====== */

if ($name === '')        $errors[] = "Recipe name is required.";
if ($description === '') $errors[] = "Description is required.";
if ($categoryid <= 0)    $errors[] = "Please select a valid category.";

/* -- Validate Photo (no upload yet) -- */
$new_photo_ready = false;
if (!isset($_FILES['photo']) || $_FILES['photo']['error'] !== 0) {
    $errors[] = "Recipe photo is required.";
} else {
    $tmp      = $_FILES['photo']['tmp_name'];
    $is_image = @getimagesize($tmp);

    if ($is_image === false) {
        $errors[] = "Invalid photo. Please upload a real image file.";
    } else {
        $allowed_types = [IMAGETYPE_JPEG, IMAGETYPE_PNG, IMAGETYPE_GIF, IMAGETYPE_WEBP];
        if (!in_array($is_image[2], $allowed_types)) {
            $errors[] = "Only JPG, PNG, GIF, WEBP images are allowed.";
        } else {
            $new_photo_ready = true; // صالح، لكن ما حملنا بعد
        }
    }
}

/* -- Validate Video (no upload yet) -- */
$new_video_ready = false;
$video_url_value = '';

if (isset($_FILES['video']) && $_FILES['video']['error'] === 0 && $_FILES['video']['name'] !== '') {
    $tmp_video      = $_FILES['video']['tmp_name'];
    $video_ext      = strtolower(pathinfo($_FILES['video']['name'], PATHINFO_EXTENSION));
    $allowed_videos = ['mp4', 'webm', 'ogg', 'mov'];
    $is_image       = @getimagesize($tmp_video);

    if ($is_image !== false) {
        $errors[] = "Security Error: The video field must contain a video file, not an image.";
    } elseif (!in_array($video_ext, $allowed_videos)) {
        $errors[] = "Invalid video extension. Supported: " . implode(', ', $allowed_videos);
    } else {
        $new_video_ready = true; // صالح، لكن ما حملنا بعد
    }

} elseif (
    (!isset($_FILES['video']) || $_FILES['video']['error'] == 4) &&
    !empty($_POST['video_url'])
) {
    $url = trim($_POST['video_url']);
    if (filter_var($url, FILTER_VALIDATE_URL)) {
        $parsed          = parse_url($url);
        $host            = $parsed['host'] ?? '';
        $valid_extension = preg_match('/\.(mp4|webm|mov|avi)$/i', $url);

        if ($valid_extension || preg_match('/youtube\.com|youtu\.be|vimeo\.com/i', $host)) {
            $video_url_value = $url; // صالح
        } else {
            $errors[] = "Invalid video URL — must be from YouTube, Vimeo, or a direct .mp4/.webm/.mov/.avi link.";
        }
    } else {
        $errors[] = "Invalid video URL format.";
    }
}

/* ====== STEP 2: IF ERRORS, STOP. NO FILES WERE UPLOADED ====== */
if (!empty($errors)) {
    echo "<div style='color:#721c24;background:#f8d7da;border:1px solid #f5c6cb;padding:20px;margin:20px;font-family:Arial;border-radius:5px;'>";
    echo "<h3>Please fix the following errors:</h3><ul>";
    foreach ($errors as $error) echo "<li>" . htmlspecialchars($error) . "</li>";
    echo "</ul><br><a href='javascript:history.back()' style='color:#721c24;font-weight:bold;'>Go Back and Edit</a></div>";
    exit();
}

/* ====== STEP 3: ALL VALID — NOW UPLOAD FILES ====== */

$photo_path = null;
if ($new_photo_ready) {
    $filename_only = $userid . "_" . time() . "_" . basename($_FILES['photo']['name']);
    if (move_uploaded_file($_FILES['photo']['tmp_name'], $upload_dir . $filename_only)) {
        $photo_path = $filename_only;
    }
}

$video_path = null;
if ($new_video_ready) {
    $video_name = time() . "_" . basename($_FILES['video']['name']);
    if (move_uploaded_file($_FILES['video']['tmp_name'], $upload_dir . $video_name)) {
        $video_path = $video_name;
    }
} elseif ($video_url_value !== '') {
    $video_path = $video_url_value;
}

/* ====== STEP 4: DATABASE INSERT ====== */
mysqli_begin_transaction($conn);
try {
    $stmt = mysqli_prepare($conn, "INSERT INTO recipe (userid, categoryid, name, description, photofilename, videofilepath) VALUES (?, ?, ?, ?, ?, ?)");
    mysqli_stmt_bind_param($stmt, "iissss", $userid, $categoryid, $name, $description, $photo_path, $video_path);
    if (!mysqli_stmt_execute($stmt)) throw new Exception("Error inserting recipe: " . mysqli_error($conn));
    $recipeid = mysqli_insert_id($conn);

    $ing_stmt = mysqli_prepare($conn, "INSERT INTO ingredients (recipeid, ingredientname, ingredientquantity) VALUES (?, ?, ?)");
    for ($i = 0; $i < count($ingredientnames); $i++) {
        $iname = trim($ingredientnames[$i] ?? '');
        $iqty  = trim($ingredientquantities[$i] ?? '');
        if ($iname !== '') {
            mysqli_stmt_bind_param($ing_stmt, "iss", $recipeid, $iname, $iqty);
            mysqli_stmt_execute($ing_stmt);
        }
    }

    $ins_stmt = mysqli_prepare($conn, "INSERT INTO instructions (recipeid, step, steporder) VALUES (?, ?, ?)");
    $order = 1;
    foreach ($steps as $steptext) {
        $steptext = trim($steptext);
        if ($steptext !== '') {
            mysqli_stmt_bind_param($ins_stmt, "isi", $recipeid, $steptext, $order);
            mysqli_stmt_execute($ins_stmt);
            $order++;
        }
    }

    mysqli_commit($conn);
    header("Location: my-recipe.php");
    exit();

} catch (Exception $e) {
    mysqli_rollback($conn);
    // لو صار خطأ في DB احذف الملفات اللي حملناها
    if ($photo_path && file_exists($upload_dir . $photo_path)) unlink($upload_dir . $photo_path);
    if ($video_path && !filter_var($video_path, FILTER_VALIDATE_URL) && file_exists($upload_dir . $video_path)) unlink($upload_dir . $video_path);
    die("Database Error: " . $e->getMessage());
}
?>

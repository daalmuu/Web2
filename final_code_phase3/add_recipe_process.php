<?php
session_start(); 
require_once 'db.php'; 

$userid = $_SESSION['userid'] ?? 1; 
$errors = []; 

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: add_recipe.php");
    exit();
}

/* ====== HELPER FUNCTION ====== */
function get_mime_type($filepath, $original_name = '') {
    if (function_exists('finfo_open')) {
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $filepath);
        finfo_close($finfo);
        return $mime;
    }
    $image_info = @getimagesize($filepath);
    if ($image_info && isset($image_info['mime'])) {
        return $image_info['mime'];
    }
    $ext = strtolower(pathinfo($original_name ?: $filepath, PATHINFO_EXTENSION));
    $map = [
        'jpg'  => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'png'  => 'image/png',
        'gif'  => 'image/gif',
        'webp' => 'image/webp',
        'mp4'  => 'video/mp4',
        'webm' => 'video/webm',
        'ogg'  => 'video/ogg',
        'mov'  => 'video/quicktime',
    ];
    return $map[$ext] ?? 'application/octet-stream';
}

/* ====== 1. DATA COLLECTION & TEXT VALIDATION ====== */
$name = trim($_POST['name'] ?? '');
$description = trim($_POST['description'] ?? '');
$categoryid = intval($_POST['categoryid'] ?? 0);
$ingredientnames = $_POST['ingredientname'] ?? [];
$ingredientquantities = $_POST['ingredientquantity'] ?? [];
$steps = $_POST['step'] ?? [];

if ($name === '') $errors[] = "Recipe name is required.";
if ($description === '') $errors[] = "Description is required.";
if ($categoryid <= 0) $errors[] = "Please select a valid category.";

$upload_dir = "uploads/";
if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0777, true);
}

/* ====== 2. PHOTO VALIDATION ONLY (NO UPLOAD YET) ====== */
if (!isset($_FILES['photo']) || $_FILES['photo']['error'] !== 0) {
    $errors[] = "Recipe photo is required.";
} else {
    $photo_mime = get_mime_type($_FILES['photo']['tmp_name'], $_FILES['photo']['name']);
    $allowed_images = ['image/jpeg', 'image/png', 'image/jpg', 'image/gif', 'image/webp'];
    if (!in_array($photo_mime, $allowed_images)) {
        $errors[] = "Invalid photo type. Detected: " . $photo_mime . ". Please upload an image.";
    }
}

/* ====== 3. VIDEO VALIDATION ONLY  ====== */
$video_url_value = null;

if (isset($_FILES['video']) && $_FILES['video']['error'] === 0 && $_FILES['video']['name'] !== '') {
    $tmp_video = $_FILES['video']['tmp_name'];
    $video_ext = strtolower(pathinfo($_FILES['video']['name'], PATHINFO_EXTENSION));
    $video_mime = get_mime_type($tmp_video, $_FILES['video']['name']);
    $is_image = @getimagesize($tmp_video);
    $allowed_videos = ['mp4', 'webm', 'ogg', 'mov'];

    if (strpos($video_mime, 'video/') !== 0 || $is_image !== false) {
        $errors[] = "Security Error: The video field must contain a video file, not an image.";
    } elseif (!in_array($video_ext, $allowed_videos)) {
        $errors[] = "Invalid video extension. Supported: " . implode(', ', $allowed_videos);
    }
} elseif (
    (!isset($_FILES['video']) || $_FILES['video']['error'] == 4) &&
    !empty($_POST['video_url']) &&
    filter_var($_POST['video_url'], FILTER_VALIDATE_URL)
) {
    $url = trim($_POST['video_url']);
    $parsed = parse_url($url);
    $host = $parsed['host'] ?? '';
    $valid_extension = preg_match('/\.(mp4|webm|mov|avi)$/i', $url);

    if ($valid_extension || preg_match('/youtube\.com|youtu\.be|vimeo\.com/i', $host)) {
        $video_url_value = $url;
    } else {
        $errors[] = "Invalid video URL — must be from YouTube, Vimeo, or a direct .mp4/.webm/.mov/.avi link.";
    }
}

/* ====== 4. DISPLAY ALL ERRORS IF ANY ====== */
if (!empty($errors)) {
    echo "<div style='color: #721c24; background-color: #f8d7da; border: 1px solid #f5c6cb; padding: 20px; margin: 20px; font-family: Arial, sans-serif; border-radius: 5px;'>";
    echo "<h3>Please fix the following errors:</h3>";
    echo "<ul>";
    foreach ($errors as $error) {
        echo "<li>" . htmlspecialchars($error) . "</li>";
    }
    echo "</ul>";
    echo "<br><a href='javascript:history.back()' style='color: #721c24; font-weight: bold;'>Go Back and Edit</a>";
    echo "</div>";
    exit();
}

/* ====== 5. ALL VALID ====== */
$photo_path = null;
$video_path = null;

$filename_only = "temp_" . time() . "_" . basename($_FILES['photo']['name']);
$full_upload_path = $upload_dir . $filename_only;
if (move_uploaded_file($_FILES['photo']['tmp_name'], $full_upload_path)) {
    $photo_path = $filename_only;
} else {
    die("Failed to upload photo.");
}

if (isset($_FILES['video']) && $_FILES['video']['error'] === 0 && $_FILES['video']['name'] !== '') {
    $video_name = "temp_" . time() . "_" . basename($_FILES['video']['name']);
    $full_video_destination = $upload_dir . $video_name;
    if (move_uploaded_file($_FILES['video']['tmp_name'], $full_video_destination)) {
        $video_path = $video_name;
    } else {
        die("Failed to upload video.");
    }
} elseif ($video_url_value) {
    $video_path = $video_url_value;
}

/* ====== 6. DATABASE PROCESSING ====== */
mysqli_begin_transaction($conn);

try {
    $recipe_sql = "INSERT INTO recipe (userid, categoryid, name, description, photofilename, videofilepath)  
                   VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = mysqli_prepare($conn, $recipe_sql);
    mysqli_stmt_bind_param($stmt, "iissss", $userid, $categoryid, $name, $description, $photo_path, $video_path);
    
    if (!mysqli_stmt_execute($stmt)) {
        throw new Exception("Error inserting recipe: " . mysqli_error($conn));
    }
    
    $recipeid = mysqli_insert_id($conn);

    if ($photo_path) {
        $clean_photo_name = preg_replace('/^(temp_\d+_|recipe_\d+_)+/', '', basename($photo_path));
        $new_photo_name = "recipe_" . $recipeid . "_" . $clean_photo_name;
        if (rename($upload_dir . $photo_path, $upload_dir . $new_photo_name)) {
            $photo_path = $new_photo_name;
        }
    }

    if ($video_path && !filter_var($video_path, FILTER_VALIDATE_URL)) {
        $clean_video_name = preg_replace('/^(temp_\d+_|recipe_\d+_)+/', '', basename($video_path));
        $new_video_name = "recipe_" . $recipeid . "_" . $clean_video_name;
        if (rename($upload_dir . $video_path, $upload_dir . $new_video_name)) {
            $video_path = $new_video_name;
        }
    }

    $update_sql = "UPDATE recipe SET photofilename = ?, videofilepath = ? WHERE id = ?";
    $update_stmt = mysqli_prepare($conn, $update_sql);
    mysqli_stmt_bind_param($update_stmt, "ssi", $photo_path, $video_path, $recipeid);
    mysqli_stmt_execute($update_stmt);

    // Ingredients insertion
    $ing_sql = "INSERT INTO ingredients (recipeid, ingredientname, ingredientquantity) VALUES (?, ?, ?)";
    $ing_stmt = mysqli_prepare($conn, $ing_sql);
    for ($i = 0; $i < count($ingredientnames); $i++) {
        $iname = trim($ingredientnames[$i] ?? '');
        $iqty = trim($ingredientquantities[$i] ?? '');
        if ($iname !== '') {
            mysqli_stmt_bind_param($ing_stmt, "iss", $recipeid, $iname, $iqty);
            mysqli_stmt_execute($ing_stmt);
        }
    }

    // Instructions insertion
    $ins_sql = "INSERT INTO instructions (recipeid, step, steporder) VALUES (?, ?, ?)";
    $ins_stmt = mysqli_prepare($conn, $ins_sql);
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
    if ($photo_path && file_exists($upload_dir . $photo_path)) unlink($upload_dir . $photo_path);
    if ($video_path && !filter_var($video_path, FILTER_VALIDATE_URL) && file_exists($upload_dir . $video_path)) unlink($upload_dir . $video_path);
    die("Database Error: " . $e->getMessage());
}
?>
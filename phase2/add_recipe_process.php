<?php
session_start(); 
include 'db.php'; 

$userid = $_SESSION['userid']?? 1; 

$errors = []; 


if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: add_recipe.php");
    exit();
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

/* ====== 2. PHOTO VALIDATION ====== */
$photo_path = null;
if (!isset($_FILES['photo']) || $_FILES['photo']['error'] !== 0) {
    $errors[] = "Recipe photo is required.";
} else {
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $photo_mime = finfo_file($finfo, $_FILES['photo']['tmp_name']);
    finfo_close($finfo);

    $allowed_images = ['image/jpeg', 'image/png', 'image/jpg', 'image/gif', 'image/webp'];
    if (!in_array($photo_mime, $allowed_images)) {
        $errors[] = "Invalid photo type. Detected: " . $photo_mime . ". Please upload an image.";
    } else {
        /*$photo_name = time() . "_" . basename($_FILES['photo']['name']);
        $photo_path = $upload_dir . $photo_name;*/
    $current_user = $_SESSION['userid'] ?? 1;    
 
 $filename_only = $current_user . "_" . time() . "_" . basename($_FILES['photo']['name']);
$target_directory = "uploads/";
$full_upload_path = $target_directory . $filename_only;

if (move_uploaded_file($_FILES['photo']['tmp_name'], $full_upload_path)) {
    
    $photo_path = $filename_only; 
}    
    }
}


/* ====== 3. VIDEO VALIDATION (STRICT) ====== */
$video_path = null;
if (isset($_FILES['video']) && $_FILES['video']['error'] === 0 && $_FILES['video']['name'] !== '') {
    $tmp_video = $_FILES['video']['tmp_name'];
    $video_ext = strtolower(pathinfo($_FILES['video']['name'], PATHINFO_EXTENSION));

    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $video_mime = finfo_file($finfo, $tmp_video);
    finfo_close($finfo);

    // Check if it's actually an image pretending to be a video
    $is_image = @getimagesize($tmp_video);
    $allowed_videos = ['mp4', 'webm', 'ogg', 'mov'];

    if (strpos($video_mime, 'video/') !== 0 || $is_image !== false) {
        $errors[] = "Security Error: The video field must contain a video file, not an image.";
    } elseif (!in_array($video_ext, $allowed_videos)) {
        $errors[] = "Invalid video extension. Supported: " . implode(', ', $allowed_videos);
    } else {
       $video_name = time() . "_" . basename($_FILES['video']['name']);
        
    $full_video_destination = $upload_dir . $video_name;
    
        if (move_uploaded_file($tmp_video, $full_video_destination)) {
            $video_path = $video_name; // هنا المهم: نخزن الاسم فقط للداتابيز (name.mp4)
        } else {
            $errors[] = "Failed to upload video file.";
        }
    }
    
   
}

// ====== دعم خيار إدخال رابط فيديو بدلاً من رفع ملف ======
if (
    (!isset($_FILES['video']) || $_FILES['video']['error'] == 4) &&
    !empty($_POST['video_url']) &&
    filter_var($_POST['video_url'], FILTER_VALIDATE_URL)
) {
    $url = trim($_POST['video_url']);
    $allowed_hosts = ['youtube.com', 'youtu.be', 'vimeo.com', 'www.youtube.com', 'www.youtu.be', 'www.vimeo.com'];
    $valid_extension = preg_match('/\.(mp4|webm|mov|avi)$/i', $url);
    $parsed = parse_url($url);
    $host = $parsed['host'] ?? '';

    if ($valid_extension || preg_match('/youtube\.com|youtu\.be|vimeo\.com/i', $host)) {
        $video_path = $url; // نحفظ الرابط نفسه في قاعدة البيانات
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
    exit(); // Stop execution here
}


    /* ====== 5. DATABASE PROCESSING (No errors found) ====== */
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
    // Cleanup uploaded files on DB failure
    if (file_exists($photo_path)) unlink($photo_path);
    if ($video_path && file_exists($video_path)) unlink($video_path);
    die("Database Error: " . $e->getMessage());
}
?>

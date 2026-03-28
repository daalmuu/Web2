<?php
include 'DB.php';

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: my-recipe.php");
    exit();
}





// 1. استلام البيانات
$recipeid = intval($_POST['recipeid'] ?? 0);
$name = trim($_POST['name'] ?? '');
$description = trim($_POST['description'] ?? '');
$categoryid = intval($_POST['categoryid'] ?? 0);
$oldphoto = $_POST['oldphoto'] ?? '';
$oldvideo = $_POST['oldvideo'] ?? '';
$ingredientnames = $_POST['ingredientname'] ?? [];
$ingredientquantities = $_POST['ingredientquantity'] ?? [];
$steps = $_POST['step'] ?? [];

$errors = []; // مصفوفة جمع الأخطاء
$upload_dir = "uploads/";

// فحص الحقول الأساسية
if ($recipeid <= 0) $errors[] = "Invalid recipe ID.";
if ($name === '') $errors[] = "Recipe name is required.";
if ($description === '') $errors[] = "Description is required.";
if ($categoryid <= 0) $errors[] = "Please select a category.";

/* ====== 2. فحص الصورة (PHOTO VALIDATION) ====== */
$photo_path = $oldphoto; // الافتراضي هو القديم
if (isset($_FILES['photo']) && $_FILES['photo']['error'] === 0) {
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $photo_mime = finfo_file($finfo, $_FILES['photo']['tmp_name']);
    finfo_close($finfo);

    $allowed_images = ['image/jpeg', 'image/png', 'image/jpg', 'image/gif', 'image/webp'];
    if (!in_array($photo_mime, $allowed_images)) {
        $errors[] = "Invalid photo type. Please upload a real image file.";
    } else {
        //$photo_path = time() . "_" . basename($_FILES['photo']['name']);
       
        $filename_only = $recipeid . "_" . time() . "_" . basename($_FILES['photo']['name']);
$photo_path = "uploads/" . $filename_only;

if (move_uploaded_file($_FILES['photo']['tmp_name'], $photo_path)) {
    // هنا السر: نخزن في المتغير اللي بيروح للداتابيز "الاسم فقط"
    $photo_path = $filename_only; 
}
        
        
        
    }
}

/* ====== 3. فحص الفيديو (STRICT VIDEO VALIDATION) ====== */
$video_path = $oldvideo; // الافتراضي هو القديم
if (isset($_FILES['video']) && $_FILES['video']['error'] === 0 && $_FILES['video']['name'] !== '') {
    $tmp_video = $_FILES['video']['tmp_name'];
    
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $video_mime = finfo_file($finfo, $tmp_video);
    finfo_close($finfo);

    // فحص حماية: هل هي صورة متنكرة في شكل فيديو؟
    $is_image = @getimagesize($tmp_video);
    
    if (strpos($video_mime, 'video/') !== 0 || $is_image !== false) {
        $errors[] = "Security Error: The video field must contain a video file, not an image.";
    } else {
        // 1. تجهيز الاسم الجديد
        $new_video_name = time() . "_" . basename($_FILES['video']['name']);
        
        // 2. سطر الرفع (هذا اللي كان ناقصك وهو أهم سطر)
        if (move_uploaded_file($tmp_video, "uploads/" . $new_video_name)) {
            $video_path = $new_video_name; // نحدث المتغير عشان يروح للداتابيز
        } else {
            $errors[] = "Failed to move uploaded video.";
        }
    }
}









/* ====== 4. عرض الأخطاء إن وجدت (التصميم الجميل) ====== */
if (!empty($errors)) {
    echo "<div style='color: #721c24; background-color: #f8d7da; border: 1px solid #f5c6cb; padding: 20px; margin: 20px; font-family: Arial, sans-serif; border-radius: 5px;'>";
    echo "<h3>Please fix the following errors:</h3><ul>";
    foreach ($errors as $error) { echo "<li>" . htmlspecialchars($error) . "</li>"; }
    echo "</ul><br><a href='javascript:history.back()' style='color: #721c24; font-weight: bold;'>Go Back and Edit</a></div>";
    exit();
}

/* ====== 5. تنفيذ الرفع الفعلي للملفات ====== */

if ($video_path !== $oldvideo) {
    if (move_uploaded_file($_FILES['video']['tmp_name'], $video_path)) {
        if (!empty($oldvideo) && file_exists($oldvideo)) @unlink($oldvideo);
    }
}

/* ====== 6. تحديث قاعدة البيانات باستخدام Transaction ====== */
mysqli_begin_transaction($conn);
try {
    $update_sql = "UPDATE recipe SET categoryid = ?, name = ?, description = ?, photofilename = ?, videofilepath = ? WHERE id = ?";
    $update_stmt = mysqli_prepare($conn, $update_sql);
    mysqli_stmt_bind_param($update_stmt, "issssi", $categoryid, $name, $description, $photo_path, $video_path, $recipeid);
    mysqli_stmt_execute($update_stmt);

    // تحديث المكونات (Delete & Insert)
    $del_ing = mysqli_prepare($conn, "DELETE FROM ingredients WHERE recipeid = ?");
    mysqli_stmt_bind_param($del_ing, "i", $recipeid);
    mysqli_stmt_execute($del_ing);

    $ins_ing = mysqli_prepare($conn, "INSERT INTO ingredients (recipeid, ingredientname, ingredientquantity) VALUES (?, ?, ?)");
    for ($i = 0; $i < count($ingredientnames); $i++) {$iname = trim($ingredientnames[$i] ?? '');
        $iqty = trim($ingredientquantities[$i] ?? '');
        if ($iname !== '' && $iqty !== '') {
            mysqli_stmt_bind_param($ins_ing, "iss", $recipeid, $iname, $iqty);
            mysqli_stmt_execute($ins_ing);
        }
    }

    // تحديث الخطوات (Delete & Insert)
    $del_ins = mysqli_prepare($conn, "DELETE FROM instructions WHERE recipeid = ?");
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

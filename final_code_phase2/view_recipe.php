<?php
require_once("session.php");
require_once("DB.php");

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: User-dashboard.php");
    exit();
}

$recipe_id       = (int)$_GET['id'];
$current_user_id = (int)$_SESSION['userid'];
$is_admin        = ($_SESSION['usertype'] === 'admin');

$stmt = $conn->prepare("
    SELECT r.*,
           u.firstname, u.lastname, u.photofilename AS creator_photo, u.id AS creator_id,
           c.categoryname
    FROM recipe r
    JOIN user u ON r.userid = u.id
    JOIN recipecategory c ON r.categoryid = c.id
    WHERE r.id = ?
");
$stmt->bind_param("i", $recipe_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: User-dashboard.php");
    exit();
}
$recipe = $result->fetch_assoc();
$stmt->close();

$is_creator   = ($current_user_id && $current_user_id == $recipe['creator_id']);
$show_buttons = ($current_user_id && !$is_creator && !$is_admin);

$already_favourited = false;
$already_liked      = false;
$already_reported   = false;

if ($show_buttons) {
    $s = $conn->prepare("SELECT 1 FROM favourites WHERE userid=? AND recipeid=?");
    $s->bind_param("ii", $current_user_id, $recipe_id);
    $s->execute();
    $already_favourited = $s->get_result()->num_rows > 0;
    $s->close();

    $s = $conn->prepare("SELECT 1 FROM likes WHERE userid=? AND recipeid=?");
    $s->bind_param("ii", $current_user_id, $recipe_id);
    $s->execute();
    $already_liked = $s->get_result()->num_rows > 0;
    $s->close();

    $s = $conn->prepare("SELECT 1 FROM report WHERE userid=? AND recipeid=?");
    $s->bind_param("ii", $current_user_id, $recipe_id);
    $s->execute();
    $already_reported = $s->get_result()->num_rows > 0;
    $s->close();
}

$stmt = $conn->prepare("SELECT ingredientname, ingredientquantity FROM ingredients WHERE recipeid=?");
$stmt->bind_param("i", $recipe_id);
$stmt->execute();
$ingredients = $stmt->get_result();
$stmt->close();

$stmt = $conn->prepare("SELECT step, steporder FROM instructions WHERE recipeid=? ORDER BY steporder");
$stmt->bind_param("i", $recipe_id);
$stmt->execute();
$instructions = $stmt->get_result();
$stmt->close();

$stmt = $conn->prepare("
    SELECT c.comment, c.date, u.firstname, u.lastname
    FROM comment c
    JOIN user u ON c.userid = u.id
    WHERE c.recipeid = ?
    ORDER BY c.date DESC
");
$stmt->bind_param("i", $recipe_id);
$stmt->execute();
$comments = $stmt->get_result();
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>BellaCucina | <?= htmlspecialchars($recipe['name']) ?></title>
    <link rel="stylesheet" href="main.css">
</head>
<body>

<header>
    <div class="header-container">
 <a href="User-dashboard.php">
            <img src="uploads/logo.png" alt="BellaCucina Logo" class="logo">
        </a>        <nav class="nav-menu">
            <a href="logout.php" class="logout-link">
                <span class="icon">
                    <span class="bracket"></span>
                    <span class="arrow">➜</span>
                </span>
                <span class="text">Sign out</span>
            </a>
        </nav>
    </div>
</header>

<main class="recipe-view-page">

    <div class="recipe-hero">
        <?php if (!empty($recipe['photofilename'])): ?>
            <img src="uploads/<?= htmlspecialchars($recipe['photofilename']) ?>" alt="Recipe Photo">
        <?php else: ?>
            <img src="uploads/default_recipe.jpg" alt="Recipe Photo">
        <?php endif; ?>

        <?php if ($show_buttons): ?>
        <div class="recipe-actions">

            <?php if ($already_favourited): ?>
                <button class="action-btn favourite" disabled style="opacity:0.45; filter:grayscale(60%);">❤ Favourite</button>
            <?php else: ?>
                <a href="add_favourite.php?recipe_id=<?= $recipe_id ?>" class="action-btn favourite">❤ Favourite</a>
            <?php endif; ?>

            <?php if ($already_liked): ?>
                <button class="action-btn like" disabled style="opacity:0.45; filter:grayscale(60%);">👍 Like</button>
            <?php else: ?>
                <a href="add_like.php?recipe_id=<?= $recipe_id ?>" class="action-btn like">👍 Like</a>
            <?php endif; ?>

            <?php if ($already_reported): ?>
                <button class="action-btn report" disabled style="opacity:0.45; filter:grayscale(60%);">🚩 Report</button>
            <?php else: ?>
                <a href="add_report.php?recipe_id=<?= $recipe_id ?>" class="action-btn report">🚩 Report</a>
            <?php endif; ?>

        </div>
        <?php endif; ?>

        <div class="recipe-hero-overlay">
            <h1><?= htmlspecialchars($recipe['name']) ?></h1>
        </div>
    </div>

    <div class="recipe-info-box">
        <div class="creator-section">
            <?php if (!empty($recipe['creator_photo']) && $recipe['creator_photo'] != 'default.png'): ?>
                <img src="uploads/<?= htmlspecialchars($recipe['creator_photo']) ?>" class="chef-photo" alt="Chef">
            <?php else: ?>
                <div class="creator-avatar large initial-avatar">
                    <?= strtoupper(mb_substr($recipe['firstname'], 0, 1)) ?>
                </div>
            <?php endif; ?>
            
            <div>
                <span class="label">Recipe Creator</span>
                <div class="creator-name">
                    <?= htmlspecialchars($recipe['firstname'] . ' ' . $recipe['lastname']) ?>
                </div>
            </div>
        </div>

        <span class="category-badge large"><?= htmlspecialchars($recipe['categoryname']) ?></span>

        <p class="recipe-description">
            <?= htmlspecialchars($recipe['description']) ?>
        </p>
    </div>

    <div class="recipe-details">

        <div class="detail-box">
            <h2>Ingredients</h2>
            <ul class="ingredients-list">
                <?php while ($ing = $ingredients->fetch_assoc()): ?>
                <li>
                    <span class="bullet"></span>
                    <?= htmlspecialchars($ing['ingredientname']) ?> – <?= htmlspecialchars($ing['ingredientquantity']) ?>
                </li>
                <?php endwhile; ?>
            </ul>
        </div>

        <div class="detail-box">
            <h2>Instructions</h2>
            <ul class="instructions-list">
                <?php $step_num = 1; while ($ins = $instructions->fetch_assoc()): ?>
                <li>
                    <span class="step"><?= $step_num++ ?></span>
                    <?= htmlspecialchars($ins['step']) ?>
                </li>
                <?php endwhile; ?>
            </ul>
        </div>

    </div>

    <?php if (!empty($recipe['videofilepath'])): ?>
    <div class="video-box">
        <h2>Recipe Video</h2>
        <div class="video-link-box" onclick="openVideoModal('<?= htmlspecialchars($recipe['videofilepath']) ?>')" style="cursor:pointer;">
            <div class="play-icon">▶</div>
            <div>
                <div class="video-title">Watch recipe preparation</div>
                <div class="video-subtitle">Click to play</div>
            </div>
        </div>
    </div>

    <!-- Video Modal -->
    <div id="videoModal" onclick="closeVideoModal()" style="
        display:none;
        position:fixed;
        top:0; left:0;
        width:100%; height:100%;
        background:rgba(0,0,0,0.75);
        z-index:9999;
        align-items:center;
        justify-content:center;
    ">
        <div style="
            position:relative;
            width:70%;
            max-width:900px;
            background:#000;
            border-radius:12px;
            overflow:hidden;
            box-shadow:0 20px 60px rgba(0,0,0,0.5);
        " onclick="event.stopPropagation()">
            <video id="videoPlayer" controls style="width:100%; display:block; max-height:75vh;">
                <source id="videoSource" src="" type="video/mp4">
                متصفحك لا يدعم تشغيل الفيديو.
            </video>
        </div>
    </div>

    <script>
        function openVideoModal(src) {
            if (src.startsWith('http://') || src.startsWith('https://')) {
                window.open(src, '_blank');
                return;
            }
            const modal  = document.getElementById('videoModal');
            const player = document.getElementById('videoPlayer');
            const source = document.getElementById('videoSource');
            source.src = 'uploads/' + src.replace('uploads/', '');
            player.load();
            modal.style.display = 'flex';
        }

        function closeVideoModal() {
            const modal  = document.getElementById('videoModal');
            const player = document.getElementById('videoPlayer');
            player.pause();
            player.currentTime = 0;
            modal.style.display = 'none';
        }
    </script>
    <?php endif; ?>

    <div class="comments-section">
        <h2>Comments</h2>

        <form class="comment-form" action="add_comment.php" method="POST">
            <input type="hidden" name="recipe_id" value="<?= $recipe_id ?>">
            <input type="text" name="comment_text" placeholder="Write a comment..." required>
            <button type="submit" class="btn-green btn-small">Add Comment</button>
        </form>

        <div class="comments-list">
            <?php if ($comments->num_rows === 0): ?>
                <p class="no-comments">No comments yet. Be the first!</p>
            <?php else: ?>
                <?php while ($c = $comments->fetch_assoc()): ?>
                <div class="comment">
                    <div class="comment-avatar">
                        <?= strtoupper(mb_substr($c['firstname'], 0, 1)) ?>
                    </div>
                    <div class="comment-content">
                        <div class="comment-header">
                            <span class="comment-author"><?= htmlspecialchars($c['firstname'] . ' ' . $c['lastname']) ?></span>
                            <span class="comment-date"><?= htmlspecialchars($c['date']) ?></span>
                        </div>
                        <p><?= htmlspecialchars($c['comment']) ?></p>
                    </div>
                </div>
                <?php endwhile; ?>
            <?php endif; ?>
        </div>
    </div>

</main>

<footer>
    © 2026 BellaCucina. All rights reserved.
</footer>

</body>
</html>

<?php
include("session.php");
include("DB.php");

if ($_SESSION['usertype'] != "user") {
    header("Location: login.php?error=Access+denied");
    exit();
}

$userID = $_SESSION['userid'];
$result = $conn->query("SELECT * FROM recipe WHERE userid = $userID");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>BellaCucina | My Recipes</title>
    <link rel="stylesheet" href="main.css">
</head>
<body>

<header>
    <div class="header-container">
        <a href="User-dashboard.php">
            <img src="uploads/logo.png" alt="BellaCucina Logo" class="logo">
        </a>
        <nav class="nav-menu">
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

<main class="dashboard-page fade-in">

    <div class="welcome-banner">
        <span class="welcome-label">My Kitchen</span>
        <h1>📋 My Recipes</h1>
        <p>Manage and edit your added recipes</p>
    </div>

    <div class="add-recipe-bar">
        <a href="add_recipe.php" class="btn-green">➕ Add New Recipe</a>
    </div>

<?php if ($result->num_rows > 0): ?>

    <section class="table-section">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Recipe</th>
                    <th>Ingredients</th>
                    <th>Instructions</th>
                    <th>Video</th>
                    <th>Likes</th>
                    <th>Edit</th>
                    <th>Delete</th>
                </tr>
            </thead>
            <tbody>

            <?php while($row = $result->fetch_assoc()): ?>

                <?php
                    $recipeID     = $row['id'];
                    $likes        = $conn->query("SELECT COUNT(*) as total FROM likes WHERE recipeid=$recipeID")->fetch_assoc()['total'];
                    $ingredients  = $conn->query("SELECT * FROM ingredients WHERE recipeid=$recipeID");
                    $instructions = $conn->query("SELECT * FROM instructions WHERE recipeid=$recipeID ORDER BY steporder");
                ?>

                <tr>
                    <td>
                        <a href="view_recipe.php?id=<?=$recipeID?>">
                            <div class="recipe-cell">
                                <img src="uploads/<?=$row['photofilename']?>" class="table-img">
                                <span><?=$row['name']?></span>
                            </div>
                        </a>
                    </td>

                    <td>
                        <ul class="small-list">
                            <?php while($ing = $ingredients->fetch_assoc()): ?>
                                <li><?=$ing['ingredientname']?> – <?=$ing['ingredientquantity']?></li>
                            <?php endwhile; ?>
                        </ul>
                    </td>

                    <td>
                        <ol class="inst-list">
                            <?php while($inst = $instructions->fetch_assoc()): ?>
                                <li><?=$inst['step']?></li>
                            <?php endwhile; ?>
                        </ol>
                    </td>

                    <td>
                        <?php if (!empty($row['videofilepath'])): ?>
                            <a class="video-link" onclick="openVideoModal('<?= htmlspecialchars($row['videofilepath']) ?>')" style="cursor:pointer;">
                                Watch Video
                            </a>
                        <?php else: ?>
                            <span class="no-video">No video for recipe</span>
                        <?php endif; ?>
                    </td>

                    <td>
                        <span class="likes-badge">👍 <?=$likes?></span>
                    </td>

                    <td>
                        <a href="update_recipe.php?id=<?=$recipeID?>" class="btn-icon edit">Edit</a>
                    </td>

                    <td>
                        <a href="delete.php?id=<?=$recipeID?>" class="btn-icon delete"
                           onclick="return confirm('Are you sure you want to delete this recipe?')">
                            Delete
                        </a>
                    </td>
                </tr>

            <?php endwhile; ?>

            </tbody>
        </table>
    </section>

<?php else: ?>

    <div class="empty-state-wrapper">
        <div class="empty-state-card">
            <div class="empty-state-icon">🍽️</div>
            <h2>You don't have any recipes yet</h2>
            <p>Start adding your recipes and they'll appear here.</p>
        </div>
    </div>

<?php endif; ?>

</main>

<footer>
    © 2026 BellaCucina. All rights reserved.
</footer>

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

</body>
</html>

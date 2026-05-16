<?php
require_once("session.php");
require_once("DB.php");

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
    <!-- jQuery  -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
</head>
<body>

<header>
    <div class="header-container">
        <a href="User-dashboard.php">
            <img src="uploads/logo.png" alt="BellaCucina Logo" class="logo">
        </a>
    </div>
</header>

<main class="dashboard-page fade-in">

    <div class="welcome-banner">
        <span class="welcome-label">My Kitchen</span>
        <h1>&#x1F4CB; My Recipes</h1>
        <p>Manage and edit your added recipes</p>
    </div>

    <div class="add-recipe-bar">
        <a href="add_recipe.php" class="btn-green">&#x2795; Add New Recipe</a>
    </div>

<?php if ($result->num_rows > 0): ?>

    <section class="table-section">
        <table class="data-table" id="myRecipesTable">
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

                <tr id="recipe-row-<?= $recipeID ?>">
                    <td>
                        <a href="view_recipe.php?id=<?= $recipeID ?>">
                            <div class="recipe-cell">
                                <img src="uploads/<?= htmlspecialchars($row['photofilename']) ?>" class="table-img">
                                <span><?= htmlspecialchars($row['name']) ?></span>
                            </div>
                        </a>
                    </td>

                    <td>
                        <ul class="small-list">
                            <?php while($ing = $ingredients->fetch_assoc()): ?>
                                <li><?= htmlspecialchars($ing['ingredientname']) ?> &ndash; <?= htmlspecialchars($ing['ingredientquantity']) ?></li>
                            <?php endwhile; ?>
                        </ul>
                    </td>

                    <td>
                        <ol class="inst-list">
                            <?php while($inst = $instructions->fetch_assoc()): ?>
                                <li><?= htmlspecialchars($inst['step']) ?></li>
                            <?php endwhile; ?>
                        </ol>
                    </td>

                    <td>
                        <?php if (!empty($row['videofilepath'])): ?>
                            <a class="video-link"
                               data-video="<?= htmlspecialchars($row['videofilepath']) ?>"
                               style="cursor:pointer;">
                                Watch Video
                            </a>
                        <?php else: ?>
                            <span class="no-video">No video for recipe</span>
                        <?php endif; ?>
                    </td>

                    <td>
                        <span class="likes-badge">&#x1F44D; <?= $likes ?></span>
                    </td>

                    <td>
                        <a href="update_recipe.php?id=<?= $recipeID ?>" class="btn-icon edit">Edit</a>
                    </td>

                    <td>
                        <button class="btn-icon delete btn-delete-recipe"
                                data-id="<?= $recipeID ?>"
                                style="border:none;cursor:pointer;font-family:inherit;display:inline-block;">
                            Delete
                        </button>
                    </td>
                </tr>

            <?php endwhile; ?>

            </tbody>
        </table>
    </section>

<?php else: ?>

    <div class="empty-state-wrapper" id="myRecipesEmpty">
        <div class="empty-state-card">
            <div class="empty-state-icon">&#x1F37D;&#xFE0F;</div>
            <h2>You don't have any recipes yet</h2>
            <p>Start adding your recipes and they'll appear here.</p>
        </div>
    </div>

<?php endif; ?>

</main>

<footer>
    &copy; 2026 BellaCucina. All rights reserved.
</footer>

<!-- Video Modal -->
<div id="videoModal" style="
    display:none;
    position:fixed;
    top:0; left:0;
    width:100%; height:100%;
    background:rgba(0,0,0,0.75);
    z-index:9999;
    align-items:center;
    justify-content:center;
">
    <div id="videoModalInner" style="
        position:relative;
        width:70%;
        max-width:900px;
        background:#000;
        border-radius:12px;
        overflow:hidden;
        box-shadow:0 20px 60px rgba(0,0,0,0.5);
    ">
        <video id="videoPlayer" controls style="width:100%; display:block; max-height:75vh;">
            <source id="videoSource" src="" type="video/mp4">
            Your browser does not support video playback.
        </video>
    </div>
</div>

<script>
$(document).ready(function () {

    /* ── Video modal – */
    $(document).on('click', '.video-link', function () {
        var src = $(this).data('video');
        if (src.indexOf('http://') === 0 || src.indexOf('https://') === 0) {
            window.open(src, '_blank');
            return;
        }
        $('#videoSource').attr('src', 'uploads/' + src.replace('uploads/', ''));
        $('#videoPlayer')[0].load();
        $('#videoModal').css('display', 'flex');
    });

    /* ── Video modal –*/
    $('#videoModal').on('click', function () {
        $('#videoPlayer')[0].pause();
        $('#videoPlayer')[0].currentTime = 0;
        $('#videoModal').hide();
    });

    $('#videoModalInner').on('click', function (e) {
        e.stopPropagation();
    });

    /* Delete a recipe  */
    $(document).on('click', '.btn-delete-recipe', function () {
        if (!confirm('Are you sure you want to delete this recipe?')) return;

        var btn      = $(this);
        var recipeID = btn.data('id');
        var row      = btn.closest('tr');

        btn.prop('disabled', true).text('Deleting…');

        $.ajax({
            url: 'ajax_delete_recipe.php',
            type: 'POST',
            data: { recipeID: recipeID },
            success: function (response) {
                if (response.trim() === 'true') {
                    row.fadeOut(400, function () {
                        $(this).remove();
                        if ($('#myRecipesTable tbody tr').length === 0) {
                            $('#myRecipesTable').closest('.table-section').replaceWith(
                                '<div class="empty-state-wrapper" id="myRecipesEmpty">' +
                                '<div class="empty-state-card">' +
                                '<div class="empty-state-icon">&#x1F37D;&#xFE0F;</div>' +
                                '<h2>You don\'t have any recipes yet</h2>' +
                                '<p>Start adding your recipes and they\'ll appear here.</p>' +
                                '</div></div>'
                            );
                        }
                    });
                } else {
                    alert('Could not delete the recipe. Please try again.');
                    btn.prop('disabled', false).text('Delete');
                }
            },
            error: function () {
                alert('An error occurred. Please try again.');
                btn.prop('disabled', false).text('Delete');
            }
        });
    });

});
</script>

</body>
</html>
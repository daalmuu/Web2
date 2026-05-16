<?php
require_once("session.php");
require_once("DB.php");

if ($_SESSION['usertype'] != "user") {
    header("Location: login.php?error=Access+denied");
    exit();
}

$userID = $_SESSION['userid'];

$userStmt = $conn->prepare("SELECT * FROM user WHERE id = ?");
$userStmt->bind_param("i", $userID);
$userStmt->execute();
$user = $userStmt->get_result()->fetch_assoc();
$userStmt->close();

$totalRecipes = $conn->query("SELECT COUNT(*) as total FROM recipe WHERE userid = $userID")->fetch_assoc()['total'];
$totalLikes   = $conn->query("SELECT COUNT(*) as total FROM likes l
                               JOIN recipe r ON l.recipeid = r.id
                               WHERE r.userid = $userID")->fetch_assoc()['total'];

$categoriesResult = $conn->query("SELECT * FROM recipecategory");
$categoriesList = [];
while ($c = $categoriesResult->fetch_assoc()) { $categoriesList[] = $c; }

// Load all recipes initially 
$recipesResult = $conn->query("SELECT r.*, u.firstname, u.lastname,
                                       u.photofilename as userphoto, c.categoryname
                                FROM recipe r
                                JOIN user u ON r.userid = u.id
                                JOIN recipecategory c ON r.categoryid = c.id");
$recipesList = [];
while ($r = $recipesResult->fetch_assoc()) {
    $rid = $r['id'];
    $r['likes'] = $conn->query("SELECT COUNT(*) as total FROM likes WHERE recipeid=$rid")->fetch_assoc()['total'];
    $recipesList[] = $r;
}

$favouritesResult = $conn->query("SELECT r.*, r.photofilename as recipephoto
                                   FROM favourites f
                                   JOIN recipe r ON f.recipeid = r.id
                                   WHERE f.userid = $userID");
$favouritesList = [];
while ($f = $favouritesResult->fetch_assoc()) { $favouritesList[] = $f; }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>BellaCucina | User Dashboard</title>
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
        <span class="welcome-label">Welcome</span>
        <h1><?= htmlspecialchars($user['firstname']) ?> &#x1F44B;</h1>
        <p>Discover and share delicious recipes</p>
    </div>

    <div class="info-cards">

        <div class="info-card">
            <h3>&#x1F464; My Information</h3>
            <div class="user-info">
                <?php if (!empty($user['photofilename']) && $user['photofilename'] != 'default.png'): ?>
                <div class="creator-avatar large">
                    <img src="uploads/<?= htmlspecialchars($user['photofilename']) ?>"
                         class="user-avatar" alt="user photo">
                </div>
                <?php else: ?>
                <div class="creator-avatar large initial-avatar">
                    <?= strtoupper(mb_substr($user['firstname'], 0, 1)) ?>
                </div>
                <?php endif; ?>
                <div>
                    <div class="user-name">
                        <?= htmlspecialchars($user['firstname']) ?>
                        <?= htmlspecialchars($user['lastname']) ?>
                    </div>
                    <div class="user-email">
                        <?= htmlspecialchars($user['emailaddress']) ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="info-card">
            <h3>&#x1F4CA; My Recipes</h3>
            <div class="stat-box green-bg">
                <span>Total Recipes</span>
                <span class="stat-value"><?= $totalRecipes ?></span>
            </div>
            <div class="stat-box red-bg">
                <span>Total Likes</span>
                <span class="stat-value red"><?= $totalLikes ?></span>
            </div>
            <a href="my-recipe.php" class="btn-green btn-full">View My Recipes</a>
        </div>

    </div>

    <!--  ALL RECIPES  -->
    <section class="table-section">
        <div class="table-header">
            <h2>&#x1F37D;&#xFE0F; All Available Recipes</h2>

            <div class="filter-area">
                <select id="categoryFilter">
                    <option value="0">All Categories</option>
                    <?php foreach ($categoriesList as $cat): ?>
                        <option value="<?= $cat['id'] ?>">
                            <?= htmlspecialchars($cat['categoryname']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div id="recipesTableWrapper">
            <?php if (count($recipesList) > 0): ?>
            <table class="data-table" id="recipesTable">
                <thead>
                    <tr>
                        <th>Recipe Name</th>
                        <th>Photo</th>
                        <th>Creator</th>
                        <th>Likes</th>
                        <th>Category</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($recipesList as $row): ?>
                    <tr>
                        <td>
                            <a href="view_recipe.php?id=<?= $row['id'] ?>">
                                <?= htmlspecialchars($row['name']) ?>
                            </a>
                        </td>
                        <td>
                            <img src="uploads/<?= htmlspecialchars($row['photofilename']) ?>" class="table-img">
                        </td>
                        <td>
                            <div class="creator-info">
                                <?php if (!empty($row['userphoto']) && $row['userphoto'] != 'default.png'): ?>
                                <div class="creator-avatar">
                                    <img src="uploads/<?= htmlspecialchars($row['userphoto']) ?>"
                                         class="table-img" alt="Chef">
                                </div>
                                <?php else: ?>
                                <div class="creator-avatar initial-avatar">
                                    <?= strtoupper(mb_substr($row['firstname'], 0, 1)) ?>
                                </div>
                                <?php endif; ?>
                                <span>
                                    <?= htmlspecialchars($row['firstname']) ?>
                                    <?= htmlspecialchars($row['lastname']) ?>
                                </span>
                            </div>
                        </td>
                        <td><span class="likes-badge">&#x1F44D; <?= $row['likes'] ?></span></td>
                        <td><span class="category-badge"><?= htmlspecialchars($row['categoryname']) ?></span></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            <?php else: ?>
            <div class="empty-state-wrapper">
                <div class="empty-state-card">
                    <div class="empty-state-icon">&#x1F50D;</div>
                    <h2>No recipes found</h2>
                    <p>There are no recipes in this category yet.</p>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- ===== FAVOURITES  ===== -->
    <section class="table-section">
        <h2>&#x2764;&#xFE0F; My Favourite Recipes</h2>

        <?php if (count($favouritesList) > 0): ?>
        <table class="data-table" id="favouritesTable">
            <thead>
                <tr>
                    <th>Recipe Name</th>
                    <th>Photo</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($favouritesList as $fav): ?>
                <tr id="fav-row-<?= $fav['id'] ?>">
                    <td>
                        <a href="view_recipe.php?id=<?= $fav['id'] ?>">
                            <?= htmlspecialchars($fav['name']) ?>
                        </a>
                    </td>
                    <td>
                        <img src="uploads/<?= htmlspecialchars($fav['recipephoto']) ?>" class="table-img">
                    </td>
                    <td>
                        <button class="red-link btn-remove-fav"
                                data-id="<?= $fav['id'] ?>"
                                style="background:none;border:none;padding:0;margin:0;cursor:pointer;font-size:inherit;font-family:inherit;font-weight:bold;text-decoration:none;display:inline;">
                            Remove
                        </button>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>

        <?php else: ?>
        <div class="empty-state-wrapper" id="favsEmpty">
            <div class="empty-state-card">
                <div class="empty-state-icon">&#x2764;&#xFE0F;</div>
                <h2>No favourites yet</h2>
                <p>Recipes you favourite will appear here.</p>
            </div>
        </div>
        <?php endif; ?>

    </section>

</main>

<footer>
    &copy; 2026 BellaCucina. All rights reserved.
</footer>

<script>
$(document).ready(function () {

    /* Filter all recipes by category  */
    $('#categoryFilter').on('change', function () {
        var categoryID = $(this).val();

        $.ajax({
            url: 'ajax_filter_recipes.php',
            type: 'POST',
            data: { categoryID: categoryID },
            dataType: 'json',
            beforeSend: function () {
                $('#recipesTableWrapper').html(
                    '<p style="text-align:center;padding:20px;">Loading&#x2026;</p>'
                );
            },
            success: function (recipes) {
                if (recipes.length === 0) {
                    $('#recipesTableWrapper').html(
                        '<div class="empty-state-wrapper"><div class="empty-state-card">' +
                        '<div class="empty-state-icon">&#x1F50D;</div>' +
                        '<h2>No recipes found</h2>' +
                        '<p>There are no recipes in this category yet.</p>' +
                        '</div></div>'
                    );
                    return;
                }

                var rows = '';
                $.each(recipes, function (i, row) {
                    var avatarHtml;
                    if (row.userphoto && row.userphoto !== 'default.png') {
                        avatarHtml =
                            '<div class="creator-avatar">' +
                            '<img src="uploads/' + row.userphoto +
                            '" class="table-img" alt="Chef"></div>';
                    } else {
                        avatarHtml =
                            '<div class="creator-avatar initial-avatar">' +
                            row.firstname.charAt(0).toUpperCase() +
                            '</div>';
                    }

                    rows +=
                        '<tr>' +
                        '<td><a href="view_recipe.php?id=' + row.id + '">' +
                            row.name + '</a></td>' +
                        '<td><img src="uploads/' + row.photofilename +
                            '" class="table-img"></td>' +
                        '<td><div class="creator-info">' + avatarHtml +
                            '<span>' + row.firstname + ' ' +
                            row.lastname + '</span></div></td>' +
                        '<td><span class="likes-badge">&#x1F44D; ' + row.likes + '</span></td>' +
                        '<td><span class="category-badge">' +
                            row.categoryname + '</span></td>' +
                        '</tr>';
                });

                var tableHtml =
                    '<table class="data-table" id="recipesTable">' +
                    '<thead><tr>' +
                    '<th>Recipe Name</th><th>Photo</th><th>Creator</th>' +
                    '<th>Likes</th><th>Category</th>' +
                    '</tr></thead>' +
                    '<tbody>' + rows + '</tbody>' +
                    '</table>';

                $('#recipesTableWrapper').html(tableHtml);
            },
            error: function () {
                alert('An error occurred while filtering recipes. Please try again.');
            }
        });
    });

    /*  Remove recipe from favourites */
    $(document).on('click', '.btn-remove-fav', function () {
        if (!confirm('Remove from favourites?')) return;

        var btn      = $(this);
        var recipeID = btn.data('id');
        var row      = btn.closest('tr');

        $.ajax({
            url: 'ajax_remove_favourite.php',
            type: 'POST',
            data: { recipeID: recipeID },
            success: function (response) {
                if (response.trim() === 'true') {
                    row.fadeOut(400, function () {
                        $(this).remove();
                        // If table is now empty, show the empty state card
                        if ($('#favouritesTable tbody tr').length === 0) {
                            $('#favouritesTable').replaceWith(
                                '<div class="empty-state-wrapper" id="favsEmpty">' +
                                '<div class="empty-state-card">' +
                                '<div class="empty-state-icon">&#x2764;&#xFE0F;</div>' +
                                '<h2>No favourites yet</h2>' +
                                '<p>Recipes you favourite will appear here.</p>' +
                                '</div></div>'
                            );
                        }
                    });
                } else {
                    alert('Could not remove recipe from favourites. Please try again.');
                }
            },
            error: function () {
                alert('An error occurred. Please try again.');
            }
        });
    });

});
</script>

</body>
</html>
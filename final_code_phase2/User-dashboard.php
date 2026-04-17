<?php
include("session.php");
include("DB.php");

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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $categoryID = intval($_POST['categoryID']);
    if ($categoryID === 0) {
        $recipesResult = $conn->query("SELECT r.*, u.firstname, u.lastname,
                                              u.photofilename as userphoto, c.categoryname
                                       FROM recipe r
                                       JOIN user u ON r.userid = u.id
                                       JOIN recipecategory c ON r.categoryid = c.id");
    } else {
        $recipesResult = $conn->query("SELECT r.*, u.firstname, u.lastname,
                                              u.photofilename as userphoto, c.categoryname
                                       FROM recipe r
                                       JOIN user u ON r.userid = u.id
                                       JOIN recipecategory c ON r.categoryid = c.id
                                       WHERE r.categoryid = $categoryID");
    }
} else {
    $recipesResult = $conn->query("SELECT r.*, u.firstname, u.lastname,
                                          u.photofilename as userphoto, c.categoryname
                                   FROM recipe r
                                   JOIN user u ON r.userid = u.id
                                   JOIN recipecategory c ON r.categoryid = c.id");
}
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
        <h1><?= htmlspecialchars($user['firstname']) ?> 👋</h1>
        <p>Discover and share delicious recipes</p>
    </div>

    <div class="info-cards">

        <div class="info-card">
            <h3>👤 My Information</h3>
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
            <h3>📊 My Recipes</h3>
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

    <section class="table-section">
        <div class="table-header">
            <h2>🍽️ All Available Recipes</h2>

            <form method="POST" action="User-dashboard.php" class="filter-area">
                <select name="categoryID">
                    <option value="0">All Categories</option>
                    <?php foreach ($categoriesList as $cat):
                        $selected = (isset($_POST['categoryID']) && $_POST['categoryID'] == $cat['id']) ? 'selected' : '';
                    ?>
                        <option value="<?= $cat['id'] ?>" <?= $selected ?>>
                            <?= htmlspecialchars($cat['categoryname']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <button type="submit" class="btn-outline btn-small">Filter</button>
            </form>
        </div>

        <?php if (count($recipesList) > 0): ?>
        <table class="data-table">
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
                    <td><span class="likes-badge">👍 <?= $row['likes'] ?></span></td>
                    <td><span class="category-badge"><?= htmlspecialchars($row['categoryname']) ?></span></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>

        <?php else: ?>
        <div class="empty-state-wrapper">
            <div class="empty-state-card">
                <div class="empty-state-icon">🔍</div>
                <h2>No recipes found</h2>
                <p>There are no recipes in this category yet.</p>
            </div>
        </div>
        <?php endif; ?>

    </section>

    <section class="table-section">
        <h2>❤️ My Favourite Recipes</h2>

        <?php if (count($favouritesList) > 0): ?>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Recipe Name</th>
                    <th>Photo</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($favouritesList as $fav): ?>
                <tr>
                    <td>
                        <a href="view_recipe.php?id=<?= $fav['id'] ?>">
                            <?= htmlspecialchars($fav['name']) ?>
                        </a>
                    </td>
                    <td>
                        <img src="uploads/<?= htmlspecialchars($fav['recipephoto']) ?>" class="table-img">
                    </td>
                    <td>
                        <a href="Remove-favourite.php?id=<?= $fav['id'] ?>" class="red-link"
                           onclick="return confirm('Remove from favourites?')">
                            Remove
                        </a>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>

        <?php else: ?>
        <div class="empty-state-wrapper">
            <div class="empty-state-card">
                <div class="empty-state-icon">❤️</div>
                <h2>No favourites yet</h2>
                <p>Recipes you favourite will appear here.</p>
            </div>
        </div>
        <?php endif; ?>

    </section>

</main>

<footer>
    © 2026 BellaCucina. All rights reserved.
</footer>

</body>
</html>
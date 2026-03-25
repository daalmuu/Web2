<?php
include("session.php");
include("DB.php");

if ($_SESSION['userType'] != "user") {
    header("Location: login.php");
    exit();
}

$userID = $_SESSION['userID'];
$result = $conn->query("SELECT * FROM Recipe WHERE userID = $userID");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>BellaCucina | My Recipes</title>
    <link rel="stylesheet" href="main.css">
</head>
<body>

<!-- ===== Header ===== -->
<header>
    <div class="header-container">
        <a href="User-dashboard.php">
            <img src="images/logo.png" alt="BellaCucina Logo" class="logo">
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

<!-- ===== Page Content ===== -->
<main class="dashboard-page fade-in">

    <!-- Page Title -->
    <div class="welcome-banner">
        <span class="welcome-label">My Kitchen</span>
        <h1>📋 My Recipes</h1>
        <p>Manage and edit your added recipes</p>
    </div>

    <!-- Add Recipe Button -->
    <div class="add-recipe-bar">
        <a href="add.php" class="btn-green">➕ Add New Recipe</a>
    </div>

<?php if ($result->num_rows > 0): ?>

    <!-- ===== Recipes Table ===== -->
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
                    $likes        = $conn->query("SELECT COUNT(*) as total FROM Likes WHERE recipeID=$recipeID")->fetch_assoc()['total'];
                    $ingredients  = $conn->query("SELECT * FROM Ingredients WHERE recipeID=$recipeID");
                    $instructions = $conn->query("SELECT * FROM Instructions WHERE recipeID=$recipeID ORDER BY stepOrder");
                ?>

                <tr>

                    <!-- Recipe name + photo -->
                    <td>
                        <a href="view.php?id=<?=$recipeID?>">
                            <div class="recipe-cell">
                                <img src="images/<?=$row['photoFileName']?>" class="table-img">
                                <span><?=$row['name']?></span>
                            </div>
                        </a>
                    </td>

                    <!-- Ingredients -->
                    <td>
                        <ul class="small-list">
                            <?php while($ing = $ingredients->fetch_assoc()): ?>
                                <li><?=$ing['ingredientName']?> – <?=$ing['ingredientQuantity']?></li>
                            <?php endwhile; ?>
                        </ul>
                    </td>

                    <!-- Instructions -->
                    <td>
                        <ol class="inst-list">
                            <?php while($inst = $instructions->fetch_assoc()): ?>
                                <li><?=$inst['step']?></li>
                            <?php endwhile; ?>
                        </ol>
                    </td>

                    <!-- Video -->
                    <td>
                        <a href="<?=$row['videoFilePath']?>" target="_blank" class="video-link">
                            Watch Video
                        </a>
                    </td>

                    <!-- Likes -->
                    <td>
                        <span class="likes-badge">👍 <?=$likes?></span>
                    </td>

                    <!-- Edit -->
                    <td>
                        <a href="edit.php?id=<?=$recipeID?>" class="btn-icon edit">Edit</a>
                    </td>

                    <!-- Delete -->
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

<!-- ===== Footer ===== -->
<footer>
    © 2026 BellaCucina. All rights reserved.
</footer>

</body>
</html>
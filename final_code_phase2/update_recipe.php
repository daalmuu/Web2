<?php
include('session.php');
include('DB.php');

if ($_SESSION['usertype'] != "user") {
    header("Location: login.php?error=Access+denied");
    exit();
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: my-recipe.php");
    exit();
}

$recipeid = intval($_GET['id']);
$userID   = (int)$_SESSION['userid'];

$recipe_stmt = mysqli_prepare($conn, "SELECT * FROM recipe WHERE id = ? AND userid = ?");
mysqli_stmt_bind_param($recipe_stmt, "ii", $recipeid, $userID);
mysqli_stmt_execute($recipe_stmt);
$recipe_result = mysqli_stmt_get_result($recipe_stmt);

if (mysqli_num_rows($recipe_result) === 0) {
    header("Location: my-recipe.php");
    exit();
}
$recipe = mysqli_fetch_assoc($recipe_result);

$category_result = mysqli_query($conn, "SELECT id, categoryname FROM recipecategory ORDER BY categoryname ASC");

$ingredients = [];
$ingredient_stmt = mysqli_prepare($conn, "SELECT * FROM ingredients WHERE recipeid = ? ORDER BY id ASC");
mysqli_stmt_bind_param($ingredient_stmt, "i", $recipeid);
mysqli_stmt_execute($ingredient_stmt);
$ingredient_result = mysqli_stmt_get_result($ingredient_stmt);
while ($row = mysqli_fetch_assoc($ingredient_result)) $ingredients[] = $row;

$instructions = [];
$instruction_stmt = mysqli_prepare($conn, "SELECT * FROM instructions WHERE recipeid = ? ORDER BY steporder ASC");
mysqli_stmt_bind_param($instruction_stmt, "i", $recipeid);
mysqli_stmt_execute($instruction_stmt);
$instruction_result = mysqli_stmt_get_result($instruction_stmt);
while ($row = mysqli_fetch_assoc($instruction_result)) $instructions[] = $row;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>BellaCucina | Edit Recipe</title>
    <link rel="stylesheet" href="main.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>

<div class="container">
    <form action="update_recipe_process.php" method="POST" enctype="multipart/form-data">
        <h2>Edit Recipe</h2>

        <input type="hidden" name="recipeid" value="<?= $recipe['id'] ?>">
        <input type="hidden" name="oldphoto" value="<?= htmlspecialchars($recipe['photofilename']) ?>">
        <input type="hidden" name="oldvideo" value="<?= htmlspecialchars($recipe['videofilepath']) ?>">

        <label>Recipe Name</label>
        <input type="text" name="name" value="<?= htmlspecialchars($recipe['name']) ?>" required>

        <label>Category</label>
        <select name="categoryid" required>
            <?php while ($category = mysqli_fetch_assoc($category_result)): ?>
                <option value="<?= $category['id'] ?>" <?= ($category['id'] == $recipe['categoryid']) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($category['categoryname']) ?>
                </option>
            <?php endwhile; ?>
        </select>

        <label>Description</label>
        <textarea name="description" required><?= htmlspecialchars($recipe['description']) ?></textarea>

        <label>Current Photo</label>
        <?php if (!empty($recipe['photofilename'])): ?>
            <div class="media-box">
                <img src="uploads/<?= htmlspecialchars($recipe['photofilename']) ?>" alt="recipe photo" style="width:150px;">
            </div>
        <?php endif; ?>

        <label>Change Photo</label>
        <input type="file" name="photo" accept="image/*">

        <label>Ingredients</label>
        <div id="ingredients">
            <?php if (!empty($ingredients)): ?>
                <?php foreach ($ingredients as $index => $ingredient): ?>
                    <div class="ingredient-row">
                        <span class="ing-num"><?= $index + 1 ?>.</span>
                        <input class="ing-name" name="ingredientname[]" type="text" value="<?= htmlspecialchars($ingredient['ingredientname']) ?>" required>
                        <input class="ing-qty" name="ingredientquantity[]" type="text" value="<?= htmlspecialchars($ingredient['ingredientquantity']) ?>" required>
                        <span class="delete" onclick="deleteRow(this)"><i class="fa-solid fa-trash"></i></span>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="ingredient-row">
                    <span class="ing-num">1.</span>
                    <input class="ing-name" name="ingredientname[]" type="text" placeholder="Ingredient name" required>
                    <input class="ing-qty" name="ingredientquantity[]" type="text" placeholder="Quantity" required>
                    <span class="delete" onclick="deleteRow(this)"><i class="fa-solid fa-trash"></i></span>
                </div>
            <?php endif; ?>
        </div>

        <button class="add-btn" type="button" onclick="addIngredient()">+ Add Ingredient</button>

        <label>Instructions</label>
        <div id="steps">
            <?php if (!empty($instructions)): ?>
                <?php foreach ($instructions as $index => $instruction): ?>
                    <div class="step-row">
                        <span class="step-num"><?= $index + 1 ?></span>
                        <input name="step[]" type="text" value="<?= htmlspecialchars($instruction['step']) ?>" required>
                        <span class="delete" onclick="deleteRow(this)"><i class="fa-solid fa-trash"></i></span>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="step-row">
                    <span class="step-num">1</span>
                    <input name="step[]" type="text" placeholder="Step instruction" required>
                    <span class="delete" onclick="deleteRow(this)"><i class="fa-solid fa-trash"></i></span>
                </div>
            <?php endif; ?>
        </div>

        <button class="add-btn" type="button" onclick="addStep()">+ Add Step</button>

        <label>Current Video</label>
        <?php if (!empty($recipe['videofilepath'])): ?>
            <div class="media-box">
                <?php if (filter_var($recipe['videofilepath'], FILTER_VALIDATE_URL)): ?>
                    <iframe width="250" height="150" src="<?= htmlspecialchars($recipe['videofilepath']) ?>" frameborder="0" allowfullscreen></iframe>
                <?php else: ?>
                    <video width="250" controls>
                        <source src="uploads/<?= htmlspecialchars($recipe['videofilepath']) ?>">
                    </video>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <label>Upload New Video</label>
        <input type="file" name="video" accept="video/*">

        <label>Or provide video URL (YouTube / MP4)</label>
        <input type="text" name="video_url" placeholder="YouTube link or file name"
               value="<?= htmlspecialchars($recipe['videofilepath']) ?>">

        <div class="buttons">
            <button type="submit">Update Recipe</button>
        </div>
    </form>
</div>

<script src="edit.js"></script>
</body>
</html>
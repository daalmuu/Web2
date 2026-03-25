<?php
include 'db.php';

$category_query = "select id, categoryname from recipecategory order by categoryname asc";
$category_result = mysqli_query($conn, $category_query);

if (!$category_result) {
    die("error loading categories: " . mysqli_error($conn));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>BellaCucina | Add Recipe</title>
    <link rel="stylesheet" href="main.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>

<div class="container">
    <form action="add_recipe_process.php" method="POST" enctype="multipart/form-data">
        <h2>Add Recipe</h2>

        <label>Recipe Name</label>
        <input name="name" type="text" placeholder="Tiramisu" required>

        <label>Category</label>
        <select name="categoryid" required>
            <option value="" selected disabled>Select category</option>
            <?php while ($category = mysqli_fetch_assoc($category_result)) { ?>
                <option value="<?= $category['id']; ?>">
                    <?= htmlspecialchars($category['categoryname']); ?>
                </option>
            <?php } ?>
        </select>

        <label>Description</label>
        <textarea name="description" required></textarea>

        <label>Recipe Photo</label>
        <input name="photo" type="file" accept="image/*" required>

        <label>Ingredients</label>
        <div id="ingredients">
            <div class="ingredient-row">
                <span class="ing-num">1.</span>
                <input class="ing-name" name="ingredientname[]" type="text" placeholder="Ingredient name" required>
                <input class="ing-qty" name="ingredientquantity[]" type="text" placeholder="Quantity" required>
                <span class="delete" onclick="deleteRow(this)">
                    <i class="fa-solid fa-trash"></i>
                </span>
            </div>
        </div>

        <button class="add-btn" type="button" onclick="addIngredient()">+ Add Ingredient</button>

        <label>Instructions</label>
        <div id="steps">
            <div class="step-row">
                <span class="step-num">1</span>
                <input name="step[]" type="text" placeholder="Step instruction" required>
                <span class="delete" onclick="deleteRow(this)">
                    <i class="fa-solid fa-trash"></i>
                </span>
            </div>
        </div>

        <button class="add-btn" type="button" onclick="addStep()">+ Add Step</button>

        <label>Upload Video</label>
        <input name="video" type="file" accept="video/*">

        <div class="buttons">
            <button type="submit">Add Recipe</button>
        </div>
    </form>
</div>

<script src="add.js"></script>
</body>
</html>

function addIngredient(){
    let container = document.getElementById("ingredients");

    let row = document.createElement("div");
    row.className = "ingredient-row";

    row.innerHTML =
        '<span class="ing-num"></span>' +
        '<input class="ing-name" type="text" placeholder="Ingredient name">' +
        '<input class="ing-qty" type="text" placeholder="Quantity">' +
        '<span class="delete" onclick="deleteRow(this)"><i class="fa-solid fa-trash"></i></span>';

    container.appendChild(row);

    renumberRows();
}

function addStep(){
    let container = document.getElementById("steps");

    let row = document.createElement("div");
    row.className = "step-row";

    row.innerHTML =
        '<span class="step-num"></span>' +
        '<input type="text" placeholder="Step instruction">' +
        '<span class="delete" onclick="deleteRow(this)"><i class="fa-solid fa-trash"></i></span>';

    container.appendChild(row);

    renumberRows();
}

function deleteRow(icon){
    icon.parentElement.remove();
    renumberRows();
}

/* =========================
   Validation helpers
========================= */

function isValidName(){
    var name = document.getElementById("recipeName").value.trim();
    return name !== "";
}

function isValidCategory(){
    var category = document.getElementById("category").value;
    return category !== "";
}

function isValidDescription(){
    var desc = document.getElementById("description").value.trim();
    return desc !== "";
}

// Ingredients: لازم على الأقل صف واحد مكتمل (name + qty)
function hasValidIngredient(){
    var rows = document.querySelectorAll("#ingredients .ingredient-row");

    for (var i = 0; i < rows.length; i++){
        var n = rows[i].querySelector(".ing-name").value.trim();
        var q = rows[i].querySelector(".ing-qty").value.trim();
        if (n !== "" && q !== "") return true;
    }
    return false;
}

// Steps: لازم على الأقل خطوة وحدة
function hasValidStep(){
    var steps = document.querySelectorAll("#steps .step-row input");
    for (var i = 0; i < steps.length; i++){
        if (steps[i].value.trim() !== "") return true;
    }
    return false;
}

// Photo optional, but if chosen must be image
function isValidPhotoFile(){
    var photoInput = document.getElementById("photoFile");
    if (!photoInput) return true;
    if (!photoInput.files || photoInput.files.length === 0) return true;
    return photoInput.files[0].type.startsWith("image/");
}

// Video optional, but if chosen must be video
function isValidVideoFile(){
    var videoInput = document.getElementById("videoFile");
    if (!videoInput) return true;
    if (!videoInput.files || videoInput.files.length === 0) return true;
    return videoInput.files[0].type.startsWith("video/");
}

/* =========================
   Update Recipe (submit)
========================= */

function updateRecipe(){
    var errors = [];

    // REQUIRED in edit (same rules as add)
    if (!isValidName()) {
        errors.push("• Recipe name cannot be empty.");
    }

    if (!isValidCategory()) {
        errors.push("• Category cannot be empty.");
    }

    if (!isValidDescription()) {
        errors.push("• Description cannot be empty.");
    }

    if (!hasValidIngredient()) {
        errors.push("• Please keep at least one ingredient (name + quantity).");
    }

    if (!hasValidStep()) {
        errors.push("• Please keep at least one instruction step.");
    }

    // OPTIONAL files, but must be correct type if used
    if (!isValidPhotoFile()) {
        errors.push("• Change Photo must be an image file only.");
    }

    if (!isValidVideoFile()) {
        errors.push("• Upload New Video must be a video file only.");
    }

    // Show ONE alert
    if (errors.length > 0) {
        alert("Fix the following:\n\n" + errors.join("\n"));
        return;
    }

    window.location.href = "my-recipe.html";
}

/* =========================
   Numbering 
========================= */

function renumberRows(){
    // Ingredients
    let ingContainer = document.getElementById("ingredients");

    let ingRows = ingContainer.getElementsByClassName("ingredient-row");

    for(let i = 0; i < ingRows.length; i++){
        let num = ingRows[i].getElementsByClassName("ing-num")[0];
        if(num){
            num.innerHTML = (i + 1) + ".";
        }
    }

    // Steps
    let stepContainer = document.getElementById("steps");
    let stepRows = stepContainer.getElementsByClassName("step-row");

    for(let j = 0; j < stepRows.length; j++){
        let num2 = stepRows[j].getElementsByClassName("step-num")[0];
        if(num2){
            num2.innerHTML = (j + 1);
        }
    }
}

/* =========================
   On load (renumber + file type checks)
========================= */

window.onload = function(){
    renumberRows();

    // Change Photo: image only (optional)
    var photoInput = document.getElementById("photoFile");
    if(photoInput){
        photoInput.onchange = function(){
            if(photoInput.files && photoInput.files.length > 0){
                var f = photoInput.files[0];
                if(!f.type.startsWith("image/")){
                    alert("Photo field accepts image files only.");
                    photoInput.value = "";
                }
            }
        };
    }

    // Upload Video: video only (optional)
    var videoInput = document.getElementById("videoFile");
    if(videoInput){
        videoInput.onchange = function(){
            if(videoInput.files && videoInput.files.length > 0){
                var f2 = videoInput.files[0];
                if(!f2.type.startsWith("video/")){
                    alert("Video field accepts video files only.");
                    videoInput.value = "";
                }
            }
        };
    }
};


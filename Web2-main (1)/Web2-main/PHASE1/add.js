/* =========================
   Add Ingredient / Step
========================= */

function addIngredient() {
  const container = document.getElementById("ingredients");

  const row = document.createElement("div");
  row.className = "ingredient-row";

  row.innerHTML =
    '<span class="ing-num"></span>' +
    '<input class="ing-name" type="text" placeholder="Ingredient name">' +
    '<input class="ing-qty" type="text" placeholder="Quantity">' +
    '<span class="delete" onclick="deleteRow(this)">' +
      '<i class="fa-solid fa-trash"></i>' +
    '</span>';

  container.appendChild(row);
  renumberRows();
}

function addStep() {
  const container = document.getElementById("steps");

  const row = document.createElement("div");
  row.className = "step-row";

  row.innerHTML =
    '<span class="step-num"></span>' +
    '<input type="text" placeholder="Step instruction">' +
    '<span class="delete" onclick="deleteRow(this)">' +
      '<i class="fa-solid fa-trash"></i>' +
    '</span>';

  container.appendChild(row);
  renumberRows();
}

/* =========================
   Delete row
========================= */

function deleteRow(el) {
  el.parentElement.remove();
  renumberRows();
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
   Validation 
========================= */

function isValidName() {
  const name = document.getElementById("recipeName").value.trim();
  return name !== "";
}

function isValidCategory() {
  const category = document.getElementById("category").value;
  return category !== "";
}

function isValidDescription() {
  const desc = document.getElementById("description").value.trim();
  return desc !== "";
}

function isValidImage() {
  const img = document.getElementById("recipePhoto");
  if (!img.files || img.files.length === 0) return false;
  return img.files[0].type.startsWith("image/");
}

/* 🔹 Video is OPTIONAL, but if uploaded must be video */
function isValidVideo() {
  const video = document.getElementById("videoFile");

  // ما رفع شيء = عادي
  if (!video.files || video.files.length === 0) return true;

  // رفع شيء = لازم يكون فيديو
  return video.files[0].type.startsWith("video/");
}

function hasValidIngredient() {
  const rows = document.querySelectorAll("#ingredients .ingredient-row");

  for (const row of rows) {
    const name = row.querySelector(".ing-name").value.trim();
    const qty  = row.querySelector(".ing-qty").value.trim();
    if (name !== "" && qty !== "") return true;
  }
  return false;
}

function hasValidStep() {
  const steps = document.querySelectorAll("#steps .step-row input");
  for (const step of steps) {
    if (step.value.trim() !== "") return true;
  }
  return false;
}

/* =========================
   Main submit
========================= */

function addRecipe() {
 
  let errors = [];

  if (!isValidName()) {
    errors.push("• Please enter recipe name.");
  }

  if (!isValidCategory()) {
    errors.push("• Please choose a category.");
  }

  if (!isValidDescription()) {
    errors.push("• Please enter description.");
  }

  if (!isValidImage()) {
    errors.push("• Please upload a recipe image.");
  }

  if (!hasValidIngredient()) {
    errors.push("• Please add at least one ingredient (name + quantity).");
  }

  if (!hasValidStep()) {
    errors.push("• Please add at least one instruction step.");
  }



// 🔹 Video optional but must be video if provided
  if (!isValidVideo()) {
    errors.push("• If you upload a file in Video field, it must be a video.");
  }

  if (errors.length > 0) {
    alert("Fix the following:\n\n" + errors.join("\n"));
    return;
  }

  window.location.href = "my-recipe.html";
}

/* =========================
   On load
========================= */

window.onload = function () {
  renumberRows();

  
  const videoInput = document.getElementById("videoFile");
  if (videoInput) {
    videoInput.onchange = function () {
      if (!isValidVideo()) {
        alert("Video field accepts video files only.");
        videoInput.value = "";
      }
    };
  }
};



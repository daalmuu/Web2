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
  let valid = true;

  if (!isValidName()) {
    alert("Please enter recipe name.");
    valid = false;
  }

  if (!isValidCategory()) {
    alert("Please choose a category.");
    valid = false;
  }

  if (!isValidDescription()) {
    alert("Please enter description.");
    valid = false;
  }

  if (!isValidImage()) {
    alert("Please upload a recipe image.");
    valid = false;
  }

  if (!hasValidIngredient()) {
    alert("Please add at least one ingredient (name + quantity).");
    valid = false;
  }

  if (!hasValidStep()) {
    alert("Please add at least one instruction step.");
    valid = false;
  }

  if (!valid) return;

  window.location.href = "my-recipe.html";
}


function checkVideo(input){
    const file = input.files[0];
    if(file && !file.type.startsWith('video/')){
        alert("Please select a video file only!");
        input.value = "";
    }
}



window.onload = renumberRows;

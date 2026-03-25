function addIngredient() {
    const container = document.getElementById("ingredients");
    const row = document.createElement("div");
    row.className = "ingredient-row";

    row.innerHTML =
        '<span class="ing-num"></span>' +
        '<input class="ing-name" name="ingredientname[]" type="text" placeholder="Ingredient name" required>' +
        '<input class="ing-qty" name="ingredientquantity[]" type="text" placeholder="Quantity" required>' +
        '<span class="delete" onclick="deleteRow(this)"><i class="fa-solid fa-trash"></i></span>';

    container.appendChild(row);
    renumberRows();
}

function addStep() {
    const container = document.getElementById("steps");
    const row = document.createElement("div");
    row.className = "step-row";

    row.innerHTML =
        '<span class="step-num"></span>' +
        '<input name="step[]" type="text" placeholder="Step instruction" required>' +
        '<span class="delete" onclick="deleteRow(this)"><i class="fa-solid fa-trash"></i></span>';

    container.appendChild(row);
    renumberRows();
}

function deleteRow(el) {
    const row = el.parentElement;
    const parent = row.parentElement;

    if (parent.children.length > 1) {
        row.remove();
        renumberRows();
    }
}

function renumberRows() {
    const ingRows = document.querySelectorAll("#ingredients .ingredient-row");
    ingRows.forEach((row, index) => {
        row.querySelector(".ing-num").textContent = (index + 1) + ".";
    });

    const stepRows = document.querySelectorAll("#steps .step-row");
    stepRows.forEach((row, index) => {
        row.querySelector(".step-num").textContent = index + 1;
    });
}

window.onload = renumberRows;

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

function updateRecipe(){
    window.location.href = "my-recipe.html";
}


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


window.onload = function(){
    renumberRows();
};

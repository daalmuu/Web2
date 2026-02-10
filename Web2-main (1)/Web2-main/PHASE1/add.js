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

  // منع اختيار صورة في حقل الفيديو مباشرة
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

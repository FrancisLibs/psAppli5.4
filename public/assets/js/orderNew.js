document.addEventListener("DOMContentLoaded", function () {
  const checkboxes = document.querySelectorAll(
    ".account-type-item input[type='checkbox']"
  );
  const lettersField = document.getElementById("lettersField");

  function letters() {
    const selectedLetters = Array.from(checkboxes)
      .filter((c) => c.checked)
      .map((c) => c.dataset.letter); // récupère la lettre grâce à choice_attr
    lettersField.value = selectedLetters.join(""); // ex: "AC"
  }

  letters();

  checkboxes.forEach((cb) => {
    cb.addEventListener("change", letters);
  });
});

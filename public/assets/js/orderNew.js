document.addEventListener("DOMContentLoaded", function () {
  const checkboxes = document.querySelectorAll(
    ".account_type_line input[type='checkbox']"
  );
  const lettersField = document.getElementById("lettersField");

  function letters() {
    const selectedLetters = Array.from(checkboxes)
      .filter((c) => c.checked)
      .map((c) => c.dataset.letter); // récupère la lettre grâce à choice_attr
    lettersField.value = selectedLetters.join(""); // ex: "AC"
  }

  checkboxes.forEach((cb) => {
    cb.addEventListener("change", letters);
  });

  // Fonction lettters dès le chargement de la page.
  letters();
});

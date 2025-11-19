document.addEventListener("DOMContentLoaded", function () {
  const accountButtons = document.querySelectorAll(
    ".account_buttons_line input[type='checkbox']"
  );
  const lettersField = document.getElementById("lettersField");

  function update() {
    // met à jour le champ des lettres
    // 1️⃣ récupère les cases cochées
    const selectedCheckboxes = Array.from(accountButtons).filter(
      (ab) => ab.checked
    );

    // 2️⃣ met à jour les lettres dans le champ
    const selectedLetters = selectedCheckboxes.map((cb) => cb.dataset.letter);
    lettersField.value = selectedLetters.join("");
  }

  accountButtons.forEach((ab) => {
    ab.addEventListener("change", update);
  });

  update(); // Appel de la fonction qui met à jour le champ des lettres et des boutons

  document.getElementById("order").addEventListener("submit", function (e) {
    if (!lettersField.value.trim()) {
      // trim() enlève les espaces
      e.preventDefault(); // bloque l'envoi
      alert("Veuillez sélectionner un type de compte avant de valider.");
    }
  });

  const investCaseParent = document.querySelector(".invest_line");
  const investCase = document.getElementById("order_investment");

  investCase.addEventListener("change", function () {
    if (investCase.checked) {
      investCaseParent.style.backgroundColor = "#19d144ff"; // Couleur verte claire
    } else {
      investCaseParent.style.backgroundColor = "";
    }
  });
});

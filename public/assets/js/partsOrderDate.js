document.addEventListener("DOMContentLoaded", function () {
  // Récupérer les éléments du formulaire
  let champA = document.getElementById("part_stock_approQte");
  let champB = document.getElementById("part_lastCommandeDate");
  let champC = document.getElementById("part_maxDeliveryDate");
  let blocText;
  let pText;

  // Fonction pour mettre à jour les champs de date
  function mettreAJourChampsDate() {
    // Obtenir la date du jour
    let dateDuJour = new Date();

    // Mettre à jour le champ B avec la date du jour
    champB.value = dateDuJour.toISOString().split("T")[0];

    // Mettre à jour le champ C avec la date du jour plus une semaine
    let dateUneSemainePlusTard = new Date(dateDuJour);
    dateUneSemainePlusTard.setDate(dateDuJour.getDate() + 7);
    champC.value = dateUneSemainePlusTard.toISOString().split("T")[0];

    // Ajouter l'élément p au DOM
    pText = document.createElement("p");
    pText.innerText =
      "Réception dans 1 semaine. À changer si la date ne correspond pas.";
    pText.classList.add("txt-box");
    pText.setAttribute("id", "deliveryDateTxt");
    document.getElementById("deliveryDateDiv").appendChild(pText);
    // Selection du bloc texte
    blocText = document.querySelector("#deliveryDateDiv .txt-box");
  }

  // Ajouter un écouteur d'événements pour le champ A
  champA.addEventListener("input", mettreAJourChampsDate);

  champC.addEventListener("change", function () {
    pText.classList.remove("txt-box");
    blocText.innerText = "";
  });
});

// Variables globales
const partModal = document.getElementById("partModal");

// Constante pour les appels fetch
const params = {
  method: "GET",
  headers: {
    "Content-Type": "application/json",
  },
};

// Ouverture modale
function openPartModal() {
  // Afficher la modale
  partModal.style.display = "block";
}

//Fermeture modale
function closePartModal() {
  partModal.style.display = "none";
}

function displayParts(parts) {
  // Selection de l'emplacement dans la modale
  const tableBody = document.getElementById("partListModale");

  tableBody.innerHTML = ""; // Clear existing content
  // Remplissage du tableau
  parts.forEach(function (part) {
    const ligne = document.createElement("tr");
    const tdCode = document.createElement("td");
    const tdDesignation = document.createElement("td");
    const tdReference = document.createElement("td");

    const codeButton = document.createElement("button");
    codeButton.className = "codeButtonClass";
    codeButton.innerText = part.code;
    tdCode.className = "tdCodeClass";
    tdCode.appendChild(codeButton);

    tdDesignation.innerText = part.designation;
    tdDesignation.className = "tdDesignationClass";
    tdReference.innerText = part.reference;
    tdReference.className = "tdReferenceClass";

    ligne.appendChild(tdCode);
    ligne.appendChild(tdDesignation);
    ligne.appendChild(tdReference);

    tableBody.appendChild(ligne);
  });
}

function loadParts() {
  const url = "/part/ajaxPartsList";
  fetch(url, params)
    .then((response) => {
      if (!response.ok) {
        throw new Error(
          "Une erreur est survenue lors de la récupération des pièces."
        );
      }
      return response.json();
    })
    .then((data) => {
      parts = data;
      displayParts(parts);
      codeSelectButtons = document.getElementsByClassName("codeButtonClass");

      // Surveillance boutton ajouter pièce
      for (let index = 0; index < codeSelectButtons.length; index++) {
        codeSelectButtons[index].addEventListener("click", function (e) {
          addSelectPart(e); // Fichier priceRequestAddpart
        });
      }
    })
    .catch((error) => {
      console.error(error.message);
    });
}

// Surveillance bouton "ajouter pièce"
const btn = document.getElementById("morePartsBtn");
btn.addEventListener("click", (e) => {
  e.preventDefault();
  emptyInputFields();
  openPartModal();
  loadParts();
});

// Clear the input fields
function emptyInputFields() {
  // Filter functionality
  var codeFilter = (document.getElementById("codeFilter").innerText = "");
  var designationFilter = (document.getElementById(
    "designationFilter"
  ).innerText = "");
  var referenceFilter = (document.getElementById("referenceFilter").innerText =
    "");
}

// Variable contenant les pièces
var parts = null;

// Surveillance bouton "selection pièce"
var codeSelectButtons = null;

document.addEventListener("DOMContentLoaded", function () {
  // La fenêtre modale
  var modal = document.getElementById("partModal");

  // Filter functionality
  var codeFilter = document.getElementById("codeFilter");
  var designationFilter = document.getElementById("designationFilter");
  var referenceFilter = document.getElementById("referenceFilter");

  [designationFilter, codeFilter, referenceFilter].forEach(function (input) {
    input.addEventListener("input", function () {
      var code = codeFilter.value.toLowerCase();
      var designation = designationFilter.value.toLowerCase();
      var reference = referenceFilter.value.toLowerCase();

      var filteredParts = parts.filter(function (part) {
        return (
          part.code.toLowerCase().includes(code) &&
          part.designation.toLowerCase().includes(designation) &&
          part.reference.toLowerCase().includes(reference)
        );
      });

      displayParts(filteredParts);
    });
  });

  // Fermer la modale (croix dans la modale + bouton fermer)
  const closePartButtons = document.getElementsByClassName("closePartModal");
  for (let index = 0; index < closePartButtons.length; index++) {
    closePartButtons[index].addEventListener("click", function (e) {
      e.preventDefault();
      closePartModal();
    });
  }
});

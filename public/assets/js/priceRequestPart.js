// Importation de fonction
import { updateLignes } from "./priceRequestQte.js";
import { totalGenPrice } from "./priceRequestQte.js";

// Importation de constantes
import { params } from "./priceRequestQte.js";

// Classes
class Part {
  constructor(id, code, designation, reference, qteMax, qteStock, price) {
    this.id = id;
    this.code = code;
    this.designation = designation;
    this.reference = reference;
    this.qteMax = qteMax;
    this.qteStock = qteStock;
    this.price = price;
  }
}

// Variable contenant les pièces
var parts = null;

// Filter functionality
const codeFilter = document.getElementById("codeFilter");
const designationFilter = document.getElementById("designationFilter");
const referenceFilter = document.getElementById("referenceFilter");

// Ouverture modale
function openPartModal() {
  // Afficher la modale
  partModal.style.display = "block";
}

//Fermeture modale
function closePartModal() {
  partModal.style.display = "none";
}

// Affichage de la modale et remplissage avec les pièces
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

  // Ouverture de la modale
  openPartModal();

  // Sélection des boutons de choix de pièce de la modale
  const selectPartButton = document.getElementsByClassName("codeButtonClass");

  // Surveillance boutton ajouter pièce
  for (let index = 0; index < selectPartButton.length; index++) {
    selectPartButton[index].addEventListener("click", function (e) {
      e.preventDefault();
      addSelectedPart(e.target);
      closePartModal();
    });
  }
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
      // Effacement champs de saisie
      clearInputFields();

      // Mise en tableau des pièces dans la modale
      displayParts(parts);
    })
    .catch((error) => {
      console.error(error.message);
    });
}

// Clear the input fields
function clearInputFields() {
  codeFilter.value = "";
  designationFilter.value = "";
  referenceFilter.value = "";
}

function addSelectedPart(target) {
  const partCode = target.innerText;

  // Recherche de la pièce
  const url = `/part/ajaxPart/${encodeURIComponent(partCode)}`;

  fetch(url, params)
    .then((response) => {
      if (!response.ok) {
        throw new Error("Erreur lors de la récupération de l'information");
      }
      return response.json();
    })
    .then((part) => {
      const partToAdd = new Part(
        part.id,
        part.code,
        part.designation,
        part.reference,
        part.qteMax,
        part.qteStock,
        part.price
      );

      addPartToList(partToAdd);

      // Appel de la fonction de mise à jour du prix total
      updateLignes();

      // Et du total général
      totalGenPrice();
    })
    .catch((error) => {
      console.error(error.message);
    });
}

function addPartToList(partToAdd) {
  let partToBuy = partToAdd.qteMax - partToAdd.qteStock;
  if (partToBuy < 0) {
    partToBuy = 0;
  }
  const partList = document.getElementById("partList");
  var row = partList.insertRow();
  var cell1 = row.insertCell();
  var cell2 = row.insertCell();
  var cell3 = row.insertCell();
  var cell4 = row.insertCell();
  var cell5 = row.insertCell();
  var cell6 = row.insertCell();
  var cell7 = row.insertCell();

  cell1.innerHTML = partToAdd.code;
  cell2.innerHTML = partToAdd.designation;
  cell3.innerHTML = partToAdd.reference;

  // Affectation de la chaîne HTML à la cellule

  cell4.innerHTML =
    '<input class="part_qte" type="number" name="quantities[' +
    partToAdd.id +
    ']" value="' +
    partToBuy +
    '" />';

  cell5.innerHTML =
    '<input class="set" type="checkbox" name="selected_parts[]" value="' +
    partToAdd.id +
    '" ></input>';
  cell6.innerHTML =
    "<span class='me-1'>Px un. :</span><span>" +
    partToAdd.price +
    "</span><span>€</span>";
  cell7.innerHTML =
    "<span class='me-1'>Prix tot. :</span><span></span><span>€</span>";

  // Ajout des classes
  row.className = "ligne";
  cell2.className = "text-center";
  cell3.className = "text-center";
  cell4.className = "qte";
  cell5.className = "set";
  cell7.className = "totalPrice";
}

//------------------------------------------------------------------------------------------
document.addEventListener("DOMContentLoaded", function () {
  // Surveillance bouton "ajouter pièce"
  document.getElementById("morePartsBtn").addEventListener("click", (e) => {
    e.preventDefault();
    loadParts();
  });

  // Filter fonctionnalities
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

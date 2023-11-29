// Gestion de la div du fournisseur
function fournisseur(data, proDiv) {
  // S'il y a quelque chose dans la div, on l'efface
  if (proDiv.innerHTML.trim() !== "") {
    proDiv.innerHTML = "";
  }

  var provider = data.provider;
  if (provider) {
    var ul = document.createElement("ul");
    for (var key in provider) {
      if (provider.hasOwnProperty(key)) {
        var li = document.createElement("li");
        li.textContent = key + ": " + provider[key];
        ul.appendChild(li);
      }
    }
    proDiv.appendChild(ul);
  }
}

// Gestion de la div des pièces
function parts(data, partDiv) {
  // S'il y a quelque chose dans la div, on l'efface
  if (partDiv.innerHTML.trim() !== "") {
    partDiv.innerHTML = "";
  }
  // Et on remplit
  var parts = data.parts;

  if (Object.keys(parts).length !== 0) {
    var span = document.createElement("span");
    span.innerHTML = "<strong>Pièces</strong>";
    partDiv.appendChild(span);

    var ul = document.createElement("ul");
    for (var key in parts) {
      if (parts.hasOwnProperty(key)) {
        var li = document.createElement("li");
        li.textContent =
          key +
          ": " +
          parts[key].id +
          " " +
          parts[key].code +
          " " +
          parts[key].designation;
        ul.appendChild(li);
      }
    }
    partDiv.appendChild(ul);
  }
}

// Gestion de la div des bons de livraison
function deliveryNotes(data, deliveryNotesDiv) {
  // S'il y a quelque chose dans la div, on l'efface
  if (deliveryNotesDiv.innerHTML.trim() !== "") {
    deliveryNotesDiv.innerHTML = "";
  }

  // Et on remplit
  var deliveryNotes = data.deliveryNotes;
  if (Object.keys(deliveryNotes).length !== 0) {
    var span = document.createElement("span");
    span.innerHTML = "<strong>Bons de livraison</strong>";
    deliveryNotesDiv.appendChild(span);

    var ul = document.createElement("ul");
    for (var key in deliveryNotes) {
      if (deliveryNotes.hasOwnProperty(key)) {
        var li = document.createElement("li");
        li.textContent =
          "id : " +
          deliveryNotes[key].id +
          " numéro : " +
          deliveryNotes[key].number;
        ul.appendChild(li);
      }
    }
    deliveryNotesDiv.appendChild(ul);
  }
}

const resultDiv1 = document.getElementById("provider1");
const resultDiv2 = document.getElementById("provider2");
const partDiv1 = document.getElementById("parts1");
const partDiv2 = document.getElementById("parts2");
const deliveryNotesDiv1 = document.getElementById("deliveryNotes1");
const deliveryNotesDiv2 = document.getElementById("deliveryNotes2");
const providerToKeep = document.getElementById("provider_clean_providerToKeep");
const providerToReplace = document.getElementById(
  "provider_clean_providerToReplace"
);

async function fetchData(entityId, proDiv, partDiv, deliveryNotesDiv) {
  return fetch("/providerUtils/get-entity-info/" + entityId)
    .then((response) => {
      if (!response.ok) {
        throw new Error("Erreur lors de la requête Ajax");
      }
      return response.json();
    })
    .then((data) => {
      // Fournisseur
      fournisseur(data, proDiv);

      // Pièces
      parts(data, partDiv);

      // Bons de livraison
      deliveryNotes(data, deliveryNotesDiv);
    })
    .catch((error) => {
      console.error(error);
    });
}

// Saisie 1er fournisseur
document
  .getElementById("provider_clean_providerToKeep")
  .addEventListener("input", function () {
    fetchData(this.value, resultDiv1, partDiv1, deliveryNotesDiv1);
  });

// Saisie 2e fournisseur
document
  .getElementById("provider_clean_providerToReplace")
  .addEventListener("input", function () {
    fetchData(this.value, resultDiv2, partDiv2, deliveryNotesDiv2);
  });

// Update de l'affichage des fournisseurs
document.getElementById("btnUpdate").addEventListener("click", function () {
  resultDiv1.innerHTML = "";
  partDiv1.innerHTML = "";
  deliveryNotesDiv1.innerHTML = "";
  var entityId = providerToKeep.value;
  if (entityId !== "") {
    fetchData(entityId, resultDiv1, partDiv1, deliveryNotesDiv1);
  }
  resultDiv2.innerHTML = "";
  partDiv2.innerHTML = "";
  deliveryNotesDiv2.innerHTML = "";
  var entityId = providerToReplace.value;
  if (entityId !== "") {
    fetchData(entityId, resultDiv2, partDiv2, deliveryNotesDiv2);
  }
});

// Inversion des deux fournisseurs dans le formulaire
const btnReverse = document.getElementById("btnReverse");
btnReverse.addEventListener("click", function () {
  var tempProviderToKeep = providerToKeep.value;
  var tempProviderToReplace = providerToReplace.value;
  var tempResultDiv1 = resultDiv1.innerHTML;
  var tempResultDiv2 = resultDiv2.innerHTML;
  var tempPartDiv1 = partDiv1.innerHTML;
  var tempPartDiv2 = partDiv2.innerHTML;
  var tempDeliveryNotesDiv1 = deliveryNotesDiv1.innerHTML;
  var tempDeliveryNotesDiv2 = deliveryNotesDiv2.innerHTML;

  providerToReplace.value = tempProviderToKeep;
  providerToKeep.value = tempProviderToReplace;
  resultDiv1.innerHTML = tempResultDiv2;
  resultDiv2.innerHTML = tempResultDiv1;
  partDiv1.innerHTML = tempPartDiv2;
  partDiv2.innerHTML = tempPartDiv1;
  deliveryNotesDiv1.innerHTML = tempDeliveryNotesDiv2;
  deliveryNotesDiv2.innerHTML = tempDeliveryNotesDiv1;
});

// Validation du formulaire
document
  .getElementById("provider_clean_form")
  .addEventListener("submit", function (event) {
    // Empêche le formulaire d'être soumis normalement
    event.preventDefault();
    // Récupère les données du formulaire
    var formData = new FormData(event.target);
    // Envoie la requête au serveur Symfony en utilisant fetch
    fetch(event.target.action, {
      method: "POST",
      body: formData,
      headers: {
        "X-Requested-With": "XMLHttpRequest",
      },
    })
      .then((response) => response.json())
      .then((data) => {
        // Fournisseur
        fournisseur(data, resultDiv1);

        // Pièces
        parts(data, partDiv1);

        // Bons de livraison
        deliveryNotes(data, deliveryNotesDiv1);
      })

      .catch((error) => {
        console.error("Erreur lors de la requête fetch:", error);
      });
    partDiv2.innerHTML = "";
    deliveryNotesDiv2.innerHTML = "";
  });

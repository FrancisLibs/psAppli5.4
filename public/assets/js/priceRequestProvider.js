// Variables globales
let provider = null;
const globalProviderContainer = document.getElementById(
  "globalProviderContainer"
);

// Récupération de la div du premier provider
const firstProviderContainer = document.getElementById(
  "firstProviderContainer"
);

// Récupération de la div contenant tous les fournisseurs
const selectFournisseurs = document.getElementById("fournisseurs");

// Constante pour les appels fetch
const params = {
  method: "GET",
  headers: {
    "Content-Type": "application/json",
  },
};

// Création d'un bouton de saisie d'email
function createEmailButton() {
  const emailButton = document.createElement("button");
  emailButton.classList.add("bi", "bi-at", "ms-3", "me-2", "email_button");
  return emailButton;
}

// Ajout du bouton de demande de saise d'un email
function addEmailButton(place) {
  const button = createEmailButton();
  place.appendChild(button);
  return button;
}

// Remplissage de la liste des fournisseurs
function fillSelect(providers) {
  selectFournisseurs.innerHTML = "";

  // Effacer les options existantes
  while (selectFournisseurs.options.length > 0) {
    selectFournisseurs.options.remove(0);
  }

  // La première ligne des selects est une case vide demandant la sélection
  var firstOption = document.createElement("option");
  firstOption.value = "";
  firstOption.text = "selectionnez un fournisseur";
  selectFournisseurs.add(firstOption);

  // Ajouter chaque fournisseur comme une option
  providers.forEach((provider) => {
    var option = document.createElement("option");
    option.value = provider.id;
    option.text = provider.nom;
    selectFournisseurs.add(option);
  });
}

// Commande modales
function openProviderModale() {
  // Afficher la modale
  document.getElementById("providerModal").style.display = "block";
}

function closeProviderModale() {
  document.getElementById("providerModal").style.display = "none";
}

function suppress(target) {
  // Vérification de la div dans laquelle on est
  // Si c'est le premier fournisseur ou un fournisseur ajouté
  if (target.classList.contains("moreProvider")) {
    target.children[5].remove();
  }
  target.children[4].remove();
  target.children[3].remove();
}

// Mise en place du champ de saisie d'email
function inputEmail(target) {
  // Effacement du contenu de la div email
  const div = target.parentNode;
  div.innerHTML = "";

  // Création des éléments
  const inputField = createInputField();
  const validButton = createEmailValidationButton();

  // Vérification s'il y a une adresse mail
  inputField.placeholder =
    provider.email === "" ? "adresse email" : provider.email;

  // Ajout des éléments à la page
  div.appendChild(inputField);
  div.appendChild(validButton);

  if (div.parentNode.parentNode.classList.contains("moreProvider")) {
    addSuppButton(target);
  }
  // Surveillance bouton email
  validButton.addEventListener("click", function (e) {
    e.preventDefault();
    validNewEmail(e.target);
  });
}

// Traitement de la nouvelle adresse saisie---------------------------------------
function validNewEmail(target) {
  const parent = target.parentNode;
  const div = parent.parentNode.children[3];
  const newEmail = div.children[0];
  const hiddenMailField = parent.parentNode.children[2];
  const noEmailButton = createEmailButton();
  const p = createParagraph();

  div.innerHTML = "";

  // Test de l'email
  if (validateEmail(newEmail.value) == false) {
    // L'email saisi n'est pas bon (vide, format, etc...)-----------
    if (provider.email) {
      p.innerText = "Email : " + provider.email;
      p.classList.remove("red_class");
    } else {
      p.innerText = "Ce fournisseur n'a pas d'adresse mail";
      p.classList.add("red_class");
    }
  } else {
    // l'email saisi est bon -> Affichage et Affectation au fourniseur-------
    provider.email = newEmail.value;
    // Sauvegarde de l'email
    const url = `/provider/email/${encodeURIComponent(
      provider.id
    )}/${encodeURIComponent(provider.email)}`;

    fetch(url, params)
      .then((response) => {
        if (!response.ok) {
          throw new Error("Erreur lors de la récupération de l'information");
        }
        return response.json();
      })
      .then((email) => {
        console.log(email);
      })
      .catch((error) => {
        console.error(error.message);
      });

    p.innerText = "Email : " + provider.email;
    hiddenMailField.value = provider.email;
  }
  div.appendChild(p);
  div.appendChild(noEmailButton);
  noEmailButton.addEventListener("click", function (e) {
    e.preventDefault();
    inputEmail(e.target);
  });
  if (parent.parentNode.classList.contains("moreProvider")) {
    // Création du bouton de suppression
    const suppButton = createSuppButton();
    div.appendChild(suppButton);
    suppButton.addEventListener("click", function (e) {
      e.preventDefault();
      parent.parentNode.remove();
    });
  }
}
// ----------------------------------------------------------------------------0

// Validation de l'email saisi
function validateEmail(email) {
  const regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
  return regex.test(email);
}

// Création du paragraphe
function createParagraph() {
  const paragraph = document.createElement("p");
  paragraph.className = "mailAddress";
  return paragraph;
}

// Création champ input
function createInputField() {
  const inputProviderName = document.createElement("input");
  inputProviderName.type = "text";
  inputProviderName.name = "provider_name[]";
  inputProviderName.className = "me-4";
  // inputProviderName.readOnly = true;
  return inputProviderName;
}

// Création d'un champ caché pour l'Id
function createHiddenInputProviderId() {
  const inputProviderId = document.createElement("input");
  inputProviderId.type = "hidden";
  inputProviderId.name = "provider_id[]";
  return inputProviderId;
}

// Création d'un champ caché pour l'email
function createHiddenInputProviderEmail() {
  const inputHiddenProviderEmail = document.createElement("input");
  inputHiddenProviderEmail.type = "hidden";
  inputHiddenProviderEmail.name = "provider_email[]";
  return inputHiddenProviderEmail;
}

// Création du champ input pour saisir les adresses mail
function createInputProviderEmail() {
  const inputProviderEmail = document.createElement("input");
  inputProviderEmail.type = "text";
  inputProviderEmail.name = "provider_email[]";
  inputProviderEmail.classList = "me-3";
  return inputProviderEmail;
}

// Création bouton de validation de saisie d'un email
function createEmailValidationButton() {
  const emailValidationButton = document.createElement("button");
  emailValidationButton.classList.add(
    "btn",
    "btn-primary",
    "btn-sm",
    "emailValidationBtn",
    "me-3"
  );
  emailValidationButton.innerText = "Valider";
  return emailValidationButton;
}

class Provider {
  constructor(id, name, email) {
    this.id = id;
    this.name = name;
    this.email = email;
  }
}

// Création d'un bouton de saisie d'email
function createEmailButton() {
  const emailButton = document.createElement("button");
  emailButton.classList.add("bi", "bi-at", "ms-3", "me-2", "email_button");
  return emailButton;
}

function takeProvider(container) {
  const id = container.children[1].value;
  const name = container.children[0].value;
  let email = container.children[2].value;
  const provider = new Provider(id, name, email);
  return provider;
}

// Création bouton de suppression
function createSuppButton() {
  const suppButton = document.createElement("button");
  suppButton.classList.add("bi", "bi-trash", "supp_button");
  return suppButton;
}

// Ajout d'un fournisseur------------------------------------------------------
function addProvider(newProvider) {
  // Affectation de new Provider à provider
  provider.id = newProvider.id;
  provider.name = newProvider.name;
  provider.email = newProvider.email;

  // Création de la div du nouveau fournisseur
  const newProviderDiv = document.createElement("div");

  // Récupération des classes du premier fournisseur
  const classFirstProviderContainer = Array.from(
    firstProviderContainer.classList
  );

  // Rajout de 2 classes avant de les transférer ver s la div
  classFirstProviderContainer.push("mt-2", "moreProvider");
  newProviderDiv.classList.add(...classFirstProviderContainer);

  const inputProviderName = createInputField();
  inputProviderName.value = provider.name;
  inputProviderName.readOnly = true;

  const hiddenInputProviderId = createHiddenInputProviderId();
  hiddenInputProviderId.className = "champ_cache";
  hiddenInputProviderId.value = provider.id;

  const hiddenInputProviderEmail = createHiddenInputProviderEmail();
  hiddenInputProviderEmail.className = "champ_cache";

  // Création de la div de l'email
  const emailDiv = document.createElement("div");
  emailDiv.classList.add(
    "d-flex",
    "justify-content-start",
    "align-items-center"
  );

  // Puis le paragraphe
  const paragraphe = document.createElement("p");
  paragraphe.className = "emailAddress";

  // Création boutton modif email
  const emailButton = createEmailButton();

  // Création du bouton de suppression
  const suppButton = createSuppButton();

  // Inclusion du paragraphe dans la div email
  emailDiv.appendChild(paragraphe);
  emailDiv.appendChild(emailButton);
  emailDiv.appendChild(suppButton);

  // Contrôle présence email
  if (provider.email == "" || provider.email == "-") {
    paragraphe.textContent = "Pas d'adresse mail pour ce fournisseur";
    paragraphe.classList.add("red_class");
    inputProviderName.classList.add("red_class");
  } else {
    hiddenInputProviderEmail.value = provider.email;
    paragraphe.textContent = "Email : " + provider.email;
  }

  // Inclusion du groupe de l'input dans la div
  newProviderDiv.appendChild(inputProviderName);
  newProviderDiv.appendChild(hiddenInputProviderId);
  newProviderDiv.appendChild(hiddenInputProviderEmail);
  newProviderDiv.appendChild(emailDiv);

  // Affichage div dans providerContainer
  globalProviderContainer.appendChild(newProviderDiv);

  // Surveillance des deux boutons
  emailButton.addEventListener("click", function (e) {
    e.preventDefault();
    inputEmail(e.target);
  });

  suppButton.addEventListener("click", function (e) {
    e.preventDefault();
    e.target.parentNode.parentNode.remove();
  });
}

//-----------------------------------------------------------------

//-----------------------------------------------------------------------------------------
document.addEventListener("DOMContentLoaded", function () {
  // Récup. du premier fournisseur
  provider = takeProvider(firstProviderContainer);

  // Création d'une div pour les emails
  const div = document.createElement("div");
  div.classList.add(
    "divMail",
    "d-flex",
    "justify-content-start",
    "align-items-center"
  );
  const p = createParagraph();
  if (provider.email === "" || provider.email === "-") {
    provider.email = "";
    p.classList.add("red_class");
    p.innerHTML = "Ce fournisseur n'a pas d'adresse email";
  } else {
    p.innerHTML = "Email : " + provider.email;
  }
  div.appendChild(p);
  firstProviderContainer.appendChild(div);

  const noEmailButton = createEmailButton();
  div.appendChild(noEmailButton);
  noEmailButton.addEventListener("click", function (e) {
    e.preventDefault();
    inputEmail(e.target);
  });

  //Surveillance du bouton "ajout fournisseur" pour ajouter un fournisseur-------
  document.getElementById("moreProviderBtn").addEventListener("click", () => {
    // Récupération de la liste des fournisseurs
    const url = "/provider/list";

    fetch(url, params)
      .then((response) => {
        if (!response.ok) {
          throw new Error("Erreur lors de la récupération des fournisseurs");
        }
        return response.json();
      })
      .then((data) => {
        fillSelect(data);
        // Une fois la liste remplie, affichage de la modale
        openProviderModale();
      })
      .catch((error) => {
        console.error(error.message);
      });
  });

  // Surveillance choix fournisseur à ajouter
  selectFournisseurs.addEventListener("change", function (e) {
    e.preventDefault;
    let providerId = selectFournisseurs.value;

    // Récupération de la liste des fournisseurs
    const url = `/provider/show/${providerId}?ajax=true`;

    fetch(url, params)
      .then((response) => {
        if (!response.ok) {
          throw new Error("Erreur lors de la récupération des fournisseurs");
        }
        return response.json();
      })
      .then((newProvider) => {
        addProvider(newProvider);
      })
      .catch((error) => {
        console.error(error.message);
      });

    closeProviderModale();
  });

  // Fermeture de la modale (croix dans la modale + bouton fermer)
  const closeProviderButtons =
    document.getElementsByClassName("closeProviderModal");
  for (let index = 0; index < closeProviderButtons.length; index++) {
    closeProviderButtons[index].addEventListener("click", function (e) {
      e.preventDefault();
      closeProviderModale();
    });
  }
});

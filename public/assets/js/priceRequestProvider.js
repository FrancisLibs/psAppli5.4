document.addEventListener("DOMContentLoaded", function () {
  // Constantes-------------------------------------------------------------------
  // Récupération de la div contenant tous les fournisseurs
  const globalProviderContainer = document.getElementById(
    "globalProviderContainer"
  );

  // Création d'un bouton de validation d'email
  const emailValidButton = createEmailValidationButton();

  // Fonctions--------------------------------------------------------------------
  // Fetch function
  function myFetch(url) {
    const params = {
      method: "GET",
      headers: {
        "Content-Type": "application/json",
      },
    };

    return fetch(url, params)
      .then((response) => {
        if (!response.ok) {
          throw new Error("Erreur lors de la récupération de l'information");
        }
        return response.json();
      })
      .then((data) => {
        return data;
      })
      .catch((error) => {
        console.error(error.message);
      });
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
      "emailValidationBtn"
    );
    emailValidationButton.innerText = "Valider";
    return emailValidationButton;
  }

  // Création d'un bouton de saisie d'email
  function createEmailButton() {
    const emailButton = document.createElement("button");
    emailButton.classList.add("bi", "bi-at", "ms-3", "email_button");
    return emailButton;
  }

  // Création du paragraphe
  function createParagraph() {
    const paragraph = document.createElement("p");
    paragraph.className = "mailAddress";
    return paragraph;
  }

  // Validation de l'email saisi
  function validateEmail(email) {
    const regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return regex.test(email);
  }

  // Commande modale
  function openModal() {
    // Afficher la modale
    document.getElementById("myModal").style.display = "block";
  }

  function closeModal() {
    document.getElementById("myModal").style.display = "none";
  }

  // Fermer la modale (croix dans la modale)
  document.getElementById("closeModal").addEventListener("click", () => {
    closeModal();
  });

  // Ajout du bouton de demande de saise d'un email
  function addEmailButton(place) {
    const button = createEmailButton();
    place.appendChild(button);
    button.addEventListener("click", function (e) {
      e.preventDefault();
      inputEmail(e);
    });
  }

  // Effacment du bouton et du paragraphe
  function inputEmail(e) {
    const target = e.target.parentNode;

    target.children[4].remove();
    target.children[3].remove();

    const inputField = createInputField();
    const validButton = createEmailValidationButton();

    // Vérification s'il y a une adresse mail
    const oldEmail = target.children[2].value;
    if (oldEmail === "") {
      inputField.placeholder = "adresse email";
    } else {
      inputField.placeholder = oldEmail;
    }
    // Ajout des éléments à la page
    target.appendChild(inputField);
    target.appendChild(validButton);

    validButton.addEventListener("click", function (e) {
      e.preventDefault();
      validNewEmail(e, oldEmail);
    });
  }

  // Traitement de la nouvelle adresse saisie
  function validNewEmail(e, oldEmail) {
    const target = e.target.parentNode;
    const providerId = target.children[1].value;
    const newEmail = e.target.parentNode.children[3].value;
    const p = createParagraph();

    // Test de l'email
    if (
      !validateEmail(newEmail) ||
      newEmail === oldEmail ||
      newEmail.length === 0
    ) {
      if (oldEmail === "") {
        p.innerText = "Pas d'adresse mail pour ce fournisseur";
        p.classList.add("red_class");
      } else {
        p.innerText = "Email : " + oldEmail;
      }
    } else {
      target.children[0].classList.remove("red_class");
      // Si oui, sauvegarde de l'email
      const url = `/provider/email/${encodeURIComponent(
        providerId
      )}/${encodeURIComponent(newEmail)}`;

      myFetch(url)
        .then((email) => {
          p.innerText = "Email : " + email;
          target.children[2].value = email;
        })
        .catch((error) => {
          // Gérer les erreurs
          console.log(error);
        });
    }
    // Effacment du bouton et du champ de saise
    target.children[4].remove();
    target.children[3].remove();
    target.appendChild(p);
    addEmailButton(target);
  }

  // Traitement fournisseur d'origine
  addEmailButton(globalProviderContainer.children[0]);

  // Traitement des ajouts de fournisseurs
  document.getElementById("moreProviderBtn").addEventListener("click", () => {
    const url = "/provider/list";
    myFetch(url)
      .then((providers) => {
        fillSelect(providers);
      })
      .catch((error) => {
        // Gérer les erreurs
        console.error(error);
      });
  });

  // Remplissage de la liste des fournisseurs
  const selectFournisseurs = document.getElementById("fournisseurs");
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
      option.value = provider.id; // Vous pouvez ajuster cela selon les besoins
      option.text = provider.nom;
      selectFournisseurs.add(option);
    });
    // Une fois la liste remplie, affichage de la modale
    openModal();
  }

  // Surveillance choix fournisseur a ajouter
  let provider = "";
  selectFournisseurs.addEventListener("change", function (e) {
    e.preventDefault;
    let providerId = selectFournisseurs.value;
    const url = `/provider/show/${providerId}?ajax=true`;
    myFetch(url)
      .then((provider) => {
        addProvider(provider);
      })
      .catch((error) => {
        // Gérer les erreurs
        console.error(error);
      });
    closeModal();
  });

  function addProvider(provider) {
    // Création de la div du nouveau fournisseur
    const div = document.createElement("div");
    const globalProviderContainer = document.getElementById(
      "globalProviderContainer"
    );

    // Récupération des classes du container et assignation à la nouvelle div
    const firstProviderContainer = document.getElementById(
      "firstProviderContainer"
    );
    const classFirstProviderContainer = Array.from(
      firstProviderContainer.classList
    );
    div.classList.add(...classFirstProviderContainer);
    div.classList.add("mt-2");

    const inputProviderName = createInputField();
    inputProviderName.value = provider.name;
    inputProviderName.readOnly = true;

    const hiddenInputProviderId = createHiddenInputProviderId();
    hiddenInputProviderId.className = "champ_cache";
    hiddenInputProviderId.value = provider.id;

    const hiddenInputProviderEmail = createHiddenInputProviderEmail();
    hiddenInputProviderEmail.className = "champ_cache";

    // Création du paragraphe email
    const paragraphe = document.createElement("p");
    paragraphe.className = "emailAddress";

    // Contrôle présence email
    if (provider.email === "" || provider.email.length === 0) {
      paragraphe.textContent = `Pas d'adresse mail pour ce fournisseur`;
      paragraphe.classList.add("red_class");
      inputProviderName.classList.add("red_class");
    } else {
      hiddenInputProviderEmail.value = provider.email;
      paragraphe.textContent = "Email : " + provider.email;
    }

    // Inclusion du groupe de l'input dans la div
    div.appendChild(inputProviderName);
    div.appendChild(hiddenInputProviderId);
    div.appendChild(hiddenInputProviderEmail);
    div.appendChild(paragraphe);

    // Affichage div dans providerContainer
    globalProviderContainer.appendChild(div);

    addEmailButton(div);
  }
});

// Ajout du bouton de demande de saise d'un email
function addEmailButton(place) {
  const button = createEmailButton();
  place.appendChild(button);
  button.addEventListener("click", function (e) {
    e.preventDefault();
    inputEmail(e);
  });
}

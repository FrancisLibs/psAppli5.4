document.addEventListener("DOMContentLoaded", function () {
  // Fonction fetch
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

  // Gestion d'ajout un fournisseur ---------------------------------------------

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
    // Une fois que la liste est remplie, affichage de la modale
    openModal();
  }

  function createSuppButton() {
    const suppButton = document.createElement("button");
    suppButton.classList.add("bi", "bi-trash", "ms-2", "suppButton");

    suppButton.addEventListener("click", function (e) {
      e.preventDefault();
      e.target.parentNode.remove();
    });

    return suppButton;
  }

  // Ajout d'un fournisseur dans le formulaire--------------------------------------
  function addProvider(provider) {
    const providerContainer = document.getElementById("providerContainer");
    const div = document.createElement("div");

    // Récupération des classes du container et assignation à la nouvelle div
    const singleContainer = document.getElementById("singleProviderContainer");
    const classSingleContainer = Array.from(singleContainer.classList);
    div.classList.add(...classSingleContainer);
    div.classList.add("mt-2");

    // Créer un nouvel élément input
    const inputProviderName = document.createElement("input");
    inputProviderName.type = "text";
    inputProviderName.name = "provider_name[]";
    inputProviderName.value = provider.name;
    inputProviderName.className = "me-4";

    // Créer un nouvel élément input caché pour l'id
    const inputProviderId = document.createElement("input");
    inputProviderId.type = "hidden";
    inputProviderId.name = "provider_id[]";
    inputProviderId.value = provider.id;

    // Créer un nouvel élément input caché pour l'email
    const inputProviderEmail = document.createElement("input");
    inputProviderEmail.type = "hidden";
    inputProviderEmail.name = "provider_email[]";
    inputProviderEmail.value = provider.email;

    // Création conditionnelle du paragraphe email
    const paragraphe = document.createElement("p");
    paragraphe.className = "emailAddress";

    // Inclusion du groupe de l'input dans la div
    div.appendChild(inputProviderName);
    div.appendChild(inputProviderId);
    div.appendChild(inputProviderEmail);
    div.appendChild(paragraphe);

    // Contrôle présence
    if (provider.email === "" || provider.email.length === 0) {
      paragraphe.textContent = `Pas d'adresse mail pour ce fournisseur`;
      paragraphe.classList.add("red_class");
      inputProviderName.classList.add("red_class");
    } else {
      paragraphe.textContent = `Email : ${provider.email}`;
    }

    const emailButton = document.createElement("button");
    emailButton.classList.add("bi", "bi-at", "ms-3", "rounded", "emailButton");

    // Surveillance du click du bouton qui demande la saisie de l'email
    emailButton.addEventListener("click", function (e) {
      e.preventDefault();
      createInputfield(e);
    });

    div.appendChild(emailButton);

    // Le bouton suppression est ajouté
    const suppButton = createSuppButton();
    div.appendChild(suppButton);

    // Et affichage de la div entière
    providerContainer.appendChild(div);
  }

  // Création d'un champ input pour l'email
  function createInputfield(e) {
    var inputProviderMail = document.createElement("input");
    inputProviderMail.type = "text";
    inputProviderMail.name = "provider_email";
    inputProviderMail.placeholder = "Nouvelle adresse mail";
    inputProviderMail.className = "me-3";

    // Intégration du champ
    const div = e.target.parentNode;
    // Effacement de la poubelle, du bouton arobase et du paragraphe
    div.children[5].remove();
    div.children[4].remove();
    div.children[3].remove();

    // Placement du champ
    div.appendChild(inputProviderMail);

    // Création bouton de validation
    const emailValidButton = document.createElement("button");
    emailValidButton.classList.add(
      "btn",
      "btn-primary",
      "btn-sm",
      "validProviderEmail"
    );
    // placement du bouton
    emailValidButton.innerText = "Valider";
    div.appendChild(emailValidButton);
    // Surveillance du bouton
    emailValidButton.addEventListener("click", function (e) {
      e.preventDefault();
      validProviderEmail(e);
    });

    // Placement du bouton suppression du fournisseur
    const suppButton = createSuppButton();
    div.appendChild(suppButton);
  }

  // Validation de l'email saisi
  function validProviderEmail(e) {
    // Looking for the provider Id and email
    const providerId = e.target.parentNode.children[1].value;
    const email = e.target.parentNode.children[3].value;

    // Check if the email address is ok
    const regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (regex.test(email) === true) {
      // L'adresse mail est sauvegardée en bdd pour le fournisseur
      // Fonction encodeURIComponent() pour échapper les valeurs des paramètres
      const url = `/provider/email/
      ${encodeURIComponent(providerId)}
      /${encodeURIComponent(email)}`;

      myFetch(url)
        .then((data) => {
          // Data contient l'adresse mail en retour
          // On efface l'input, le bouton validation et la poubelle
          const parent = e.target.parentNode;
          const css = parent.parentNode.children[0].children[3].classList;
          // Effacement de tous les éléments non nécessaires
          parent.children[5].remove();
          parent.children[4].remove();
          parent.children[3].remove();
          parent.children[2].remove();

          // Modification du champ caché de l'adresse email
          // Créer un nouvel élément input caché pour l'email
          let inputProviderEmail = document.createElement("input");
          inputProviderEmail.type = "hidden";
          inputProviderEmail.name = "provider_mail[]";
          inputProviderEmail.value = data;

          // Insertion du champ caché
          parent.appendChild(inputProviderEmail);

          // Puis affichage de l'adresse email
          const paragraphe = document.createElement("p");
          paragraphe.classList.add(css);
          paragraphe.innerText = "Email : " + data;
          parent.appendChild(paragraphe);
          // Et la case du fournisseur retrouve une couleur normale
          parent.children[0].classList.remove("red_class");

          // Et on réaffiche la poubelle
          const suppButton = createSuppButton();
          parent.appendChild(suppButton);
        })
        .catch((error) => {
          // Gérer les erreurs
          console.error(error);
        });
    } else {
      e.target.previousElementSibling.value = "";
    }
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

  // Création de la liste des fournisseurs
  document
    .getElementById("moreProviderBtn")
    .addEventListener("click", function () {
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

  // ajout d'un fournisseur dans le formulaire
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
});

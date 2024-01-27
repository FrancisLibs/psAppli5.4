document.addEventListener("DOMContentLoaded", function () {
  // Fonctions -----------------------------
  // Mise à jour de chaque ligne de pièces
  function update(ligne) {
    const set = ligne.children[4].childNodes[0].checked;
    const qte = ligne.children[3].children[0].value;
    const price = ligne.children[5].children[1].innerHTML;
    let total = ligne.children[6].children[1];
    if (set === true) {
      total.innerHTML = qte * price;
    } else {
      total.innerHTML = 0;
    }
    totalGenPrice();
  }

  // Calcul du prix général
  function totalGenPrice() {
    const totalPricePerPart = document.getElementsByClassName("totalPrice");
    const totalPrice = document.getElementById("totalPrice");
    let totalGenPrice = 0;
    for (let index = 0; index < totalPricePerPart.length; index++) {
      totalGenPrice =
        totalGenPrice + Number(totalPricePerPart[index].children[1].innerHTML);
    }
    totalPrice.innerHTML = totalGenPrice;
  }

  // Gestion d'ajout un fournisseur ---------------------------------------------
  const btnMoreProvider = document.getElementById("moreProvider");
  const selectContainer = document.getElementById("selectContainer");
  const selectFournisseurs = document.getElementById("fournisseurs");

  // Appel de la liste des fournisseurs
  btnMoreProvider.addEventListener("click", function () {
    fetch("/provider/list", {
      method: "GET",
    })
      .then((response) => {
        if (!response.ok) {
          throw new Error("Erreur lors de la récupération des fournisseurs");
        }
        return response.json();
      })
      .then((data) => {
        fillSelect(data);
      })
      .catch((error) => {
        console.error(error.message);
      });
  });

  // Remplissage de la liste des fournisseurs
  function fillSelect(data) {
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
    data.forEach((provider) => {
      var option = document.createElement("option");
      option.value = provider.id; // Vous pouvez ajuster cela selon les besoins
      option.text = provider.nom;
      selectFournisseurs.add(option);
    });
    // Une fois que la liste est remplie, affichage de la modale
    openModal();
  }

  function openModal() {
    // Afficher la modale
    document.getElementById("myModal").style.display = "block";
  }

  function closeModal() {
    document.getElementById("myModal").style.display = "none";
  }

  // Fermer la modale
  document.getElementById("closeModal").addEventListener("click", () => {
    closeModal();
  });

  function addProvider(providerId) {
    const id = providerId; // Remplacez cela par la valeur souhaitée
    let ajax = true;
    const url = `/provider/show/${id}?ajax=true`;
    const params = {
      method: "GET",
      headers: {
        "Content-Type": "application/json",
      },
    };

    fetch(url, params)
      .then((response) => {
        if (!response.ok) {
          throw new Error("Erreur lors de la récupération du fournisseur");
        }
        return response.json();
      })
      .then((data) => {
        addProvider(data);
      })
      .catch((error) => {
        console.error(error.message);
      });
  }

  function addProvider(data) {
    console.log(data);
    // let singleContainer = document.getElementById("singleProviderContainer");
    // let classSingleContainer = array.from(singleContainer);
    // console.log(classSingleContainer);
    // // Créer un nouvel élément input
    // var inputFournisseur = document.createElement("input");

    // // Définir l'ID et le nom du nouvel input (ajustez selon vos besoins)
    // inputFournisseur.id = "inputFournisseur";
    // inputFournisseur.name = "inputFournisseur";

    // // Définir la valeur du nouvel input avec la valeur sélectionnée
    // inputFournisseur.value = selectedValue;
  }

  selectFournisseurs.addEventListener("change", function () {
    let providerId = selectFournisseurs.value;
    addProvider(providerId);
    closeModal();
  });

  // Script général------------------------------------------------------

  const qtes = document.getElementsByClassName("part_qte");
  const lignes = document.getElementsByClassName("ligne");

  // Mise à jour des prix totaux lors du chargement de la page
  for (let index = 0; index < lignes.length; index++) {
    update(lignes[index]);
  }

  // génération des du prix général au chargement de la page
  totalGenPrice();

  // Surveillance de la modification de quantités
  for (let index = 0; index < qtes.length; index++) {
    qtes[index].addEventListener("change", function (event) {
      let ligne = event.target.parentNode.parentNode;
      update(ligne);
    });
  }

  // Surveillande de l'activation ou désactivation de pièces
  const sets = document.getElementsByClassName("set");
  for (let index = 0; index < sets.length; index++) {
    sets[index].addEventListener("change", function (event) {
      update(lignes[index]);
    });
  }
});

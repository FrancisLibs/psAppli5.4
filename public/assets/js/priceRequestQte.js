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

  // SCRIPT GENERAL-------------------------------------------

  const qtes = document.getElementsByClassName("part_qte");
  const lignes = document.getElementsByClassName("ligne");

  // Mise à jour des prix totaux lors du chargement de la page
  for (let index = 0; index < lignes.length; index++) {
    update(lignes[index]);
  }

  // génération du prix général au chargement de la page
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

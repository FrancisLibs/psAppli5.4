// Mise à jour de chaque ligne de pièces
function updateLignes() {
  const lignes = document.getElementsByClassName("ligne");

  for (let index = 0; index < lignes.length; index++) {
    const set = lignes[index].children[4].childNodes[0].checked;
    const qte = lignes[index].children[3].children[0].value;
    const price = lignes[index].children[5].children[1].innerHTML;
    let total = lignes[index].children[6].children[1];
    let totalPrice = qte * price;

    if (set === true) {
      total.innerHTML = totalPrice;
    } else {
      total.innerHTML = 0;
    }
  }

  totalGenPrice();
}

// Calcul du prix général
function totalGenPrice() {
  const totalPricePart = document.getElementsByClassName("totalPrice");
  let totalGenPrice =
    document.getElementById("totalGenPrice").childNodes[1].childNodes[3];

  let totalGenValue = 0;
  for (let index = 0; index < totalPricePart.length; index++) {
    totalGenValue =
      totalGenValue + Number(totalPricePart[index].children[1].innerHTML);
  }

  totalGenValue = Math.round(totalGenValue * 100) / 100;
  totalGenPrice.innerHTML = totalGenValue;
}

document.addEventListener("DOMContentLoaded", function () {
  const qtes = document.getElementsByClassName("part_qte");
  const sets = document.getElementsByClassName("set");
  // Mise à jour des prix totaux lors du chargement de la page
  updateLignes();

  // Surveillance de la modification de quantités
  for (let index = 0; index < qtes.length; index++) {
    qtes[index].addEventListener("change", function (event) {
      updateLignes();
    });
  }

  // Surveillande de l'activation ou désactivation de pièces
  for (let index = 0; index < sets.length; index++) {
    sets[index].addEventListener("change", function (event) {
      updateLignes();
    });
  }
});

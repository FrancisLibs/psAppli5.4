// Mise à jour de chaque ligne de pièces
export function updateLignes() {
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
}

// Calcul du prix général
export function totalGenPrice() {
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

function buttonClickHandler() {
  console.log("ok");
  updateLignes();
  totalGenPrice();
}

// let qtes = document.getElementsByClassName("part_qte");
// let sets = document.getElementsByClassName("set");

document.addEventListener("DOMContentLoaded", function () {
  // conteneur pour la modification des quantités
  const container = document.getElementById("partList");

  // Surveillance du container
  container.addEventListener("change", buttonClickHandler);
  // Mise à jour des prix totaux lors du chargement de la page
  updateLignes();
});

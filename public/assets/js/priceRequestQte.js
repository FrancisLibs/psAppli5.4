// Déclaration constantes
// Paramètres pour les appels fetch
export const params = {
  method: "GET",
  headers: {
    "Content-Type": "application/json",
  },
};

// Mise à jour de chaque ligne de pièces
export function updateLignes() {
  for (let index = 0; index < lignes.length; index++) {
    const set = lignes[index].children[4].childNodes[0].checked;
    const qte = lignes[index].children[3].children[0].value;
    const price = lignes[index].children[5].children[1].innerHTML;
    let total = lignes[index].children[6].children[1];

    if (set === true) {
      total.innerHTML = Math.round(qte * price * 100) / 100;
    } else {
      total.innerHTML = 0;
    }
  }
  totalGenPrice();
}

// Calcul du prix général
export function totalGenPrice() {
  let totalGenValue = 0;
  for (let index = 0; index < totalPricePart.length; index++) {
    totalGenValue += Number(totalPricePart[index].children[1].innerHTML);
  }

  totalGenValue = Math.round(totalGenValue * 100) / 100;
  totalGen.innerHTML = totalGenValue;
}

// Déclaration des variables globales pour stocker les références aux éléments HTML
let lignes = document.getElementsByClassName("ligne");
let totalPricePart = document.getElementsByClassName("totalPrice");
let totalGen =
  document.getElementById("totalGenPrice").childNodes[1].childNodes[3];
let container = document.getElementById("partList");

// Surveillance du container
container.addEventListener("change", function () {
  updateLignes();
});

document.addEventListener("DOMContentLoaded", function () {
  // Mise à jour des prix totaux lors du chargement de la page
  updateLignes();
});

// Déclaration constantes
// Paramètres pour les appels fetch
export const params = {
  method: "GET",
  headers: {
    "Content-Type": "application/json",
  },
};

// Déclaration des variables globales pour stocker les références aux éléments HTML
let lignes;
let totalPricePart;
let totalGen;

// Fonction pour initialiser les références aux éléments HTML une fois la page chargée
function initElements() {
  lignes = document.getElementsByClassName("ligne");
  totalPricePart = document.getElementsByClassName("totalPrice");
  totalGen =
    document.getElementById("totalGenPrice").childNodes[1].childNodes[3];
}

// Mise à jour de chaque ligne de pièces
export function updateLignes() {
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
  let totalGenValue = 0;
  for (let index = 0; index < totalPricePart.length; index++) {
    totalGenValue += Number(totalPricePart[index].children[1].innerHTML);
  }

  totalGenValue = Math.round(totalGenValue * 100) / 100;
  totalGen.innerHTML = totalGenValue;
}

// Fonction éxécutée si action sur bouton select ou quantité
const buttonClickHandler = () => {
  updateLignes();
  totalGenPrice();
};

document.addEventListener("DOMContentLoaded", function () {
  // Appel de la fonction pour initialiser les références aux éléments HTML
  initElements();

  // conteneur pour la modification des quantités
  const container = document.getElementById("partList");

  // Surveillance du container
  container.addEventListener("change", buttonClickHandler);

  // Mise à jour des prix totaux lors du chargement de la page
  updateLignes();
});

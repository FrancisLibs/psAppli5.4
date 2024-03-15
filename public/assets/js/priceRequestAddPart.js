// Ajouter une ligne dans le tableau de pièces
function addSelectPart(e) {
  // Récupération du code de la pièce
  const partCode = e.target.innerText;
  
// Appel ajax pour la pièce à ajouter
  const url = `/part/ajaxPart/${partCode}?ajax=true`;
  fetch(url)
    .then((response) => {
      if (!response.ok) {
        throw new Error(
          "Une erreur est survenue lors de la récupération des pièces."
        );
      }
      return response.json();
    })
    .then((data) => {
      part = data;
    })
    .catch((error) => {
      console.error(error.message);
    });

  // Construction de la ligne du tableau de pièces
  const tr = document.createElement("tr");
  const td = document.createElement("td");
  
}

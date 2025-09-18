let cadre = document.getElementById("infoFrame");

if (nbOfLateBT > 11 || partsToBuy > 21) {
  // Ajoutez la classe "flashing-border" à l'élément que vous souhaitez encadrer.
  cadre.classList.add("flashing-border");
} else {
  cadre.classList.add("greenBorder");
}

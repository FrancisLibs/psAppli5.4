function cligno() {
  if (divClignotante.style.visibility == "visible") {
    divClignotante.style.visibility = "hidden";
  } else {
    divClignotante.style.visibility = "visible";
  }
}

let divClignotante = document.querySelector("#divClignotante");
if (divClignotante) {
  setInterval(cligno, 800);
}

let divClignotante = document.querySelector("#divClignotante");

function cligno() {
  if (divClignotante.style.visibility == "visible") {
    divClignotante.style.visibility = "hidden";
  } else {
    divClignotante.style.visibility = "visible";
  }
}

if (divClignotante) {
  setInterval(cligno, 800);
}

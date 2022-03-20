function cligno() {
  let divClignotante = document.querySelector("#divClignotante");

  if (divClignotante.style.visibility == "visible") {
    divClignotante.style.visibility = "hidden";
  } else {
    divClignotante.style.visibility = "visible";
  }
}

setInterval(cligno, 800);

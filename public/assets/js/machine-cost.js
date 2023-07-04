// Gestion des dates d'affichage
const form = document.getElementById("formDate");
const startDate = form.elements["start-date"];
const endDate = form.elements["end-date"];

const january = "01";
const today = new Date();
const todayTimeStamp = Date.now();
const firstDay = "01";
const firstMonth = january;
let day = today.getDate();
let month = today.getMonth() + 1;
const year = today.getFullYear();

// les inputs de date nécessitent des chiffres avec 2 digits
day = day < 10 ? "0" + day : day;
month = month < 10 ? "0" + month : month;

// Remplissage des deux inputs avec les dates par défaut
function setStartDate() {
  startDate.value = `${year}-${firstMonth}-${firstDay}`; // Premier jour de l'année en cours
}

function setEndDate() {
  endDate.value = `${year}-${month}-${day}`; // Jour d'aujourd'hui
}

setStartDate();
setEndDate();

// Les deux dates correspondent à un intervalle de temps
function timeInterval() {
  if (startDate.value < endDate.value) {
    return true;
  }
}

function startDateProcess() {
  if (timeInterval()) {
    data = fetch()
  } else {
    return;
  }
}

function endDateProcess() {
  if (timeInterval()) {
    console.log("ok2");
  } else {
    return;
  }
}

// Déclaration des listeners sur les deux champs
startDate.addEventListener("input", startDateProcess);
endDate.addEventListener("input", endDateProcess);

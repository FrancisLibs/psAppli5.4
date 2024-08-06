let form = document.forms.workorder;

// Constantes
const request = form[2];
const realization = form[3];
const remark = form[4];
const startDate = form[5];
const startTime = form[6];
const endDate = form[7];
const endTime = form[8];
const durationDay = form[9];
const durationHour = form[10];
const durationMinute = form[11];
const toClose = form[15];
const warningTimeTextBox = document.getElementById("warning-time-text-box");
const warningDurationTextBox = document.getElementById(
  "warning-duration-text-box"
);
const completedAfterText = document.getElementById("completedAfterText");
const completedStandby = document.getElementById("completedStandby");

const standby = document.getElementById("workorder_standby");
let standbyText;
if (document.getElementById("standby-text")) {
  standbyText = document.getElementById("standby-text");
} else {
  standbyText = document.createElement("p");
}

// Vérification présence demande
function controlRequest() {
  if (request.value == "") {
    alert("Attention il n'y a rien dans le champ 'Demande'");
    return false;
  } else {
    return true;
  }
}

// Vérification présence réalisation
function controlRealization() {
  if (realization.value == "") {
    alert("Attention il n'y a rien dans le champ 'Réalisation'");
    return false;
  } else {
    return true;
  }
}

// Check if the start date is smaller than the end date
function compareDates(sDate, eDate) {
  return eDate >= sDate;
}

function timeConversion(milliseconds) {
  // Nombre de millisecondes dans une journée (24 heures * 60 minutes * 60 secondes * 1000 millisecondes)
  var days = Math.floor(milliseconds / (1000 * 60 * 60 * 24));
  // Nombre de millisecondes restantes après avoir pris en compte les jours
  var left = milliseconds % (1000 * 60 * 60 * 24);
  // Nombre d'heures restantes
  var hours = Math.floor(left / (1000 * 60 * 60));
  // Nombre de millisecondes restantes après avoir pris en compte les heures
  left = left % (1000 * 60 * 60);
  // Nombre de minutes restantes
  var minutes = Math.floor(left / (1000 * 60));
  return { days: days, hours: hours, minutes: minutes };
}

function normalState() {
  warningTimeTextBox.classList.add("invisible");
  warningDurationTextBox.classList.add("invisible");
  completedAfterText.classList.add("invisible");
  completedStandby.classList.add("invisible");
  standbyText.classList.add("invisible");
}

function errorTimeState() {
  warningTimeTextBox.classList.remove("invisible");
  completedAfterText.classList.remove("invisible");
  completedStandby.classList.add("invisible");
}

function errorDurationState() {
  warningDurationTextBox.classList.remove("invisible");
  completedAfterText.classList.remove("invisible");
  completedStandby.classList.add("invisible");
  standby.classList.remove("invisible");
  standbyText.classList.remove("invisible");
}

function duration(sDate, eDate) {
  // Calculer la différence entre les deux dates avec heures en millisecondes
  let milisecondsTimeDif = Math.abs(eDate - sDate);

  // Conversion en jours, heures, minutes
  var convertedTime = timeConversion(milisecondsTimeDif);

  // Affichage dans les cases durée
  durationDay.value = convertedTime.days;
  durationHour.value = convertedTime.hours;
  durationMinute.value = convertedTime.minutes;
}

function zeroDurationBox() {
  durationDay.value = 0;
  durationHour.value = 0;
  durationMinute.value = 0;
}

function removeEndDate() {
  endDate.value = "";
  endTime.value = "";
}

function durationBoxZero(days, hours, minutes) {
  let duration = days * 1 + hours * 1 + minutes * 1;
  if (duration == 0) {
    return true;
  } else {
    return false;
  }
}

function adjustTime() {
  // Construction of startDate
  let startTimeParts = startTime.value.split(":"); // Diviser la chaîne en heures : minutes
  // startTimeParts contiendra ["13", "02"]
  let hours = startTimeParts[0] * 1; // Convertir l'heure en nombre
  let minutes = startTimeParts[1] * 1; // Convertir les minutes en nombre

  // Création de la date avec l'année, le mois, le jour, l'heure et les minutes
  let newStartDate = new Date(startDate.value);
  newStartDate.setHours(hours); // Définir les heures
  newStartDate.setMinutes(minutes); // Définir les minutes

  // Add to startDate the day, hours and minutes from the duration boxes
  newStartDate.setDate(newStartDate.getDate() + durationDay.value * 1);
  newStartDate.setHours(
    newStartDate.getHours() + durationHour.value * 1,
    newStartDate.getMinutes() + durationMinute.value * 1
  );

  // Change the end date in the date boxes
  endDate.value = newStartDate.toISOString().slice(0, 10);
  endTime.value =
    (newStartDate.getHours() < 10 ? "0" : "") +
    newStartDate.getHours() +
    ":" +
    (newStartDate.getMinutes() < 10 ? "0" : "") +
    newStartDate.getMinutes();
}

function timeManagment() {
  // lecture des chjamps dates
  const sDate = new Date(startDate.value + "T" + startTime.value);
  const eDate = new Date(endDate.value + "T" + endTime.value);
  // Détection dates erronées
  if (eDate >= sDate) {
    // Si dates ok
    // Calcul de la durée entrre les deux dates pour remplir les case de durée
    duration(sDate, eDate);
    // L'état d'erreur est désactivé
    normalState();
    // La checkbox standby est décochée
    form[15].checked = false;
  } else {
    // Si les dates ne sont pas bonnes
    //les cases de durée sont mises à 0
    zeroDurationBox();
    // L'état erreur de date est activé
    errorTimeState();
    // L'état erreur de durée est activé
    errorDurationState();
    // La checkbox standby est cochée
    toClose.checked = true;
  }
}

function durationManagement(e) {
  // Remise à 0 des champs passés en négatif
  if (e.target.value < 0) {
    e.target.value = 0;
  }
  adjustTime();
  // Test si la durée est à 0
  let duration = durationBoxZero(
    durationDay.value,
    durationHour.value,
    durationMinute.value
  );
  // If the duration = 0
  if (duration) {
    errorDurationState();
    toClose.checked = true;
  } else {
    normalState();
    adjustTime();
    toClose.checked = false;
  }
}

// Surveillance des modifications des dates et heures (de form[5] à form[8])
for (let index = 5; index < 9; index++) {
  form[index].addEventListener("blur", timeManagment);
}

// Surveillance des modifications de la durée de l'intervention (form[10] form[12])
for (let index = 9; index < 12; index++) {
  form[index].addEventListener("change", durationManagement);
}

// // Surveillance de la case standby
standby.addEventListener("change", () => {
  if (standby.checked) {
    // Set to zero the duration box
    zeroDurationBox();
    // Set to zero the end time
    removeEndDate();
    // Affichage de : "Attention ce BT devra être complété..."
    completedAfterText.classList.remove("invisible");
  } else {
    // Effacement de : "Attention ce BT devra être complété..."
    completedAfterText.classList.add("invisible");
    // Display of the message "Pour sortir d el'état standby, mattre une durée"
    completedStandby.classList.remove("invisible");
    // The checkbox stay in the same state
    standby.checked = true;
  }
});

window.addEventListener("load", () => {
  const pageTitle = document.querySelector(".page-title");
  if (
    pageTitle.textContent == "Edition bon de travail" &&
    durationBoxZero(durationDay.value, durationHour.value, durationMinute.value)
  ) {
    standby.checked = true;
  } else {
    standby.checked = false;
    normalState();
  }
});

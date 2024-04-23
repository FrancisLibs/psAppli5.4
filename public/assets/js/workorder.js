let form = document.forms.workorder;

//Lecture des dates et heures de début et fin d'intervention
function readParams() {
  let params = {};
  // Valeurs formulaire
  params.startDate = form[5];
  params.startTime = form[6];
  params.endDate = form[7];
  params.endTime = form[8];
  params.toClose = form[9];
  params.durationDay = form[10];
  params.durationHour = form[11];
  params.durationMinute = form[12];

  // Valeurs calculées
  params.startHours = params.startTime.value.substr(0, 2);
  params.startMinutes = params.startTime.value.substr(3, 2);
  params.endHours = params.endTime.value.substr(0, 2);
  params.endMinutes = params.endTime.value.substr(3, 2);

  params.dateS = new Date(params.startDate.value);
  params.dateE = new Date(params.endDate.value);

  params.sDate = new Date(
    params.startDate.value + "T" + params.startTime.value
  );
  params.eDate = new Date(params.endDate.value + "T" + params.endTime.value);

  params.warningTextBox = document.getElementById("warning-text-box");

  params.completedAfterText = document.getElementById("completedAfterText");

  return params;
}

// Vérification présence demande
function controlRequest() {
  const request = form[2].value;
  if (request == "") {
    alert("Attention il n'y a rien dans le champ 'Demande'");
    return false;
  } else {
    return true;
  }
}

// Vérification présence réalisation
function controlRealization() {
  const realization = form[3].value;
  if (realization == "") {
    alert("Attention il n'y a rien dans le champ 'Réalisation'");
    return false;
  } else {
    return true;
  }
}

// Check if the start date is smaller than the end date
function compareDates() {
  const sDate = new Date(form[5].value + "T" + form[6].value);
  const eDate = new Date(form[7].value + "T" + form[8].value);
  console.log("sdate :" + sDate);
  console.log("eDate :" + eDate);

  return eDate >= sDate;
}

// Submit the form
form.addEventListener("submit", (e) => {
  e.preventDefault();
  // Check if dates ok
  if (checkDateOrder()) {
    // Check if request and action fields are ok
    if (controlRequest() && controlRealization()) {
      form.submit();
    }
  }
});

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
  document.getElementById("warning-text-box").classList.add("invisible");
  document.getElementById("completedAfterText").classList.add("invisible");
  document.getElementById("date-box-with-text").style.border = "blue solid 1px";
  form[9].checked = false;
}

function errorState() {
  document.getElementById("warning-text-box").classList.remove("invisible");
  document.getElementById("completedAfterText").classList.remove("invisible");
  document.getElementById("date-box-with-text").style.border = "red solid 3px";
  form[9].checked = true;
  form[10].value = 0;
  form[11].value = 0;
  form[12].value = 0;
}

function zeroDuration(){
  
}

function duration() {
  const sDate = new Date(form[5].value + "T" + form[6].value);
  const eDate = new Date(form[7].value + "T" + form[8].value);
  // Calculer la différence entre les deux dates avec heures en millisecondes
  let milisecondsTimeDif = Math.abs(eDate - sDate);

  // Conversion en jours, heures, minutes
  var convertedTime = timeConversion(milisecondsTimeDif);
  form[10].value = convertedTime.days;
  form[11].value = convertedTime.hours;
  form[12].value = convertedTime.minutes;
}

// Calcul du temps de l'intervention
function computeTime() {
  // Vérification de l'antériorité de la date de fin
  if (compareDates()) {
    // Normal state
    normalState();
    // fill intervention duration
    duration();
  } else {
    errorState();
  }
}

function adjustTime() {
  let params = readParams();

  // Construction of startDate
  let startTimeParts = params.startTime.value.split(":"); // Diviser la chaîne en heures et minutes

  // startTimeParts contiendra ["13", "02"]
  let hours = parseInt(startTimeParts[0], 10); // Convertir l'heure en nombre
  let minutes = parseInt(startTimeParts[1], 10); // Convertir les minutes en nombre

  // Création de la date avec l'année, le mois, le jour, l'heure et les minutes
  let startDate = new Date(params.startDate.value);
  startDate.setHours(hours); // Définir les heures
  startDate.setMinutes(minutes); // Définir les minutes

  // Add to startDate the day, hours and minutes from the duration boxes
  startDate.setDate(startDate.getDate() + parseInt(params.durationDay.value));
  startDate.setHours(
    startDate.getHours() + parseInt(params.durationHour.value),
    startDate.getMinutes() + parseInt(params.durationMinute.value)
  );

  // Change the end date in the date boxes
  params.endDate.value = startDate.toISOString().slice(0, 10);
  params.endTime.value =
    (startDate.getHours() < 10 ? "0" : "") +
    startDate.getHours() +
    ":" +
    (startDate.getMinutes() < 10 ? "0" : "") +
    startDate.getMinutes();

  compareDates() ? normalState() : errorState();
}

// Surveillance des modifications des dates et heures (de form[5] à form[8])
for (let index = 5; index < 9; index++) {
  form[index].addEventListener("blur", () => {
    computeTime();
    // adjustTime();
  });
}

// Surveillance des modifications de la durée de l'intervention (form[10] form[12])
for (let index = 10; index < 13; index++) {
  form[index].addEventListener("change", () => {
    if (form[index].value < 0) {
      form[index].value = 0;
    } else {
      adjustTime();
    }
  });
}

// Check the dates after loading the page
// window.onload = () => {

//   };

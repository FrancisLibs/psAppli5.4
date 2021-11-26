window.onload = () => {
  let button = document.querySelector("#btn_copy");
  let workorderForm = document.forms.workorder;
  let workorderFormRequest = workorderForm["workorder[request]"];
  let workorderFormImplementation = workorderForm["workorder[implementation]"];

  button.addEventListener("click", function (e) {
    e.preventDefault();
    workorderFormImplementation.value = workorderFormRequest.value;
  });
  // -------------------------------------------------------------------

  // Gestion du temps de l'intervention
  // Date et heure de début
  // Calcul de la durée si on a date et heure de début et date et heure de fin
  // Lecture des champs début intervention et création d'un dateTime
  function readParams() {
    let params = new Object();
    // Valeurs formulaire
    params.startDate = workorderForm["workorder[startDate]"].value;
    params.endDate = workorderForm["workorder[endDate]"].value;
    params.startTime = workorderForm["workorder[startTime]"].value;
    params.endTime = workorderForm["workorder[endTime]"].value;
    params.durationDay = workorderForm["workorder[durationDay]"].value;
    params.durationHour = workorderForm["workorder[durationHour]"].value;
    params.durationMinute =
      workorderForm["workorder[durationMinute]"].value;
    // Valeurs calculées
    params.heureD = params.startTime.substr(0, 2);
    params.minuteD = params.startTime.substr(3, 2);
    params.heureF = params.endTime.substr(0, 2);
    params.minuteF = params.endTime.substr(3, 2);

    params.dateD = new Date(params.startDate);
    params.dateF = new Date(params.endDate);
  
    return params;
  }

  // // Gestion du temps de l'intervention
  function calcTemps() {
    // Lecture des champs début intervention et création d'un dateTime
    let params = readParams();

    params.dateD.setHours(params.heureD);
    params.dateD.setMinutes(params.minuteD);
    params.dateF.setHours(params.heureF);
    params.dateF.setMinutes(params.minuteF);

    if (params.heureFin === "") {
      params.durationDay.value = 0;
      params.durationHour.value = 0;
      params.durationMinute.value = 0;
    }

    // Test si calcul possible il faut que l'heure de fin soit définie
    if (params.heureF && params.minuteF) {
      //Calcul de la durée
      let diffTemps = Math.abs(params.dateF - params.dateD);
      diffTemps = diffTemps / 1000;

      // Transformation en jours, heures, minutes, secondes
      let jours = Math.floor(diffTemps / 86400);
      let resteJour = diffTemps - jours * 86400;
      let heures = Math.floor(resteJour / 3600);
      let resteHeure = Math.floor(resteJour - heures * 3600);
      let minutes = Math.floor(resteHeure / 60);
      // let secondes = Math.floor(resteHeure - minutes * 60);

      // Affichage
      workorderForm["workorder[durationDay]"].value = jours;
      workorderForm["workorder[durationHour]"].value = heures;
      workorderForm["workorder[durationMinute]"].value = minutes;
    }
  }

  // Si modif case duration (jours, heures ou minutes) calcul des autres données
  function duration() {
    let params = readParams();
    // Création objet Date
    let date = new Date(params.startDate);
    // Convertion en time stamp
    date_timeStamp = date.getTime();

    // Ajout de l'heure et des minutes
    let hours = params.heureD * 60 * 60 * 1000;
    let minutes = params.minuteD * 60 * 1000;
    date_timeStamp = date_timeStamp + hours + minutes;

    // conversion des jours, heures, minutes en milisecondes
    let durationDay = params.durationDay * 24 * 60 * 60 * 1000;
    let durationHour = params.durationHour * 60 * 60 * 1000;
    let durationMinute = params.durationMinute * 60 * 1000;

    // Ajout du temps à la date et à l'heure de début
    let newDate = date_timeStamp + durationDay + durationHour + durationMinute;

    let newCalculateDate = new Date(newDate).toISOString().substring(0, 10);
    let newCalculateTime = new Date(newDate).toISOString().substring(11, 16);

    workorderForm["workorder[endDate]"].value = newCalculateDate;
    workorderForm["workorder[endTime]"].value = newCalculateTime;

    if (workorderForm["workorder[endTime]"].value == "") {
      workorderForm["workorder[endTime]"].value = 0;
    }
    if (workorderForm["workorder[durationDay]"].value == "") {
      workorderForm["workorder[durationDay]"].value = 0;
    }
    if (workorderForm["workorder[durationHour]"].value == "") {
      workorderForm["workorder[durationHour]"].value = 0;
    }
  }

  // Calcul des temps au moment du chargement de la page
  calcTemps();

  let end_time = document.querySelector("#workorder_endTime");
  end_time.addEventListener("change", calcTemps, false);

  let duration_form = document.querySelector("#duration_time");
  duration_form.addEventListener("change", duration, false);
};

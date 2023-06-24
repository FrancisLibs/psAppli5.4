window.onload = () => {
  let workorderForm = document.forms.workorder;
  let workorderFormRequest = workorderForm["workorder[request]"];
  let workorderFormImplementation = workorderForm["workorder[implementation]"];

  // -------------------------------------------------------------------
  // Gestion du temps de l'intervention
  // Date et heure de début
  // Calcul de la durée si on a date et l'heure de début et date et l'heure de fin
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
    params.durationMinute = workorderForm["workorder[durationMinute]"].value;
    // Valeurs calculées
    params.heureD = params.startTime.substr(0, 2);
    params.minuteD = params.startTime.substr(3, 2);
    params.heureF = params.endTime.substr(0, 2);
    params.minuteF = params.endTime.substr(3, 2);

    params.dateD = new Date(params.startDate);
    params.dateF = new Date(params.endDate);

    return params;
  }

  // Gestion du temps de l'intervention
  function calcTemps() {
    // Lecture des champs début intervention et création d'un dateTime
    let params = readParams();

    params.dateD.setHours(params.heureD);
    params.dateD.setMinutes(params.minuteD);
    params.dateF.setHours(params.heureF);
    params.dateF.setMinutes(params.minuteF);

    // Test si calcul possible : il faut que l'heure de fin soit définie
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

  //Si modif case duration (jours, heures ou minutes) calcul des autres données
  function duration() {
    let params = readParams();

    // Création objet Date
    let date = new Date(params.startDate);

    // Convertion en time stamp
    let dateTimeStamp = date.getTime();

    // Ajout de l'heure et des minutes
    let hours = params.heureD * 60 * 60 * 1000;
    let minutes = params.minuteD * 60 * 1000;
    dateTimeStamp = dateTimeStamp + hours + minutes;

    // conversion des jours, heures, minutes en milisecondes
    let durationDay = params.durationDay * 24 * 60 * 60 * 1000;
    let durationHour = params.durationHour * 60 * 60 * 1000;
    let durationMinute = params.durationMinute * 60 * 1000;

    // Ajout du temps à la date et à l'heure de début
    let newDate = dateTimeStamp + durationDay + durationHour + durationMinute;
    let newCalculateDate = new Date(newDate).toISOString().substring(0, 10);
    let newCalculateTime = new Date(newDate).toISOString().substring(11, 16);

    workorderForm["workorder[endDate]"].value = newCalculateDate;
    workorderForm["workorder[endTime]"].value = newCalculateTime;

    if (workorderForm["workorder[endTime]"].value === "") {
      workorderForm["workorder[endTime]"].value = 0;
    }
    if (workorderForm["workorder[durationDay]"].value === "") {
      workorderForm["workorder[durationDay]"].value = 0;
    }
    if (workorderForm["workorder[durationHour]"].value === "") {
      workorderForm["workorder[durationHour]"].value = 0;
    }
  }

  function modifEndDate() {
    // Lecture des champs début intervention et création d'un dateTime
    let params = readParams();
    if (params.endDate < params.startDate) {
      workorderForm["workorder[endDate]"].value =
        workorderForm["workorder[startDate]"].value;
    }
  }

  function verifEndDate() {
    // Lecture des champs début intervention et création d'un dateTime
    let params = readParams();
    if (params.endDate < params.startDate) {
      alert("Attention la date de fin est antérieure à la date de départ...");
    }
  }

  function checkValidForm(e) {
    // // Validation de la durée d'intervention
    calcTemps();
    if (
      workorderForm["workorder[durationDay]"].value == 0 &&
      workorderForm["workorder[durationHour]"].value == 0 &&
      workorderForm["workorder[durationMinute]"].value == 0
    ) {
      const reponse = confirm("Tu n'as pas mis de temps d'intervention, ok ?");
      if (reponse) {
        confirm("Ton BT restera ouvert jusqu'à la date de fin.");
      } else {
        e.preventDefault();
      }
    }
  }

  // Surveillance des modifications des dates
  let endTime = document.querySelector("#workorder_time");
  endTime.addEventListener("change", calcTemps, false);

  // Surveillance des modifications des temps de durée
  let durationForm = document.querySelector("#duration_time");
  durationForm.addEventListener("change", duration, false);

  // Surveillance de la date de départ pour donner la même à la date de fin
  let beginDate = workorderForm["workorder[startDate]"];
  beginDate.addEventListener("change", modifEndDate, false);

  // Surveillance de la date de fin qui doit être suppérieur à la date de départ
  let endDate = workorderForm["workorder[endDate]"];
  endDate.addEventListener("change", verifEndDate, false);

  // Surveillance de la validation du formulaire
  workorderForm.addEventListener("submit", checkValidForm, false);
};

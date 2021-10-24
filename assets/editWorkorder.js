window.onload = () => {
  // Gestion du bouton copier
  let button = document.querySelector("#btn_copy");
  let workorderForm = document.forms.workorder_edit;
  let workorderFormRequest = workorderForm["workorder_edit[request]"];
  let workorderFormImplementation =
    workorderForm["workorder_edit[implementation]"];
  button.addEventListener("click", function (e) {
    e.preventDefault();
    workorderFormImplementation.value = workorderFormRequest.value;
  });

  // Gestion du temps de l'intervention
  function calcTemps() {
    // Lecture des champs début intervention et création d'un dateTime
    let startDate = workorderForm["workorder_edit[startDate]"].value;
    let endDate = workorderForm["workorder_edit[endDate]"].value;
    let heureDebut = workorderForm["workorder_edit[startTime]"].value;
    let heureFin = workorderForm["workorder_edit[endTime]"].value;

    let heureD = heureDebut.substr(0, 2);
    let minuteD = heureDebut.substr(3, 2);
    let heureF = heureFin.substr(0, 2);
    let minuteF = heureFin.substr(3, 2);

    let dateD = new Date(startDate);
    let dateF = new Date(endDate);

    dateD.setHours(heureD);
    dateD.setMinutes(minuteD);
    dateF.setHours(heureF);
    dateF.setMinutes(minuteF);

    // Test si calcul possible
    if (heureF && minuteF) {
      //Calcul de la durée
      let diffTemps = Math.abs(dateF - dateD);
      diffTemps = diffTemps / 1000;

      // Transformation en jours, heures, minutes, secondes
      let jours = Math.floor(diffTemps / 86400);
      let resteJour = diffTemps - jours * 86400;
      let heures = Math.floor(resteJour / 3600);
      let resteHeure = Math.floor(resteJour - heures * 3600);
      let minutes = Math.floor(resteHeure / 60);
      let secondes = Math.floor(resteHeure - minutes * 60);

      // Affichage
      workorderForm["workorder_edit[durationDay]"].value = jours;
      workorderForm["workorder_edit[durationHour]"].value = heures;
      workorderForm["workorder_edit[durationMinute]"].value = minutes;
    }
  }

  // Calcul des temps lors de l'affichage de l'édition
  calcTemps();

  // Date et heure de début
  let timeZone = document.querySelector("#time_management");
  timeZone.addEventListener("change", calcTemps, false);
};

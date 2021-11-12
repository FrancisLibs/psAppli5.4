window.onload = () => {
  // Gestion du bouton copier entre demande et réalisation
  let button = document.querySelector("#btn_copy");
  let workorderForm = document.forms.workorder;
  let workorderFormRequest = workorderForm["workorder[request]"];
  let workorderFormImplementation = workorderForm["workorder[implementation]"];
  button.addEventListener("click", function (e) {
    e.preventDefault();
    workorderFormImplementation.value = workorderFormRequest.value;
  });

  // Gestion du temps de l'intervention
  // Date et heure de début
  // Calcul de la durée si on a date et heure de début et date et heure de fin
  let time_zone = document.querySelector("#time_management");
  time_zone.addEventListener("change", function () {
    // Lecture des champs début intervention et création d'un dateTime
    let startDate = workorderForm["workorder[startDate]"].value;
    let endDate = workorderForm["workorder[endDate]"].value;
    let heureDebut = workorderForm["workorder[startTime]"].value;
    let heureFin = workorderForm["workorder[endTime]"].value;

    let heureD = heureDebut.substr(0, 2);
    let minuteD = heureDebut.substr(3, 2);
    let heureF = heureFin.substr(0, 2);
    let minuteF = heureFin.substr(3, 2);

    let dateD = new Date(startDate.value);
    let dateF = new Date(endDate.value);

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
      workorderForm["workorder[durationDay]"].value = jours;
      workorderForm["workorder[durationHour]"].value = heures;
      workorderForm["workorder[durationMinute]"].value = minutes;
    }
  });

  // time_zone.addEventListener("keyUp", function (e) {
  //   e.preventDefault();
  //   // Lecture des valeurs de durée
  //   let durationDay = workorderForm["workorder[durationDay]"].value;
  //   let durationHour = workorderForm["workorder[durationHour]"].value;
  //   let durationMinute = workorderForm["workorder[durationMinute]"].value;

  //   if(durationDay > 0){

  //   }
  //   if (durationHour > 0) {
  //   }
  //   if (durationminute > 0) {
  //   }
  // });
};

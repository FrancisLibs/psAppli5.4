// Chargement ajax de la liste des machines de l'atelier sélectionné
window.onload = () => {
  // On va chercher l'atelier
  let workshop = document.querySelector("#workorder_workshop");

  workshop.addEventListener("change", function () {
    let form = this.closest("form");
    let data = this.name + "=" + this.value;
    
    fetch(form.action, {
      method: form.getAttribute("method"),
      body: data,
      headers: {
        "content-type": "application/x-www-form-urlencoded; charset: utf-8",
      },
    })
      .then((response) => response.text())
      .then((html) => {
        let content = document.createElement("html");
        console.log(content);
        content.innerHTML = html;
        
        let nouveauSelect = content.querySelector("#workorder_machine");
        document.querySelector("#workorder_machine").replaceWith(nouveauSelect);
      });
  });

  // Gestion du bouton copier
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
  let time_zone = document.querySelector("#time_management");
  time_zone.addEventListener("change", function () {
    // Lecture des champs début intervention et création d'un dateTime
    let startDate = document.querySelector("#workorder_startDate");
    let endDate = document.querySelector("#workorder_endDate");
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
};

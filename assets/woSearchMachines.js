// Chargement ajax de la liste des machines de l'atelier sélectionné
window.onload = () => {
  // On va chercher l'atelier
  let workshop = document.querySelector("#workorder_workshop");

  workshop.addEventListener("change", function () {
    let form = this.closest("form");
    let data = this.name + "=" + this.value;
    // console.log(data);
    // console.log(form.action);

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
  function getSelectValue(selectId) {
    var selectElmt = document.getElementById(selectId);
    return selectElmt.options[selectElmt.selectedIndex].value;
  }
  // Date et heure de début
  let jourDebut = getSelectValue("workorder_startDate_day");
  let moisDebut = getSelectValue("workorder_startDate_month");
  let anneeDebut = getSelectValue("workorder_startDate_year");
  let heureDebut = workorderForm["workorder[startTime]"].value;
  let heureD = heureDebut.substr(0, 2);
  let minuteD = heureDebut.substr(3, 2);
  let dateD = new Date();
  dateD.setDate(jourDebut);
  dateD.setMonth(moisDebut - 1);
  dateD.setFullYear(anneeDebut);
  dateD.setHours(heureD);
  dateD.setMinutes(minuteD);
  // console.log(dateD);

  // Durée de l'intervention
  let jourFin = getSelectValue("workorder_endDate_day");
  let moisFin = getSelectValue("workorder_endDate_month");
  let anneeFin = getSelectValue("workorder_endDate_year");
  let heureFin = workorderForm["workorder[endTime]"].value;
  let heureF = heureFin.substr(0, 2);
  let minuteF = heureFin.substr(3, 2);
  let dateF = new Date();
  dateF.setDate(jourFin);
  dateF.setMonth(moisFin - 1);
  dateF.setFullYear(anneeFin);
  dateF.setHours(heureF);
  dateF.setMinutes(minuteF);
  // console.log(dateF);
  let diffTemps = Math.abs(dateF - dateD);
  diffTemps = diffTemps / 1000;

  let jours = Math.floor(diffTemps / 86400);
  let resteJour = diffTemps - jours * 86400;

  let heures = Math.floor(resteJour / 3600);
  let resteHeure = Math.floor(resteJour - heures * 3600);

  let minutes = Math.floor(resteHeure / 60);
  let secondes = Math.floor(resteHeure - minutes * 60);

  console.log("diffTemps " + diffTemps);
  console.log("jours " + jours);
  console.log("heures " + heures);
  console.log("minutes " + minutes);
  console.log("secondes " + secondes);
};

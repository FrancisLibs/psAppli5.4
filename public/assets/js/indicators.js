// Gestion des dates d'affichage
document.addEventListener("DOMContentLoaded", function () {
  const form = document.getElementById("formDate");
  const startDateForm = form.elements["search_indicator[startDate]"];
  const endDateForm = form.elements["search_indicator[endDate]"];

  // Gestion des boutons d'incrémentation/décrémentation de la date début
  currentDate = new Date();
  const dateButtonsStart = document.querySelectorAll(".date-start");
  dateButtonsStart.forEach(function (button) {
    button.addEventListener("click", function () {
      const attribute = button.getAttribute("data-increment");
      let newDate = new Date(startDateForm.value);
      switch (attribute) {
        case "J+":
          newDate.setDate(newDate.getDate() + 1);
          break;
        case "M+":
          newDate.setMonth(newDate.getMonth() + 1);
          break;
        case "Y+":
          newDate.setFullYear(newDate.getFullYear() + 1);
          break;
        case "J-":
          newDate.setDate(newDate.getDate() - 1);
          break;
        case "M-":
          newDate.setMonth(newDate.getMonth() - 1);
          break;
        case "Y-":
          newDate.setFullYear(newDate.getFullYear() - 1);
          break;
        default:
          console.log("il n'y a pas de possibilité");
      }
      updateDateInput(startDateForm, newDate);
    });
  });

  const dateButtonsEnd = document.querySelectorAll(".date-end");
  dateButtonsEnd.forEach(function (button) {
    button.addEventListener("click", function () {
      const attribute = button.getAttribute("data-increment");
      let newDate = new Date(endDateForm.value);
      switch (attribute) {
        case "J+":
          newDate.setDate(newDate.getDate() + 1);
          break;
        case "M+":
          newDate.setMonth(newDate.getMonth() + 1);
          break;
        case "Y+":
          newDate.setFullYear(newDate.getFullYear() + 1);
          break;
        case "J-":
          newDate.setDate(newDate.getDate() - 1);
          break;
        case "M-":
          newDate.setMonth(newDate.getMonth() - 1);
          break;
        case "Y-":
          newDate.setFullYear(newDate.getFullYear() - 1);
          break;
        default:
          console.log("il n'y a pas de possibilité");
      }
      updateDateInput(endDateForm, newDate);
    });
  });

  function updateDateInput(input, date) {
    var formattedDate = date.toISOString().split("T")[0]; // Format ISO 8601 pour les champs de type "text"
    input.value = formattedDate;
  }
});

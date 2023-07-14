// Gestion des dates d'affichage
const form = document.getElementById("formDate");
const startDate = form.elements["search_indicator[startDate]"];
const endDate = form.elements["search_indicator[endDate]"];

const today = new Date();
let day = today.getDate();
let month = today.getMonth() + 1;
const year = today.getFullYear();

// date inputs need 2 digits
day = day < 10 ? "0" + day : day;
month = month < 10 ? "0" + month : month;

const dateDuJour = year + "-" + month + "-" + day;

if (endDate.value == "") {
  endDate.value = dateDuJour;
}

if (startDate.value == "") {
  startDate.value = "2022-01-01";
}



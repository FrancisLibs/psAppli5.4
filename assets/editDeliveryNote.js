// Lors d'un nouveau bon de livraison, le numéro de BL
// et de sa date sont mis en session par ajax

function saveNumber() {
  let routeClass = document.querySelector(".deliveryNoteRoute");
  let numberRoute = routeClass.dataset.urlDeliverynotenumber;

  let number = blNumber.value;
  if (number == "") {
    number = null;
  }
  const url = numberRoute + "/" + number;
  console.log(url);
  const message = fetch(url)
    .then((resultat) => resultat.json())
    .then((json) => {
      json.message;
    });
  console.log(message);
}

function saveDate() {
  let routeClass = document.querySelector(".deliveryNoteRoute");
  let dateRoute = routeClass.dataset.urlDeliverynotedate;

  let date = blDate.value;
  const url = dateRoute + "/" + date;
  console.log(url);
  const message = fetch(url)
    .then((resultat) => resultat.json())
    .then((json) => {
      json.message;
    });
  console.log(message);
}

let blForm = document.forms.delivery_note;
let blNumber = blForm["delivery_note[number]"];
let blDate = blForm["delivery_note[date]"];

blNumber.addEventListener("change", saveNumber, false);
blDate.addEventListener("change", saveDate, false);

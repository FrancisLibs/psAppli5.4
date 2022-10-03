let blForm = document.forms.delivery_note;
let blNumber = blForm["delivery_note[number]"];
let blDate = blForm["delivery_note[date]"];

// Lors d'un nouveau bon de livraison, le numéro de BL
// et de sa date sont mis en session par ajax
function saveNumber() {
  let routeClass = document.querySelector(".deliveryNoteRoute");
  let numberRoute = routeClass.dataset.urlDeliverynotenumber;

  let number = blNumber.value;
  if (number === "") {
    number = null;
  }
  const url = numberRoute + "/" + number;
  const message = fetch(url)
    .then((resultat) => resultat.json())
    .then((json) => {
      json.message;
    });
}

function saveDate() {
  let routeClass = document.querySelector(".deliveryNoteRoute");
  let dateRoute = routeClass.dataset.urlDeliverynotedate;

  let date = blDate.value;
  const url = dateRoute + "/" + date;
  const message = fetch(url)
    .then((resultat) => resultat.json())
    .then((json) => {
      json.message;
    });
}

blNumber.addEventListener("change", saveNumber, false);
blDate.addEventListener("change", saveDate, false);

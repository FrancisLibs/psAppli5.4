function saveNumber() {
  let number = blNumber.value;
  if (number == "") {
    number = null;
  }
  const url = "https://127.0.0.1:8000/delivery/note/saveNumber/" + number;
  const message = fetch(url)
    .then((resultat) => resultat.json())
    .then((json) => {
      json.message;
    });
  console.log(message);
}

function saveDate() {
  let date = blDate.value;
 
  const url = "https://127.0.0.1:8000/delivery/note/saveDate/" + date;
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

document.addEventListener("DOMContentLoaded", () => {
  let blForm = document.forms.delivery_note;
  let blNumber = blForm["delivery_note[number]"];
  let blDate = blForm["delivery_note[date]"];

  async function saveNumber() {
    let routeClass = document.querySelector(".deliveryNoteRoute");
    let numberRoute = routeClass.dataset.urlDeliverynotenumber;

    let number = blNumber.value || null;
    const url = numberRoute + "/" + number;

    try {
      let resultat = await fetch(url);
      let json = await resultat.json();
      console.log("Message serveur (number) :", json.message);
    } catch (e) {
      console.error("Erreur (number) :", e);
    }
  }

  async function saveDate() {
    let routeClass = document.querySelector(".deliveryNoteRoute");
    let dateRoute = routeClass.dataset.urlDeliverynotedate;

    let date = blDate.value;
    const url = dateRoute + "/" + date; 

    try {
      let resultat = await fetch(url);
      let json = await resultat.json();
      console.log("Message serveur (date) :", json.message);
    } catch (e) {
      console.error("Erreur (date) :", e);
    }
  }

  blNumber.addEventListener("change", saveNumber, false);
  blDate.addEventListener("change", saveDate, false);
});

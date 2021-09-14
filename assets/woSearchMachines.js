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
};

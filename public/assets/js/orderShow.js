document.addEventListener("DOMContentLoaded", function () {
  const orderId_container = document.getElementById("order-container");
  const orderId = orderId_container.dataset.orderId;
  const orderLetters = document
    .querySelector("#accountLetters")
    .textContent.split("");
  const buttonArea = document.getElementById("buttonArea");

  const config = {
    P: { label: "Petit matériel", route: "/accueil" },
    D: {
      label: "Pièces détachées",
      route: "/part/list/orderAddPart" + "/" + orderId,
    },
    I: { label: "Interventions", route: "/clients" },
    M: { label: "Main d'oeuvre", route: "/pieces-detachees" },
  };

  buttonArea.innerHTML = ""; // Clear existing buttons if any

  orderLetters.forEach((letter) => {
    if (config[letter]) {
      const btn = document.createElement("button");
      btn.textContent = config[letter].label;
      btn.classList.add("btn", "btn-primary", "btn-sm");
      btn.onclick = function () {
        window.location.href = config[letter].route;
      };
      buttonArea.appendChild(btn);
    }
  });
});

window.addEventListener("load", () => {
  const standby = document.getElementById("standby-tag");
  const standbyText = document.getElementById("standby-text");

  if (standby.textContent === "Standby") {
    console.log(1);
    standbyText.classList.remove("invisible");
  }
});

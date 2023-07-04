window.onload = () => {
  const buttons = document.querySelectorAll(".button_title");

  function changeClass() {
    const parent= this.parentNode.parentNode;
    content = parent.querySelector('.content');
    content.classList.toggle("is-visible");
  }

  buttons.forEach(function (button) {
    button.addEventListener('click', changeClass)
  });
};

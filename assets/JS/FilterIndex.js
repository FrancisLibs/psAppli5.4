/**
 * @property {HTMLElement} content
 * @property {HTMLElement} pagination
 * @property {HTMLElement} sorting
 * @property {HTMLFormElement} form
 */
export class FilterIndex {
  /**
   * @param {HTMLElement|null} element
   */
  constructor(element) {
    if (element === null) {
      return;
    }

    this.form = element.querySelector(".js-filter-form");
    this.content = element.querySelector(".js-filter-content");
    this.sorting = element.querySelector(".js-filter-sorting");
    this.pagination = element.querySelector(".js-filter-pagination");
    this.bindEvents();
  }

  /**
   * Ajoute les comportements aux différents éléments
   */
  bindEvents() {
    const aClickListener = (e) => {
      if (e.target.tagName === "A") {
        e.preventDefault();
        this.loadUrl(e.target.getAttribute("href"));
      }
    };

    const resetField = (e) => {
      e.preventDefault();
      const typeChamp =
        e.target.parentNode.parentNode.childNodes[1].children[0].firstChild
          .nodeName;
      let champAReseter = e.target.parentNode.parentNode;
      let nomDuChampAReseter = "";

      switch (typeChamp) {
        case "SELECT":
          // reset du champ de recherche SELECT
          champAReseter = champAReseter.querySelector("select");
          nomDuChampAReseter = champAReseter.getAttribute("name");
          champAReseter.selectedIndex = 0;
          break;
        case "INPUT":
          // reset du champ de recherche INPUT
          champAReseter = champAReseter.querySelector("input");
          nomDuChampAReseter = champAReseter.getAttribute("name");
          champAReseter.value = "";
          break;
      }

      // Appel de la fonction de mise à jour de l'écran
      let newUrl = new URL(window.location);
      let params = new URLSearchParams(window.location.search);
      params.set(nomDuChampAReseter, "");
      newUrl.search = params.toString();
      this.loadUrl(newUrl);
    };

    const resetButton = this.form.querySelectorAll(".resetBtn");
    resetButton.forEach((button) => {
      button.addEventListener("click", resetField);
    });

    this.sorting.addEventListener("click", aClickListener);
    this.pagination.addEventListener("click", aClickListener);

    const inputs = this.form.querySelectorAll("input");

    const inputForm = this.form.querySelectorAll("input");
    inputForm.forEach((input) => {
      input.addEventListener("keyup", this.loadForm.bind(this));
    });

    const selectForm = this.form.querySelectorAll("select");
    selectForm.forEach((select) => {
      select.addEventListener("change", this.loadForm.bind(this));
    });

    const checkbox = this.form.querySelectorAll("input");
    checkbox.forEach((checkbox) => {
      checkbox.addEventListener("change", this.loadForm.bind(this));
    });
  }

  async loadForm() {
    const data = new FormData(this.form);

    const url = new URL(
      this.form.getAttribute("action") || window.location.href
    );

    const params = new URLSearchParams();
    data.forEach((value, key) => {
      params.append(key, value);
    });
    return this.loadUrl(url.pathname + "?" + params.toString());
  }

  async loadUrl(url) {
    const ajaxUrl = url + "&ajax=1";
    const response = await fetch(ajaxUrl, {
      headers: {
        "X-Requested-With": "XMLHttpRequest",
      },
    });
    if (response.status >= 200 && response.status < 300) {
      const data = await response.json();
      this.content.innerHTML = data.content;
      this.sorting.innerHTML = data.sorting;
      this.pagination.innerHTML = data.pagination;
      history.replaceState({}, "", url);
    }
  }
}

/**
 * @property {HTMLElement} content
 * @property {HTMLElement} pagination
 * @property {HTMLElement} sorting
 * @property {HTMLFormElement} form
 */
export class PartIndex {
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

    console.log("ok");
    console.log(this.content);
    console.log(this.sorting);
    console.log(this.pagination);

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

    this.sorting.addEventListener("click", aClickListener);
    this.pagination.addEventListener("click", aClickListener);

    const inputForm = this.form.querySelectorAll("input");
    inputForm.forEach((input) => {
      input.addEventListener("keyup", this.loadForm.bind(this));
    });

    const selectForm = this.form.querySelectorAll("select");
    selectForm.forEach((select) => {
      select.addEventListener("change", this.loadForm.bind(this));
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

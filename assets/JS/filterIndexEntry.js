import { FilterIndex } from "./FilterIndex";

const filterEl = document.querySelector(".js-filter");
if (filterEl) {
  new FilterIndex(filterEl);
}

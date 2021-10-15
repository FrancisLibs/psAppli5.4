/*
 * Welcome to your app's main JavaScript file!
 *
 * We recommend including the built version of this JavaScript file
 * (and its CSS file) in your base layout (base.html.twig).
 */

// any CSS you import will output into a single css file (app.css in this case)
import './styles/app.css';

// start the Stimulus application
import './bootstrap';

import { PartIndex } from "./JS/PartIndex";
new PartIndex(document.querySelector(".part-js-filter"));

import { MachineIndex } from "./JS/MachineIndex";
new MachineIndex(document.querySelector(".machine-js-filter"));

import { WorkorderIndex } from "./JS/WorkorderIndex";
new WorkorderIndex(document.querySelector(".workorder-js-filter"));
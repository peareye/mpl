/**
 * PitonCMS (https://github.com/PitonCMS)
 *
 * @link      https://github.com/PitonCMS/Piton
 * @copyright Copyright 2018 Wolfgang Moritz
 * @license   https://github.com/PitonCMS/Piton/blob/master/LICENSE (MIT License)
 */

 /**
 * Piton Front End JS
 */

import { pitonConfig } from './modules/config.js';
import { postXHRPromise } from "./modules/xhrPromise.js";

// Set the contact honeypot to a known value
const honeypotValue = "alt@example.com";
document.querySelectorAll(`input[name="alt-email"]`).forEach(input => {
  input.setAttribute("value", honeypotValue);
});

/**
 * Contact Submit Message Request
 *
 * @param {Event} event
 */
const contactSubmitMessage = function(event) {
  if (!(event.target.dataset.contactForm === "true")) return;
  event.preventDefault();

  // Check honeypot if available
  if (event.target.querySelector(".alt-email") && event.target.querySelector(".alt-email").value !== honeypotValue) return;

  // Set indicator of work in progress
  let buttonText = (event.target.dataset.contactFormButtonText) ? event.target.dataset.contactFormButtonText : "Sending...";
  event.target.querySelector(`button[type="submit"]`).innerHTML = buttonText;

  postXHRPromise(pitonConfig.routes.submitMessage, new FormData(event.target))
    .then(text => {
      event.target.innerHTML = `<p>${text}</p>`;
    })
    .catch(error => {
      event.target.innerHTML = `<p>${error}</p>`;
    });
}

document.addEventListener("submit", contactSubmitMessage, false);

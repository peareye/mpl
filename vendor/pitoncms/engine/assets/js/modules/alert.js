/**
 * PitonCMS (https://github.com/PitonCMS)
 *
 * @link      https://github.com/PitonCMS/Piton
 * @copyright Copyright 2018 Wolfgang Moritz
 * @license   https://github.com/PitonCMS/Piton/blob/master/LICENSE (MIT License)
 */

/**
 * Display system alert messages module
 */

import { pitonConfig } from './config.js';

const alertContainer = document.querySelector(`[data-alert-modal="true"]`);

/**
 * Dismiss Inline Alert
 *
 * @param {Event} event
 */
const dismissAlertInlineMessage = function(event) {
    if (event.target.dataset.dismiss === "alert") {
        event.target.closest(`[data-alert="container"]`)?.remove();
    }
}

/**
 * Display Inline HTML Message Alert
 *
 * @param {string} severity Severity color code
 * @param {string} heading  Message heading
 * @param {mixed} message   Message text or object
 */
const alertInlineMessage = function(severity, heading, message) {
    // Stringify message
    if (Array.isArray(message) && message !== null) {
        message = message.join("<br>");
    } else if (message instanceof Error) {
        message = message.message;
    } else if (typeof message === "object" && message !== null) {
        message = Object.values(message).join("<br>");
    } else {
        message = String(message);
    }

    // Insert into inline alert container
    if (alertContainer) {
        // Create element and insert alert HTML and update with alert data
        let container = document.createElement("div");
        container.innerHTML = pitonConfig.alertInlineHTML;
        container.querySelector(`[data-alert="container"]`).classList.add("alert-" + severity);
        container.querySelector(`[data-alert="heading"]`).innerHTML = heading;
        container.querySelector(`[data-alert="content"]`).innerHTML = message;

        alertContainer.insertAdjacentHTML('afterbegin', container.innerHTML);
        window.scrollTo(0,0);
    } else {
        // If alert container does not exist, then use standard JS alert
        alert(message);
    }

}

document.addEventListener("click", dismissAlertInlineMessage, false);

export { alertInlineMessage };
/**
 * PitonCMS (https://github.com/PitonCMS)
 *
 * @link      https://github.com/PitonCMS/Piton
 * @copyright Copyright 2018 Wolfgang Moritz
 * @license   https://github.com/PitonCMS/Piton/blob/master/LICENSE (MIT License)
 */

/**
 * Open, Load, and Dismiss Modal Window Module
 */

import { pitonConfig } from './config.js';

/**
 * Get Modal
 *
 * Returns reference to modal content node, but only after inserted into DOM by loadModalContent()
 */
const getModal = function() {
    return document.querySelector(`[data-modal="content"]`);
}

/**
 * Load Modal (Background)
 *
 * Call first if request requires processing time before content is available to load in loadModalContent
 */
const loadModal = function() {
    document.body.insertAdjacentHTML("afterbegin", pitonConfig.modalBackgroundHTML);
}

/**
 * Load Modal Content and Display
 *
 * Loads modal background if not already loaded
 * @param {string} header
 * @param {string} body
 */
const loadModalContent = function(header, body) {
    // Create temp new div element and load modal HTML string to make live
    let container = document.createElement("div");
    container.insertAdjacentHTML("afterbegin", pitonConfig.modalContentHTML);

    // Insert the modal title in the header element
    container.querySelector(`[data-modal="header"]`).insertAdjacentHTML("afterbegin", header);

    // Determine if the body is text HTML to be turned into a DOM element, or is already a Node
    if (typeof body === "string") {
        container.querySelector(`[data-modal="body"]`).insertAdjacentHTML("afterbegin", body);
    } else if (typeof body === "object" && body instanceof Node) {
        container.querySelector(`[data-modal="body"]`).append(body)
    }

    // Load modal background if it does not yet exit in the DOM
    if (document.querySelector(`[data-modal="modal"]`) === null) {
        loadModal();
    }

    // Insert into the background modal div, stripping off the temp container div container
    document.querySelector(`[data-modal="modal"]`).append(container.firstChild);
}

/**
 * Remove Modal and Contents
 */
const removeModal = function() {
    document.querySelector(`[data-modal="modal"]`)?.remove();
}

/**
 * Remove Modal (Event)
 * @param {Event} event
 */
const removeModalEvent = function(event) {
    if (!(event.target.dataset.modal === "modal" || event.target.dataset.modal === "dismiss")) return;
    removeModal();
}

// Bind modal events
document.addEventListener("click", removeModalEvent, false);

export { getModal, loadModal, loadModalContent, removeModal };

/**
 * PitonCMS (https://github.com/PitonCMS)
 *
 * @link      https://github.com/PitonCMS/Piton
 * @copyright Copyright 2018 Wolfgang Moritz
 * @license   https://github.com/PitonCMS/Piton/blob/master/LICENSE (MIT License)
 */

/**
 * Media Select Modal Module
 *
 * Allows selecting media for use.
 * Loads modal with available media with search and filter controls.
 */

import { pitonConfig } from './config.js';
import { setQueryRequestPath } from "./filter.js";
import { loadModal, loadModalContent, removeModal } from './modal.js';
import { getXHRPromise } from './xhrPromise.js';
import { alertInlineMessage } from './alert.js';

// Set filter query end point
setQueryRequestPath(pitonConfig.routes.adminMediaGet + "static");

/**
 * Event to dispatch pseudo "input" event on hidden inputs
 */
const inputEvent = new Event("input", {"bubbles": true});

/**
 * Target element
 */
let targetElement = null;

/**
 * Flag (bool) whether return and set object data (true, default), or return the media path and filename (false)
 */
let returnObject = true;

/**
 * Set Target Element
 *
 * This is the element to set the selected media
 * @param {HTMLElement} element
 */
const setTargetElement = function (element) {
    targetElement = element;
}

/**
 * Get Target Element
 *
 * This is the element to set the selected media
 * @param void
 */
const getTargetElement = function () {
    return targetElement;
}

/**
 * Opens Modal with Media Images for Select
 * @param void
 */
const openMediaModal = function() {
    // Load modal background first to show something is happening as XHR get request processes
    loadModal();
    getXHRPromise(pitonConfig.routes.adminMediaGet + "static")
        .then(data => {
            // Get the media controls and wrapper
            getXHRPromise(pitonConfig.routes.adminMediaControlsGet)
                .then(controls => {
                    // Create element to inject HTML string into to get this live
                    let container = document.createElement("div");
                    container.classList.add("modal-container");
                    // Set data-media-select-modal="true" as selector
                    container.dataset.mediaSelectModal = true;
                    container.insertAdjacentHTML("afterbegin", controls);

                    // Find the query filter content div to inject media results from first query
                    container.querySelector(`[data-query="content"]`).insertAdjacentHTML("afterbegin", data);

                    return container;
                })
                .then(mediaHtml => {
                    loadModalContent("Select Media", mediaHtml);
                });
        })
        .catch((error) => {
            removeModal();
            alertInlineMessage("danger", "Failed to Launch Media Modal", error);
        });
}

/**
 * Media Input Select Modal
 *
 * Launches media select modal attached to Piton form media inputs
 * @param {Event} event
 */
const mediaInputSelectModal = function (event) {
    if (event.target.dataset.mediaModal) {
        // Save reference to target element and load modal
        setTargetElement(event.target.closest(`[data-media-select="true"]`))
        openMediaModal();
        returnObject = true;
    }
}

/**
 * Media Input Selected listener
 *
 * Listens for click event when a media file is selected
 * Sets selected media file in form input
 * @param {Event} event
 */
const mediaInputSelectedListener = function (event) {
    if (!(event.target.closest(`[data-media-card="true"]`) && event.target.closest(`[data-media-select-modal]`))) return;
    if (!returnObject) return;

    // Get media data and set in form
    let mediaCard = event.target.closest(`[data-media-card="true"]`);
    let data = {
        "id": mediaCard.dataset.mediaId,
        "caption": mediaCard.dataset.mediaCaption,
        "filename": mediaCard.dataset.mediaFilename
    }

    // Set ID, filename and relative path, an caption in target element
    let targetInput = getTargetElement().querySelector(`input[name*="media_id"]`);
    let targetImg = getTargetElement().querySelector("img");

    targetInput.value = data.id;
    targetImg.src = data.filename;
    targetImg.alt = data.caption;
    targetImg.title = data.caption;
    targetImg.classList.remove("d-none");

    // Dispatch input event on hidden field
    targetInput.dispatchEvent(inputEvent);

    removeModal();
}

/**
 * Media Input Clear
 *
 * Clears media set in input
 * @param {Event} event
 */
const mediaInputClear = function (event) {
    if (event.target.dataset.mediaClear) {
        // Clear media input form
        let targetInput = event.target.closest(`[data-media-select="true"]`).querySelector(`input[name*="media_id"]`);
        let targetImg = event.target.closest(`[data-media-select="true"]`).querySelector("img");

        targetInput.value = "";
        targetImg.src = "";
        targetImg.alt = "";
        targetImg.title = "";
        targetImg.classList.add("d-none");

        // Dispatch input event to trigger save button state
        targetInput.dispatchEvent(inputEvent);
    }
}

/**
 * Media CKEditor Select Modal
 *
 * Opens media select modal, returns selected media file or null
 * @param {void}
 */
const mediaCKEditorSelectModal = function () {
    returnObject = false;
    openMediaModal();
}

/**
 * Media CKEditor Media Click Listener
 *
 * Listens for click event when a media file is selected in the media modal while editing text
 * Sets selected media filename text editor
 * @param {editor} editor
 */
const mediaCKEditorSelectedListener = function (editor) {

    /**
     * CKEditor Media Select Click Handler
     * @param {event} event
     */
    const mediaClickBody = function (event) {
        if ((event.target.closest(`[data-media-card="true"]`) && event.target.closest(`[data-media-select-modal]`)) && !returnObject) {
            let mediaCard = event.target.closest(`[data-media-card="true"]`);
            returnObject = true;
            removeModal();

            // Set media in editor
            if (mediaCard.dataset) {
                editor.model.change(writer => {
                    const mediaElement = writer.createElement('image', {
                        src: mediaCard.dataset.mediaFilename,
                        alt: mediaCard.dataset.mediaCaption
                    });

                    // Add caption if set
                    if (mediaCard.dataset.mediaCaption) {
                        const captionElement = writer.createElement('caption');
                        writer.appendText(mediaCard.dataset.mediaCaption, captionElement);
                        writer.append( captionElement, mediaElement );
                    }

                    // Insert the image in the current selection location.
                    editor.model.insertContent(mediaElement, editor.model.document.selection);

                    // Remove the click handler from document. This mediaClickBody will be added again on the next toolbar select media click
                    document.removeEventListener("click", mediaClickBody, false);

                    // No need to dispatch input event when working in the text editor
                });
            } else {
                throw new Error("Piton: mediaCard.dataset not set.");
            }
        }
    }

    // Return this in callback submitted to CKEditor plugin
    return function() {
        document.addEventListener("click", mediaClickBody, false);
    }
}

document.addEventListener("click", mediaInputSelectModal, false);
document.addEventListener("click", mediaInputClear, false);
document.addEventListener("click", mediaInputSelectedListener, false);

export { mediaCKEditorSelectModal, mediaCKEditorSelectedListener };
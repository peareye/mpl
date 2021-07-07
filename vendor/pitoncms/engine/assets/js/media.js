/**
 * PitonCMS (https://github.com/PitonCMS)
 *
 * @link      https://github.com/PitonCMS/Piton
 * @copyright Copyright 2018 Wolfgang Moritz
 * @license   https://github.com/PitonCMS/Piton/blob/master/LICENSE (MIT License)
 */

/**
 * Manage Media JS
 */

import './modules/main.js';
import './modules/mediaUpload.js';
import { pitonConfig } from './modules/config.js';
import { enableSpinner, disableSpinner } from './modules/spinner.js';
import { postXHRPromise } from './modules/xhrPromise.js';
import { alertInlineMessage } from './modules/alert.js';
import { setQueryRequestPath } from "./modules/filter.js";
import { dragStartHandler, dragEnterHandler, dragOverHandler, dragLeaveHandler, dragDropHandler, dispatchInputEventOnMovedElement } from './modules/drag.js';

// Set query path end point
setQueryRequestPath(pitonConfig.routes.adminMediaGet + "edit");

// Reference to <span> that contains text suggestion to move media order when a category has been selected
const draggableMessage = document.querySelector(`[data-drag-media-message="true"]`);

/**
 * Get Category ID Filter Value
 *
 * From filter options control
 */
const getFlterCategoryId = function () {
    return document.querySelector('input[type="radio"][name="category"]:checked')?.value;
}

/**
 * Get Featured Flag Filter Value
 */
const getFilterFeatured = function () {
    return document.querySelector('input[type="radio"][name="featured"]:checked')?.value;
}

/**
 * Filter Category Change
 *
 * Update controls and message when selecting a media category filter option.
 */
const filterCategoryChange = function() {
    // Respond whether viewing a defined category (ID) or "all"
    if (!isNaN(getFlterCategoryId()) && getFilterFeatured() === "all") {
        draggableMessage.style.display = "inline";
        document.querySelectorAll(`[data-media-card="true"]`)?.forEach(media => {
            media.setAttribute("draggable", true);
            media.style.cursor = "move";
        });
    } else {
        draggableMessage.style.display = "none";
        document.querySelectorAll(`[data-media-card="true"]`)?.forEach(media => {
            media.setAttribute("draggable", false);
            media.style.cursor = "default";
        });
    }
}

// Watch for changes to DOM when media filters are applied, to update draggable state
const observer = new MutationObserver(filterCategoryChange);
observer.observe(document.querySelector(`[data-query="content"]`), {childList: true});

/**
 * Save Media
 * @param {Event} event
 */
const saveMedia = function(event) {
    if (event.target.dataset.formButton !== "save") return;
    const form = event.target.closest("form");

    postXHRPromise(pitonConfig.routes.adminMediaSave, new FormData(form))
        .then(() => {
            // Show save complete by disabling save and discard buttons again
            form.querySelectorAll(`[data-form-button="save"], [data-form-button="cancel"]`)?.forEach(control => {
                control.disabled = true;
            });
        })
        .then(() => {
            disableSpinner();
        })
        .catch((error) => {
            disableSpinner();
            alertInlineMessage('danger', 'Failed to Save Media', error);
        });
}

/**
 * Delete Media Asynchronously
 * @param {Event} event
 */
const deleteMedia = function(event) {
    if (!event.target.dataset.deleteMediaPrompt) return;
    if (!confirm(event.target.dataset.deleteMediaPrompt)) return;

    let mediaCard = event.target.closest('[data-media-card="true"]');
    let mediaId = event.target.dataset.deleteMediaId;

    enableSpinner();
    postXHRPromise(pitonConfig.routes.adminMediaDelete, {"media_id": mediaId})
        .then(() => {
            mediaCard.remove();
        })
        .then(() => {
            disableSpinner();
        })
        .catch((error) => {
            disableSpinner();
            alertInlineMessage('danger', 'Failed to Delete Media', error);
        });
}

/**
 * Copy Media Path on Click
 *
 * Copies relative path to media file
 * @param {Event} event
 */
const copyMediaPath = function(event) {
    if (!event.target.dataset.mediaClickCopy) return;

    try {
        // Stop if the current browser does not support navigator clipboard
        if (!navigator.clipboard) {
            throw "Your browser does not support click to copy.";
        }

        let dataPath = event.target.dataset.mediaClickCopy;
        navigator.clipboard.writeText(dataPath);
    } catch (error) {
        alert("Error in click to copy: " + error);
    }
}

/**
 * OVERRIDE
 * Drag End Handler
 *
 * Cleans up end of drag events, and also saves new order of images
 * @param {Event} event
 */
const dragEndHandler = function(event) {
    // Cleanup drop zones
    document.querySelectorAll(".drag-drop").forEach(zone => {
        zone.remove();
    });

    if (!isNaN(getFlterCategoryId())) {
        enableSpinner();

        // Get all media elements listed on page
        let mediaElements = document.querySelectorAll(`[data-draggable="children"] > [draggable="true"]`);

        // Assign media ID to array
        let mediaArray = [];
        mediaElements.forEach(media => {
            mediaArray.push(media.dataset.mediaId);
        });

        let data = {
            "categoryId": getFlterCategoryId(),
            "mediaIds": mediaArray
        }

        postXHRPromise(pitonConfig.routes.adminMediaCategorySaveOrder, data)
            .then(() => {
                disableSpinner();
            })
            .catch((error) => {
                disableSpinner();
                alertInlineMessage('danger', 'Failed to Save Media Order', error);
            });
    }

    // Dispatch input event to finish
    dispatchInputEventOnMovedElement();
}

// Disable default enter key submit on media card edit forms. Save should be an explicit button click to save
document.addEventListener("keypress", event => {
    if (event.target.closest("form") && event.key === "Enter") {
        event.preventDefault();
    }
}, false);

// Draggable media elements
document.querySelectorAll(`[data-draggable="children"]`).forEach(zone => {
    zone.addEventListener("dragstart", dragStartHandler, false);
    zone.addEventListener("dragenter", dragEnterHandler, false);
    zone.addEventListener("dragover", dragOverHandler, false);
    zone.addEventListener("dragleave", dragLeaveHandler, false);
    zone.addEventListener("drop", dragDropHandler, false);
    zone.addEventListener("dragend", dragEndHandler, false);
});

// Bind events
document.addEventListener("click", saveMedia, false);
document.addEventListener("click", deleteMedia, false);
document.addEventListener("click", copyMediaPath, false);

/**
 * PitonCMS (https://github.com/PitonCMS)
 *
 * @link      https://github.com/PitonCMS/Piton
 * @copyright Copyright 2018 Wolfgang Moritz
 * @license   https://github.com/PitonCMS/Piton/blob/master/LICENSE (MIT License)
 */

/**
 * Page Edit JS
 */

import './modules/main.js';
import { pitonConfig } from './modules/config.js';
import { mediaCKEditorSelectModal, mediaCKEditorSelectedListener } from './modules/mediaModal.js';
import { enableSpinner, disableSpinner } from './modules/spinner.js';
import { getXHRPromise, postXHRPromise } from './modules/xhrPromise.js';
import { setCleanSlug, unlockSlug } from './modules/url.js';
import { dragStartHandler, dragEnterHandler, dragOverHandler, dragLeaveHandler, dragDropHandler, dragEndHandler } from './modules/drag.js';
import { alertInlineMessage } from './modules/alert.js';

/**
 * Set Element Title
 */
const setElementTitleText = function(event) {
    if (!event.target.matches(`input[name*="element_title"]`)) return;

    let title = event.target.value;
    let elementTitle = event.target.closest(`[data-element="parent"]`).querySelector(".secondary-title");
    elementTitle.innerHTML = title;
}

/**
 * CKEditor Media Select Open Modal
 *
 * Passes in mediaCKEditorSelectModal to PitonSelectMedia plugin at runtime to open media modal
 * @param {Editor} editor
 */
const mediaCKEditorSelectModalCallback = function(editor) {
    editor.plugins.get("PitonSelectMedia").setOpenMediaModal(mediaCKEditorSelectModal);
}

/**
 * CKEditor Media Select and Set Media in Editor
 *
 * Passes in mediaCKEditorSelectedListener to PitonSelectMedia plugin at runtime to select and close media modal
 * @param {Editor} editor
 */
const mediaCKEditorSelectedListenerCallback = function(editor) {
    editor.plugins.get("PitonSelectMedia").setMediaSelectListener(mediaCKEditorSelectedListener(editor));
}

/**
 * Text CK Editor Initalize
 * @param {object} textElement
 */
const initEditor = function(textElement) {
        // The toolbar configuration is set in a custom build of CKEditor, in the PitonCMS/PitonCKEditor project fork.
        // To  update the editor layout or configuration, modify in /packages/piton-build bundle
        ClassicEditor.create(textElement, {
                extraPlugins: [mediaCKEditorSelectModalCallback, mediaCKEditorSelectedListenerCallback]
            })
            .then(editor => {
                editor.model.document.on('change:data', (e) => {
                    textElement.dispatchEvent(new Event("input", {"bubbles": true}));
                });

                // Uncomment to display toolbar and plugin options
                // console.log(Array.from(editor.ui.componentFactory.names()));
                // console.log(ClassicEditor.builtinPlugins.map(plugin => plugin.pluginName));
            })
            .catch(error => {
                console.error(error);
            });
}

/**
 * Enable Draggable
 * Use with mouseup event to re-enable draggable=true on parent element
 * @param {Event} event
 */
const enableDraggable = function(event) {
    if (event.target.closest(`[data-drag-handle="true"]`)) return;
    event.target.closest(`[data-element="parent"]`).setAttribute("draggable", true);
}

/**
 * Disable Draggable
 * Use with mousedown event to disable draggable=true on parent element
 * @param {Event} event
 */
const disableDraggable = function(event) {
    if (event.target.closest(`[data-drag-handle="true"]`)) return;
    event.target.closest(`[data-element="parent"]`).setAttribute("draggable", false);
}

// Add new event.target event
document.querySelectorAll(`[data-element-select-block]`).forEach(block => {
    // Track element count and limit to enable or disable new elements
    let blockKey = block.dataset.elementSelectBlock;
    let blockElementCount = parseInt(block.dataset.elementCount ?? 0);
    let blockElementCountLimit = parseInt(block.dataset.elementCountLimit);
    let newElementDropdown = block.querySelector(`[data-collapse-toggle*="newElementButton"]`).parentElement;

    const addElementToggleState = function (increment) {
        blockElementCount = blockElementCount + increment;

        if (blockElementCount >= blockElementCountLimit) {
            // Disable
            newElementDropdown.classList.add("dropdown-disabled");
        } else {
            // Enable
            newElementDropdown.classList.remove("dropdown-disabled");
        }
    }

    // New element
    block.querySelectorAll(`a[data-element="add"]`).forEach(addEl => {
        addEl.addEventListener("click", (e) => {
            e.preventDefault();

            // Check element limit
            if (blockElementCount >= blockElementCountLimit) {
                return;
            }

            // Get new element
            enableSpinner();

            // Get query string and XHR Promise
            let query = {
                "template": addEl.dataset.elementTemplate,
                "blockKey": blockKey
            }

            getXHRPromise(pitonConfig.routes.adminPageElementGet, query)
                .then(response => {
                    let container = document.createElement("div");
                    let targetBlock = document.getElementById("block-" + blockKey);
                    container.innerHTML = response;

                    // Update element count
                    addElementToggleState(1);

                    // If Block container is collapsed, uncollapse
                    if (targetBlock.parentElement.classList.contains("collapsed")) {
                        targetBlock.parentElement.classList.remove("collapsed")
                    }

                    // Add new-element class and insert element
                    container.querySelector(`[data-element="parent"]`).classList.add("new-element");
                    targetBlock.insertAdjacentHTML('beforeend', container.innerHTML);

                    // Set focus with page scroll to newly inserted element
                    const elementList = targetBlock.querySelectorAll(`input[name*="element_title"]`);
                    elementList[elementList.length - 1].focus();

                    // Trigger form control state change with Input event
                    targetBlock.dispatchEvent(new Event("input", {"bubbles": true}));

                    // Unable to initalize SimpleMDE on the unattached HTML fragment until we insert it
                    let newEditor = targetBlock.lastElementChild.querySelector(`textarea[data-cke="true"]`);
                    initEditor(newEditor);
                })
                .then(() => {
                    disableSpinner();
                }).catch((error) => {
                    disableSpinner();
                    alertInlineMessage("danger", "Failed to Add Element", error);
                });
        }, false);
    });

    // Delete element
    block.addEventListener("click", (event) => {
        if (!event.target.dataset.deleteElementPrompt) return;
        // Confirm delete
        if (!confirm(event.target.dataset.deleteElementPrompt)) return;

        // Get element ID and element
        let elementId = parseInt(event.target.dataset.elementId);
        let element = event.target.closest(`[data-element="parent"]`);

        if (isNaN(elementId)) {
            // Element has not been saved to DB, just remove from DOM
            element.remove();
        } else {
            // Element has been saved, do a hard delete
            enableSpinner();
            postXHRPromise(pitonConfig.routes.adminPageElementDelete, {"elementId": elementId})
                .then(() => {
                    element.remove();
                })
                .then(() => {
                    disableSpinner();
                })
                .catch((error) => {
                    disableSpinner();
                    alertInlineMessage("danger", "Failed to Delete Element", error);
                });
        }

        addElementToggleState(-1);

    }, false);
});

// Bind CK Editor to selected textareas on page load
document.querySelectorAll(`textarea[data-cke="true"]`).forEach(editor => {
    initEditor(editor);
});

// Bind set page slug from page title
document.querySelector(`[data-url-slug="source"]`).addEventListener("input", (e) => {
    setCleanSlug(e.target.value);
}, false);

// Bind warning on unlocking page slug
document.querySelector(`[data-url-slug-lock="1"]`).addEventListener("click", (e) => {
    unlockSlug(e);
}, false);

// Bind page edit listeners for events that bubble
document.addEventListener("change", setElementTitleText, false);

// Draggable page elements
document.querySelectorAll(`[data-draggable="children"]`).forEach(zone => {
    zone.addEventListener("mousedown", disableDraggable, false);
    zone.addEventListener("mouseup", enableDraggable, false);
    zone.addEventListener("dragstart", dragStartHandler, false);
    zone.addEventListener("dragenter", dragEnterHandler, false);
    zone.addEventListener("dragover", dragOverHandler, false);
    zone.addEventListener("dragleave", dragLeaveHandler, false);
    zone.addEventListener("drop", dragDropHandler, false);
    zone.addEventListener("dragend", dragEndHandler, false);
});

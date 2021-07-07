/**
 * PitonCMS (https://github.com/PitonCMS)
 *
 * @link      https://github.com/PitonCMS/Piton
 * @copyright Copyright 2018 Wolfgang Moritz
 * @license   https://github.com/PitonCMS/Piton/blob/master/LICENSE (MIT License)
 */

/**
 * Drag and Drop elements module
 *
 * HTML
 * Set parent container with data-draggable="children"
 * Set draggable children with draggable="true"
 */

// Hoist reference to element to be moved
let movedElement;

// Event to dispatch pseudo "input" event
const inputEvent = new Event("input", {"bubbles": true});

// Empty drop zone divs to inject in DOM around draggable elements
const dropZone = document.createElement("div");
dropZone.classList.add("drag-drop");

/**
 * Return Moved Element
 */
const getMovedElement = function() {
    return movedElement;
}

/**
 * Drag Start Handler
 * @param {Event} event
 */
const dragStartHandler = function(event) {
    // Save reference to the element being moved
    movedElement = event.target;

    event.dataTransfer.setData("text/plain", null);
    event.dataTransfer.dropEffect = "move";

    // Insert drop zone divs around each draggable element
    // setTimeout() hack: https://stackoverflow.com/a/34698388/452133
    // To allow DOM manipulation in dragstart
    setTimeout(() => {
        document.querySelectorAll(`[draggable="true"]`).forEach(element => {
            // Insert drop zone before all draggable elements
            // Except around and inside the movedElement
            if (element !== movedElement && element !== movedElement.nextElementSibling && !movedElement.contains(element)) {
                element.parentElement.insertBefore(dropZone.cloneNode(), element);
            }

            // Insert drop zone after last child
            if (element === element.parentElement.lastElementChild && element !== movedElement) {
                element.parentElement.appendChild(dropZone.cloneNode());
            }
        });

        // Optional "other" drop targets
        document.querySelectorAll(`[data-drop-zone="1"]`).forEach(element => {
            // Do not allow movedElement to become child of itself
            if (!movedElement.contains(element)) {
                element.parentElement.insertBefore(dropZone.cloneNode(), element);
            }
        });
    }, 0);
}

/**
 * Drag Enter Handler
 * @param {Event} event
 */
const dragEnterHandler = function(event) {
    event.preventDefault();
    event.stopPropagation();
    event.dataTransfer.dropEffect = "move";

    if (event.target.matches(".drag-drop")) {
        event.target.classList.add("drag-hover");
    }
}

/**
 * Drag Over Handler
 * @param {Event} event
 */
const dragOverHandler = function(event) {
    event.preventDefault();
    event.stopPropagation();
    event.dataTransfer.dropEffect = "move";
}

/**
 * Drag Leave Handler
 * @param {Event} event
 */
const dragLeaveHandler = function(event) {
    event.preventDefault();
    event.stopPropagation();
    event.dataTransfer.dropEffect = "move";

    if (event.target.matches(".drag-drop")) {
        event.target.classList.remove("drag-hover");
    }
}

/**
 * Drag Drop Handler
 * @param {Event} event
 */
const dragDropHandler = function(event) {
    event.preventDefault();
    event.stopPropagation();

    if (movedElement !== event.target && event.target.matches(".drag-drop")) {
        event.target.parentElement.insertBefore(movedElement, event.target.nextSibling)
    }
}

/**
 * Drag End Handler
 * @param {Event} event
 */
const dragEndHandler = function(event) {
    // Cleanup drop zones
    document.querySelectorAll(".drag-drop").forEach(zone => {
        zone.remove();
    });

    // Dispatch input event
    movedElement.dispatchEvent(inputEvent);
}

/**
 * Dispatch Input Event on Moved Element
 * @param void
 */
const dispatchInputEventOnMovedElement = function() {
    movedElement.dispatchEvent(inputEvent);
}

export { dragStartHandler, dragEnterHandler, dragOverHandler, dragLeaveHandler, dragDropHandler, dragEndHandler, getMovedElement, dispatchInputEventOnMovedElement };
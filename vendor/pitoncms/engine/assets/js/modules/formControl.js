/**
 * PitonCMS (https://github.com/PitonCMS)
 *
 * @link      https://github.com/PitonCMS/Piton
 * @copyright Copyright 2018 Wolfgang Moritz
 * @license   https://github.com/PitonCMS/Piton/blob/master/LICENSE (MIT License)
 */

/**
 * Form Controls
 *
 * Listens for input events to:
 * - Enable form controls (save, cancel) on Input events
 * - Delete confirm prompts
 * - Reset confirm prompts
 *
 * HTML
 * Assumes all control buttons are set to disabled on page load, except delete which should always be enabled
 * Add data-form-button="save" to Save buttons
 * Add data-form-button="cancel" to Cancel / Reset buttons
 * - Optionally add data-form-reset-href="{{ <url> }}" to load a page instead of resetting the form
 * Add data-delete-prompt="Custom message" to any delete button
 *
 * JS
 * import './formControl.js';
 */

 /**
  * Enable Form Controls on Input Event
  * @param {Event} event
  */
 const enableFormControlsOnInput = function (event) {
    if (!event.target.closest("form")) return;

    // Get reference to buttons and form
    let form = event.target.closest("form");
    let controls = form.querySelectorAll('[data-form-button="save"], [data-form-button="cancel"]');

    // Enable buttons
    if (controls) {
        controls.forEach(control => {
            control.disabled = false;
        });
    }
 }

/**
 * Confirm Form Reset Cancel Prompt
 * @param {Event} event
 */
 const confirmResetCancelPrompt = function (event) {
    if (event.target.dataset.formButton !== "cancel") return;
    event.stopPropagation();

    // Stop if cancel is selected on prompt
    if (!confirm("Click Ok to discard your changes, or Cancel continue editing.")) {
        event.preventDefault();
        return;
    }

    // Disable all form controls once again
    let form = event.target.closest("form");
    let controls = form.querySelectorAll('[data-form-button="save"], [data-form-button="cancel"]');

    // Disable buttons
    if (controls) {
        if (event.target.dataset.formResetHref) {
            // Reload page if a url was provided as a data value
            event.preventDefault();
            window.location = event.target.dataset.formResetHref;
        } else {
            // Otherwise let type="reset" reset form as default event and disable buttons again
            // The type="reset" native reset was not working as this JS was disabling that button before the form could be reset
            // Wrapping this in setTimeout() seems to fix this. Weird.
            setTimeout(() => {
                controls.forEach(control => {
                    control.disabled = true;
                });
            }, 0);
        }
    }
 }

/**
 * Confirm Delete Prompt
 * @param {Event} event
 */
 const confirmDeletePrompt = function (event) {
    if (!event.target.dataset.deletePrompt) return;
    if (!confirm(event.target.dataset.deletePrompt)) event.preventDefault();
 }

document.addEventListener("input", enableFormControlsOnInput, false);
document.addEventListener("click", confirmDeletePrompt, false);
document.addEventListener("click", confirmResetCancelPrompt, false);

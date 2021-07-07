/**
 * PitonCMS (https://github.com/PitonCMS)
 *
 * @link      https://github.com/PitonCMS/Piton
 * @copyright Copyright 2018 Wolfgang Moritz
 * @license   https://github.com/PitonCMS/Piton/blob/master/LICENSE (MIT License)
 */

/**
 * Toggle Collapse of Elements Module
 *
 * When the element with data-collapse-toggle="<key>" is clicked the element with data-collapse-target="<key>"
 * has the "collapsed" class toggled to animate a slide up or down.
 *
 * Add data-collapse-auto="<key>" to any target element to listen for a click event and apply the collapsed class
 *
 * HTML
 * Add data-collapse-toggle="<key>" (with a unique key value) on the element to click and trigger a toggle collapse.
 * Add data-collapse-target="<key>" (with the same key value) on the element to be collapsed.
 * Optionally add data-collapse-auto="<key>" (with the same key value) to any other element that can click collapse the target.
 * To load the page to a collapsed state, add the class "collapsed" on the target.
 *
 * JS
 * Import this file.
 */

// Class name to toggle to show/hide elements
const collapseClass = "collapsed";

/**
 * Collapse Toggle
 * @param {Event} event
 */
const collapseToggle = function (event) {
    if (!event.target.closest(`[data-collapse-toggle]`)) return;

    // Find the matching collapse target by key and toggle class
    let toggleKey = event.target.closest(`[data-collapse-toggle]`).dataset.collapseToggle;
    let collapseTarget = document.querySelector(`[data-collapse-target="${toggleKey}"]`);
    collapseTarget.classList.toggle(collapseClass);
}

/**
 * Auto Collapse
 * @param {Event} event
 */
const autoCollapse = function (event) {
    if (!event.target.closest(`[data-collapse-auto]`)) return;

    // Find the matching collapse target by key and apply toggle class (not toggle)
    let toggleKey = event.target.closest(`[data-collapse-auto]`).dataset.collapseAuto;
    let collapseTarget = document.querySelector(`[data-collapse-target="${toggleKey}"]`);
    collapseTarget.classList.add(collapseClass);
}

document.addEventListener("click", collapseToggle, false);
document.addEventListener("click", autoCollapse, false);

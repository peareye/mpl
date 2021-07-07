/**
 * PitonCMS (https://github.com/PitonCMS)
 *
 * @link      https://github.com/PitonCMS/Piton
 * @copyright Copyright 2018 Wolfgang Moritz
 * @license   https://github.com/PitonCMS/Piton/blob/master/LICENSE (MIT License)
 */

/**
 * Manage Navigation JS
 */

import './modules/main.js';
import { pitonConfig } from './modules/config.js';
import { enableSpinner, disableSpinner } from './modules/spinner.js';
import { dragStartHandler, dragEnterHandler, dragOverHandler, dragLeaveHandler, dragEndHandler, getMovedElement } from './modules/drag.js';
import { postXHRPromise } from './modules/xhrPromise.js';
import { alertInlineMessage } from './modules/alert.js';

const navItems = [];
const navPages = document.querySelectorAll(`[data-add-nav="page"] input`);
const navCollections = document.querySelectorAll(`[data-add-nav="collection"] input`);
const navPlaceholder = document.querySelectorAll(`[data-add-nav="placeholder"] input`);
const navElement = document.querySelector(`[data-navigation="spare"] > div`);
const navContainer = document.querySelector(`[data-navigation-container="1"]`);
let elementKey = 0;

/**
 * Append Navigation Elements
 */
const appendNavElements = function() {
    navItems.forEach(nav => {
        // Clone spare navigation element, and set unique name array key so POST array keeps inputs together
        let newNav = navElement.cloneNode(true);
        let arrayKey = (elementKey++) + "n";
        newNav.querySelectorAll(`input[name^=nav]`).forEach(input => {
            input.name = input.name.replace(/(.+?\[)(\].+)/, "$1" + arrayKey + "$2");
        });
        newNav.dataset.navId = arrayKey;
        newNav.querySelector(`[data-collapse-toggle]`).dataset.collapseToggle = arrayKey;
        newNav.querySelector(`[data-collapse-target]`).dataset.collapseTarget = arrayKey;

        // Set data
        if (nav.pageId) {
            newNav.querySelector(`input[name$="\[pageId\]"]`).value = nav.pageId;
            newNav.querySelector(`[data-nav="title"]`).innerHTML = nav.pageTitle;
            newNav.querySelector(`[data-nav="type"]`).innerHTML = "page";
            newNav.querySelector(`[data-nav="pageTitle"]`).innerHTML = nav.pageTitle;
            newNav.querySelector(`[data-nav="pageTitle"]`).parentElement.classList.remove("d-none");

        } else if (nav.navTitle) {
            newNav.querySelector(`[data-nav="title"]`).innerHTML = nav.navTitle;
            newNav.querySelector(`[data-nav="type"]`).innerHTML = "placeholder";
            newNav.querySelector(`input[name$="\[navTitle\]"]`).value = nav.navTitle;
            newNav.querySelector(`input[name$="\[url\]"]`).value = nav.url;
            newNav.querySelector(`input[name$="\[url\]"]`).parentElement.classList.remove("d-none");

        } else if (nav.collectionId) {
            newNav.querySelector(`input[name$="\[collectionId\]"]`).value = nav.collectionId;
            newNav.querySelector(`[data-nav="title"]`).innerHTML = nav.collectionTitle;
            newNav.querySelector(`[data-nav="type"]`).innerHTML = "collection";
            newNav.querySelector(`[data-nav="collectionTitle"]`).innerHTML = nav.collectionTitle;
            newNav.querySelector(`[data-nav="collectionTitle"]`).parentElement.classList.remove("d-none");

        }

        navContainer.appendChild(newNav);
        newNav.dispatchEvent(new Event("input", {"bubbles": true}));
    });

    // Reset
    navItems.length = 0;
}

/**
 * Add Page Navigation
 */
const addPageNav = function() {
    navPages.forEach(element => {
        if (element.checked) {
            let navItem = {
                "pageId": element.dataset.pageId,
                "pageTitle": element.dataset.pageTitle
            }

            element.checked = false;
            navItems.push(navItem);
        }

    });

    appendNavElements();
}

/**
 * Add Collection Navigation
 */
const addCollectionNav = function() {
    navCollections.forEach(element => {
        if (element.checked) {
            let navItem = {
                "collectionId": element.dataset.collectionId,
                "collectionTitle": element.dataset.collectionTitle
            }

            element.checked = false;
            navItems.push(navItem);
        }

    });

    appendNavElements();
}

/**
 * Add Placeholder Navigation
 */
const addPlaceholderNav = function() {
    if (navPlaceholder[0].value) {
        let navItem = {
            "navTitle": navPlaceholder[0].value,
            "url": navPlaceholder[1].value
        }

        navPlaceholder[0].value = "";
        navPlaceholder[1].value = "";
        navItems.push(navItem);
    }

    appendNavElements();
}

/**
 * Insert Child Drop Zone
 */

/**
 * OVERRIDE Drag Drop Handler
 * Overrides drag.js to support child navigation drops
 * @param {Event} event
 */
const dragDropHandler = function(event) {
    event.preventDefault();
    event.stopPropagation();

    let movedElement = getMovedElement();

    // Nothing to do if dropping on self
    if (movedElement !== event.target && event.target.matches(".drag-drop")) {
        let movedElementParentId = movedElement.querySelector(`input[name$="\[parentId\]"]`).value;
        let newParent = event.target.parentElement.closest(`[data-navigation="parent"]`);

        // If the element parentId matches the current parent ID (element sorted within current level), just drop in new order
        if (movedElementParentId === newParent.dataset.navId) {
            event.target.parentElement.insertBefore(movedElement, event.target.nextSibling);
        }

        // If the element parentId and does not match the new  parent ID, move to last child of new parent
        if (movedElementParentId !== newParent.dataset.navId) {
            movedElement.querySelector(`input[name$="\[parentId\]"]`).value = newParent.dataset.navId;

            // Add / remove class
            if (newParent.dataset.navId === "") {
                movedElement.classList.remove("sub-toggle-block");
            } else {
                movedElement.classList.add("sub-toggle-block");
            }

            event.target.parentElement.insertBefore(movedElement, event.target.nextSibling);
        }
    }
}

const deleteNavItem = function(event) {
    if (!event.target.dataset.deleteNavigationPrompt) return;
    if (!confirm(event.target.dataset.deleteNavigationPrompt)) return;

     // Get nav element and create array of IDs to delete
     let navIds = [];
     let navElement = event.target.closest(`[data-navigation="parent"]`);

     // Get this ID and all currently assigned child navigation ID's. Some may have been previously saved before being added to this nav parent
     navElement.querySelectorAll(`input[name$="\[navId\]"]`).forEach((i) => {
        let id = parseInt(i.value);
        if (!isNaN(id)) {
            navIds.push(id);
        }
     });

     if (navIds.length > 0) {
        // Delete any navs that had been saved
        enableSpinner();
        let data = JSON.stringify(navIds);

        postXHRPromise(pitonConfig.routes.adminNavigationDelete, {"navIds": data})
            .then(() => {
                // This removes the current nav item along with any children
                navElement.remove();
            })
            .then(() => {
                disableSpinner();
            })
            .catch((error) => {
                disableSpinner();
                alertInlineMessage("danger", "Failed to delete navigation", error);
            });

        // Reset
        navIds.length = 0;
     } else {
        // This removes the current nav item along with any children
        navElement.remove();
     }

}

// Bind events
document.querySelector(`[data-add-nav="pageButton"]`).addEventListener("click", addPageNav, false);
document.querySelector(`[data-add-nav="collectionButton"]`).addEventListener("click", addCollectionNav, false);
document.querySelector(`[data-add-nav="placeholderButton"]`).addEventListener("click", addPlaceholderNav, false);
document.addEventListener("click", deleteNavItem, false);

// Draggable navigation elements
document.querySelectorAll(`[data-draggable="children"]`).forEach(zone => {
    zone.addEventListener("dragstart", dragStartHandler, false);
    zone.addEventListener("dragenter", dragEnterHandler, false);
    zone.addEventListener("dragover", dragOverHandler, false);
    zone.addEventListener("dragleave", dragLeaveHandler, false);
    zone.addEventListener("drop", dragDropHandler, false);
    zone.addEventListener("dragend", dragEndHandler, false);
});

// Prevent nav source from triggering form control enable
document.querySelectorAll(`[data-add-nav="page"], [data-add-nav="collection"], [data-add-nav="placeholder"]`).forEach(element => {
    element.addEventListener("input", (event) => {
        event.stopPropagation();
    });
});
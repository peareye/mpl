/**
 * PitonCMS (https://github.com/PitonCMS)
 *
 * @link      https://github.com/PitonCMS/Piton
 * @copyright Copyright 2018 Wolfgang Moritz
 * @license   https://github.com/PitonCMS/Piton/blob/master/LICENSE (MIT License)
 */

/**
 * Manage Messages JS
 */

import "./modules/main.js";
import { pitonConfig } from './modules/config.js';
import { setQueryRequestPath, applyFilters } from "./modules/filter.js";
import { postXHRPromise, getXHRPromise } from "./modules/xhrPromise.js";
import { disableSpinner, enableSpinner } from "./modules/spinner.js";
import { alertInlineMessage } from "./modules/alert.js";

setQueryRequestPath(pitonConfig.routes.adminMessageGet);
const unreadMessageCountBadge = document.querySelector(`[data-message="count"]`);

/**
 * Update Unread Message Count in Sidebar
 *
 * @param {void}
 */
const updateUnreadMessageCount = function() {
    getXHRPromise(pitonConfig.routes.adminMessageCountGet)
        .then(data => {
            unreadMessageCountBadge.innerHTML = data;
        })
        .catch(error => {
            alertInlineMessage('danger', 'Unable to Update Inbox Message Count', error);
        });
}

/**
 * Update Message
 *
 * For Read, Archive status toggle, and Delete
 * @param {Event} event
 */
const updateMessage = function (event) {
    if (!event.target.dataset.messageControl) return;

    let messageParent = event.target.closest(`[data-message="parent"]`);
    let data = {"messageId": messageParent.dataset.messageId};

    // Process control request
    if (event.target.dataset.messageControl === 'delete') {
        // Message delete
        if (!confirm(event.target.dataset.messageDeletePrompt)) return;
        data["control"] = "delete";
    } else if (event.target.dataset.messageControl === 'archive') {
        // Toggle archive
        data["control"] = "archive";
    } else if (event.target.dataset.messageControl === 'read') {
        // Toggle read
        data["control"] = "read";
    }

    enableSpinner();
    postXHRPromise(pitonConfig.routes.adminMessageSave, data)
        .then(() => {
            updateUnreadMessageCount();
        })
        .then(() => {
            applyFilters();
        })
        .then(() => {
            disableSpinner();
        })
        .catch(error => {
            disableSpinner();
            alertInlineMessage('danger', 'Failed to Update Message', error);
        });
}

// Bind event handlers to page
document.addEventListener("click", updateMessage, false);

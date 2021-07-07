/**
 * PitonCMS (https://github.com/PitonCMS)
 *
 * @link      https://github.com/PitonCMS/Piton
 * @copyright Copyright 2018 Wolfgang Moritz
 * @license   https://github.com/PitonCMS/Piton/blob/master/LICENSE (MIT License)
 */

/**
 * XHR query request refresh for filters, search, and pagination module
 */

 import { enableSpinner, disableSpinner } from './spinner.js';
 import { getXHRPromise } from './xhrPromise.js';
 import { alertInlineMessage } from './alert.js';

// Hoist request route path variable
let requestPath;

/**
 * Get Query Results Display Element
 *
 * @param {string} element default `[data-query="content"]`
 */
const getQueryResultsElement = function (element = `[data-query="content"]`) {
    return document.querySelector(element);
}

/**
 * Set Query Request Path
 *
 * @param {string} route
 */
const setQueryRequestPath = function(route) {
    requestPath = route;
}

/**
 * Remove Result Set Rows
 *
 * Clears result set from element getQueryResultsElement()
 * @param void
 */
const removeRows = function() {
    if (getQueryResultsElement()) {
        while (getQueryResultsElement().firstChild) {
            getQueryResultsElement().removeChild(getQueryResultsElement().lastChild);
        }
    }
};

/**
 * Get Query XHR Promise
 *
 * @param {object} options
 */
const getQueryXHRPromise = function(options) {
    if (!requestPath) {
        console.error("Module xhrQuery requestPath is not set.");
    }

    enableSpinner();

    return getXHRPromise(requestPath, options)
        .then((data) => {
            removeRows();
            return data;
        })
        .then(data => {
            getQueryResultsElement().insertAdjacentHTML('afterbegin', data);
        })
        .then(() => {
            disableSpinner();
        })
        .catch((error) => {
            disableSpinner();
            alertInlineMessage('danger', 'Failed to Get Results', error);
        });
}

export { setQueryRequestPath, getQueryXHRPromise };
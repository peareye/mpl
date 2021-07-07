/**
 * PitonCMS (https://github.com/PitonCMS)
 *
 * @link      https://github.com/PitonCMS/Piton
 * @copyright Copyright 2018 Wolfgang Moritz
 * @license   https://github.com/PitonCMS/Piton/blob/master/LICENSE (MIT License)
 */

 /**
  * Enable XHR Pagination Requests Module
  */

 import { setQueryRequestPath, getQueryXHRPromise } from './xhrQuery.js';

/**
 * Pagination XHR Query
 *
 * Interrupts page link request to submit as XHR
 * @param {Event} event
 */
const paginationXHRQuery = function(event) {
    if (event.target.closest(".pagination > div")) {
        event.preventDefault();

        // Get query string parameters from pagination link and submit to XHRPromise as a URLSearchParams object
        let link = event.target.closest(".pagination > div").querySelector("a").href;
        let url = new URL(link);
        let searchParams = new URLSearchParams(url.search);

        return getQueryXHRPromise(searchParams);
    }
}

document.addEventListener("click", paginationXHRQuery, false);

export { setQueryRequestPath };
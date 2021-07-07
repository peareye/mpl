/**
 * PitonCMS (https://github.com/PitonCMS)
 *
 * @link      https://github.com/PitonCMS/Piton
 * @copyright Copyright 2018 Wolfgang Moritz
 * @license   https://github.com/PitonCMS/Piton/blob/master/LICENSE (MIT License)
 */

/**
 * Filter, Search, and Pagination XHR Controls for Results Sets Module
 *
 * HTML
 * Import /includes/_pitonMacros.html
 * Echo filterSearch() and filterOptions()
 *
 * Add data-query="content" on the parent elment containing the result set.
 * When the query is executed the direct children of data-query="content" are removed and replaced
 *
 * JS
 * Import
 *   import { setQueryRequestPath } from "./modules/filter.js";
 *
 * Define query endpoint in main script
 *   setQueryRequestPath("path/to/query/endpoint")
 *
 */
import { setQueryRequestPath, getQueryXHRPromise } from './xhrQuery.js';
import './pagination.js';

/**
 * Clear Filter Control
 *
 * Resets the current filter, but not other filters
 * @param {Event} event
 */
const clearFilterControl = function(event) {
    if (event.target.dataset.filterControl === "clear") {
        let filter = event.target.closest(`[data-filter="options"]`);
        filter.querySelectorAll("input").forEach(input => {
            input.checked = false;
        });
    }
}

/**
 * Clear All Filter Controls
 *
 * Use to reset all filters controls with other events such a search
 * @param void
 */
const clearAllFilterControls = function() {
    let filters = document.querySelectorAll(`[data-filter="options"] input`);

    // Clear filters
    filters.forEach((input) => {
        if (input.checked) {
            input.checked = false;
        }
    });
}

/**
 * Apply Filter Control
 *
 * @param {Event} event
 */
const ApplyFilterControl = function(event) {
    if (event.target.dataset.filterControl === "apply") {
        applyFilters();
    }
}

/**
 * Apply Filters
 *
 * Applies all filters on page as single XHR request
 * Call to refresh page after data updates
 * @param void
 */
const applyFilters = function() {
    let filters = document.querySelectorAll(`[data-filter="options"] input`);
    let selectedOptions = {};

    // Get filter options
    filters.forEach((input) => {
        if (input.checked) {
            // Check if this property has already been set, in which case concatenate value
            if (selectedOptions.hasOwnProperty(input.name)) {
                selectedOptions[input.name] += "," + input.value;
            } else {
                selectedOptions[input.name] = input.value;
            }
        }
    });

    return getQueryXHRPromise(selectedOptions);
}

/**
 * Text Search
 *
 * @param void
 */
const search = function() {
    let terms = document.querySelector(`[data-filter="search"] input`);
    let query = {"terms": terms.value};
    clearAllFilterControls();

    return getQueryXHRPromise(query);
}

// Bind events
// There may be more than one filter control on the page
document.addEventListener("click", ApplyFilterControl, false);
document.addEventListener("click", clearFilterControl, false);

// For the search box, listen to both the search icon click, and also the enter key submit
document.addEventListener("click", (event) => {
    if (!event.target.closest(`[data-filter-control="search"]`)) return;
    search();
}, false);
document.addEventListener("keypress", (event) => {
    if (!(event.target.closest(`[data-filter="search"]`) && event.key === 'Enter')) return;
    search();
}, false);

export { setQueryRequestPath, applyFilters };
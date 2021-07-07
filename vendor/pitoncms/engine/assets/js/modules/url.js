/**
 * PitonCMS (https://github.com/PitonCMS)
 *
 * @link      https://github.com/PitonCMS/Piton
 * @copyright Copyright 2018 Wolfgang Moritz
 * @license   https://github.com/PitonCMS/Piton/blob/master/LICENSE (MIT License)
 */

/**
 * URL Module
 */

const pageSlug = document.querySelector(`[data-url-slug="target"]`);

/**
 * Clean URL Slug
 *
 * Should match to Piton\Library\Utilities cleanUrl()
 * @param {string} value
 */
const setCleanSlug = function(value) {
    // Do not change the home page
    if (pageSlug.value === 'home') return;

    if (pageSlug.dataset.urlSlugStatus === 'unlock') {
        value = value.replace(/&/g, 'and');
        value = value.replace(`'`, '');
        value = value.replace(/[^a-z0-9]+/gi, '-');
        value = value.replace(/-+$/gi, '');
        value = value.toLowerCase();

        pageSlug.value = value;
    }
}

const unlockSlug = function(event) {
    // Do not change the home page
    if (pageSlug.value === 'home') {
        alert("You cannot change the home page slug.");
        return;
    };

    const message = 'Are you sure you want to change the URL Slug? This can impact links and search engine results.';

    if (event.target.classList && event.target.classList.contains("fa-lock")) {
        if (!confirm(message)) return;

        // Continue to unlock and enable input
        event.target.classList.replace("fa-lock", "fa-unlock");
        pageSlug.readOnly = false;
        pageSlug.dataset.urlSlugStatus = 'unlock';
    }
}

export { setCleanSlug, unlockSlug };
/**
 * PitonCMS (https://github.com/PitonCMS)
 *
 * @link      https://github.com/PitonCMS/Piton
 * @copyright Copyright 2018 Wolfgang Moritz
 * @license   https://github.com/PitonCMS/Piton/blob/master/LICENSE (MIT License)
 */

/**
 * XHR Promise Base Module
 *
 * Piton XHR request objects return a Promise.
 * For GET call getXHRPromise()
 * For POST call postXHRPromise()
 */

import { pitonConfig } from './config.js';

/**
 * XHR Request Promise Base
 *
 * @param {string} method "GET"|"POST"
 * @param {string} url    Resource request URL
 * @param {FormData} data   FormData payload to send
 */
const XHRPromise = function(method, url, data) {
    let xhr = new XMLHttpRequest();

    return new Promise((resolve, reject) => {
        let response;

        xhr.onreadystatechange = () => {
            if (xhr.readyState !== XMLHttpRequest.DONE) return;

            try {
                if (xhr.status === 200) {
                    // Successful server response, so parse payload to check return status
                    response = JSON.parse(xhr.responseText);

                    if (response.status === "success") {
                        // Response successful, resolve
                        return resolve(response.text);
                    }

                    throw new Error(`Application Error ${response.text}`);
                }

                throw new Error(`Server Error ${xhr.status} ${xhr.statusText}.`);
            } catch (error) {
                // JS Error thrown
                if (!(error instanceof Error)) {
                    let error = new Error(error);
                }

                return reject(error.message);
            }
        }

        // Setup request
        xhr.open(method, url, true);
        xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');

        // Add CSRF token from pitonConfig to header for any POST request
        if (method === "POST" && pitonConfig.csrfTokenValue) {
            xhr.setRequestHeader(pitonConfig.csrfTokenRequestHeader, pitonConfig.csrfTokenValue);
        }

        // And send request
        xhr.send(data);
    });
}

/**
 * GET XHR Promise Request
 *
 * @param {string} url  Resource URL
 * @param {object} data Object with query string parameters as key: values
 */
const getXHRPromise = function(url, data) {
    // Create query string if a data object was provided
    if (data) {
        let queryString;
        if (data instanceof URLSearchParams) {
            queryString = data;
        } else {
            queryString = new URLSearchParams();
            for (let [key, value] of Object.entries(data)) {
                queryString.append(key, value);
            }
        }

        url += "?" + queryString.toString();
    }

    return XHRPromise("GET", url);
}

/**
 * POST XHR Promise Request
 *
 * @param {string} url  Resource URL
 * @param {object} data Object with key: values, or FormData instance
 */
const postXHRPromise = function(url, data) {
    let formData;
    if (data instanceof FormData) {
        formData = data;
    } else {
        formData = new FormData();
        for (let [key, value] of Object.entries(data)) {
            formData.append(key, value);
        }
    }

    return XHRPromise("POST", url,  formData);
}

export { getXHRPromise, postXHRPromise };

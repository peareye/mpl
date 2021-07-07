/**
 * PitonCMS (https://github.com/PitonCMS)
 *
 * @link      https://github.com/PitonCMS/Piton
 * @copyright Copyright 2018 Wolfgang Moritz
 * @license   https://github.com/PitonCMS/Piton/blob/master/LICENSE (MIT License)
 */

/**
 * Upload Media Module
 */

import { pitonConfig } from './config.js';
import { loadModal, loadModalContent, removeModal } from './modal.js';
import { enableSpinner, disableSpinner } from './spinner.js';
import { getXHRPromise, postXHRPromise } from './xhrPromise.js';
import { alertInlineMessage } from './alert.js';

/**
 * Flag to reload page after upload, or asynchronously upload
 */
const refreshPageOnUpload = document.querySelector(`[data-media-refresh="true"]`) ? true : false;

/**
 * Format Bytes to Readable Size
 *
 * Takes an size of bytes (integer) and returns formatted string with size
 * @param {Int} bytes
 */
const formatBytes = function(bytes) {
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));

    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
}

/**
 * File Validation Error Messages
 */
const errorMessages = {
    fileTooLarge: `The selected file is too large. Max limit is ${formatBytes(pitonConfig.maxFileUploadSize)} bytes`,
    mimeTypeNotAllowed: `The file type is not supported for file uploads`,
};

/**
 * Validate Media File
 *
 * Validates selected file prior to upload
 * @param {Event} event
 */
const validateMediaFile = function(event) {
    if (!(event.target.type === "file" && event.target.closest(`[data-media-upload="form"]`))) return;
    let targetFile = event.target.files[0] ?? false;
    const displayMessage = document.querySelector(`[data-media-upload="message"]`);
    const uploadButton = document.querySelector(`[data-media-upload="button"]`);

    if (targetFile) {
        // Check if file size exceeds server upload limit
        if (targetFile.size > pitonConfig.maxFileUploadSize) {
            displayMessage.innerHTML = errorMessages.fileTooLarge;
            uploadButton.disabled = true;
        } else {
            displayMessage.innerHTML = "";
            uploadButton.disabled = false;
        }
    }
}

/**
 * Show Media Upload Form in Modal
 */
const showMediaUploadForm = function() {
    // Get file upload form with most current list of categories
    loadModal();
    getXHRPromise(pitonConfig.routes.adminMediaUploadFormGet)
        .then(data => {
            loadModalContent("Upload Media", data);
        })
        .catch((error) => {
            removeModal();
            alertInlineMessage("danger", "Failed To Open Media Upload Modal", error);
        });
}

/**
 * Media Upload Form Action
 *
 * @param {Event} event
 */
const mediaUploadAction = function(event) {
    if (event.target.dataset.mediaUpload !== "button") return;

    enableSpinner();
    const form = document.querySelector(`form[data-media-upload="form"]`);

    postXHRPromise(pitonConfig.routes.adminMediaUploadFile, new FormData(form))
        .then(() => {
            if (refreshPageOnUpload) {
                window.location.reload();
            }
        })
        .then(() => {
            removeModal();
        })
        .then(() => {
            disableSpinner();
        })
        .catch((error) => {
            removeModal();
            disableSpinner();
            alertInlineMessage('danger', 'Failed to Upload File', error);
        });
}

// Bind page events
document.addEventListener("click", mediaUploadAction, false);
document.querySelectorAll(`[data-media-upload="open"]`)?.forEach(upload => {
    upload.addEventListener("click", showMediaUploadForm, false);
});
document.addEventListener("change", validateMediaFile, false);

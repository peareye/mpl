/**
 * PitonCMS (https://github.com/PitonCMS)
 *
 * @link      https://github.com/PitonCMS/Piton
 * @copyright Copyright 2021 Wolfgang Moritz
 * @license   https://github.com/PitonCMS/Piton/blob/master/LICENSE (MIT License)
 */

/**
 * Piton Configuration
 */

let pitonConfig = {
  // When updating the CSRF Request Header name, also update app/Library/Handlers/CsrfGuard.php class property
  csrfTokenRequestHeader: "Piton-CSRF-Token",
  routes: {
    // Admin routes
    adminPageGet: "/admin/page/get",
    adminPageElementGet: "/admin/page/element/get",
    adminPageElementDelete: "/admin/page/element/delete",
    adminMessageSave: "/admin/message/save",
    adminMessageGet: "/admin/message/get",
    adminMessageCountGet: "/admin/message/getnewmessagecount",
    adminMedia: "/admin/media/",
    adminMediaSave: "/admin/media/save",
    adminMediaGet: "/admin/media/get/",
    adminMediaControlsGet: "/admin/media/getmediacontrols",
    adminMediaCategorySaveOrder: "/admin/media/category/saveorder",
    adminMediaDelete: "/admin/media/delete",
    adminMediaCategoryDelete: "/admin/media/category/delete",
    adminMediaUploadFormGet: "/admin/media/uploadform",
    adminMediaUploadFile: "/admin/media/upload",
    adminCollection: "/admin/collection/",
    adminNavigationDelete: "/admin/navigation/delete",

    // Front end routes
    submitMessage: "/submitmessage",
  },
};

// Merge admin config object into pitonConfig
if (typeof config === "object") {
  pitonConfig = {...pitonConfig, ... config};
}

export { pitonConfig }

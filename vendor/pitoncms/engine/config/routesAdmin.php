<?php

/**
 * PitonCMS (https://github.com/PitonCMS)
 *
 * @link      https://github.com/PitonCMS/Piton
 * @copyright Copyright 2018 Wolfgang Moritz
 * @license   https://github.com/PitonCMS/Piton/blob/master/LICENSE (MIT License)
 */

declare(strict_types=1);

use Psr\Http\Message\RequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Piton\Controllers\AdminController;
use Piton\Controllers\AdminUserController;
use Piton\Controllers\AdminPageController;
use Piton\Controllers\AdminNavigationController;
use Piton\Controllers\AdminSettingController;
use Piton\Controllers\AdminAccessController;
use Piton\Controllers\AdminMediaController;
use Piton\Controllers\AdminMessageController;

//
// Private secured routes
//
$app->group('/admin', function () {

    // Admin home
    $this->get('/home', function ($args) {
        return (new AdminController($this))->home();
    })->setName('adminHome');

    // Page route
    $this->group('/page', function () {
        // XHR: Get page list asynchronously
        $this->get('/get', function ($args) {
            return (new AdminPageController($this))->getPages();
        })->setName('adminPageGet');

        // Edit or add new page. Must provide ID or page layout argument
        $this->get('/edit[/[{id:[0-9]+}]]', function ($args) {
            $args['type'] = 'page';
            return (new AdminPageController($this))->editPage($args);
        })->setName('adminPageEdit');

        // Save Page for Update or Insert
        $this->post('/save', function ($args) {
            return (new AdminPageController($this))->savePage();
        })->add('csrfGuardHandler')->setName('adminPageSave');

        // Delete page
        $this->post('/delete', function ($args) {
            return (new AdminPageController($this))->deletePage($args);
        })->add('csrfGuardHandler')->setName('adminPageDelete');

        // Page elements
        $this->group('/element', function () {
            // XHR: Get element
            $this->get('/get', function ($args) {
                return (new AdminPageController($this))->getNewElement();
            })->setName('adminPageElementGet');

            // XHR: Delete ELement
            $this->post('/delete', function ($args) {
                return (new AdminPageController($this))->deleteElement();
            })->add('csrfGuardHandler')->setName('adminPageElementDelete');
        });

        // Show All Pages
        $this->get('[/]', function ($args) {
            return (new AdminPageController($this))->showPages();
        })->setName('adminPage');

        // End page elements
    });
    // End page routes

    // Collection group routes
    $this->group('/collection', function () {
        // Edit group collection
        $this->get('/edit[/[{id:[0-9]+}]]', function ($args) {
            return (new AdminPageController($this))->editCollection($args);
        })->setName('adminCollectionEdit');

        // Save collection group
        $this->post('/save', function ($args) {
            return (new AdminPageController($this))->saveCollection();
        })->add('csrfGuardHandler')->setName('adminCollectionSave');

        // Delete collection group
        $this->post('/delete', function ($args) {
            return (new AdminPageController($this))->deleteCollection();
        })->add('csrfGuardHandler')->setName('adminCollectionDelete');

        // Show all collection groups, filtered optionally by collection
        $this->get('[/]', function ($args) {
            return (new AdminPageController($this))->showCollectionGroups();
        })->setName('adminCollection');
    });
    // End collection

    // Navigation route
    $this->group('/navigation', function () {
        // Show Navigators
        $this->get('[/]', function ($args) {
            return (new AdminNavigationController($this))->showNavigators();
        })->setName('adminNavigation');

        // Save Navigation
        $this->post('/save', function ($args) {
            return (new AdminNavigationController($this))->saveNavigation();
        })->add('csrfGuardHandler')->setName('adminNavigationSave');

        // Edit Navigator
        $this->get('/edit/{navigator:[a-zA-Z0-9-]+}', function ($args) {
            return (new AdminNavigationController($this))->editNavigator($args);
        })->setName('adminNavigationEdit');

        // XHR: Delete navigation
        $this->post('/delete', function ($args) {
            return (new AdminNavigationController($this))->deleteNavigator();
        })->add('csrfGuardHandler')->setName('adminNavigationDelete');
    });
    // End Navigation

    // Media
    $this->group('/media', function () {
        // XHR: Get media asynchronously
        $this->get('/get/[{context:edit|static}]', function ($args) {
            return (new AdminMediaController($this))->getMedia($args);
        })->setName('adminMediaGet');

        // XHR: Get media controls asynchronously
        $this->get('/getmediacontrols', function ($args) {
            return (new AdminMediaController($this))->getMediaSearchControls();
        })->setName('adminMediaControlsGet');

        // XHR: Get media file upload form asynchronously
        $this->get('/uploadform', function ($args) {
            return (new AdminMediaController($this))->getMediaUploadForm();
        })->setName('adminMediaUploadFormGet');

        // XHR: File upload
        $this->post('/upload', function ($args) {
            return (new AdminMediaController($this))->uploadMedia();
        })->add('csrfGuardHandler')->setName('adminMediaUploadFile');

        // XHR: Media save
        $this->post('/save', function ($args) {
            return (new AdminMediaController($this))->saveMedia();
        })->add('csrfGuardHandler')->setName('adminMediaSave');

        // XHR: Media delete
        $this->post('/delete', function ($args) {
            return (new AdminMediaController($this))->deleteMedia();
        })->add('csrfGuardHandler')->setName('adminMediaDelete');

        // Media categories
        $this->group('/category', function () {
            $this->get('/edit', function ($args) {
                return (new AdminMediaController($this))->editMediaCategories();
            })->setName('adminMediaCategoryEdit');

            // Save media category
            $this->post('/save', function ($args) {
                return (new AdminMediaController($this))->saveMediaCategories();
            })->add('csrfGuardHandler')->setName('adminMediaCategorySave');

            // XHR Delete media category
            $this->post('/delete', function ($args) {
                return (new AdminMediaController($this))->deleteMediaCategory();
            })->add('csrfGuardHandler')->setName('adminMediaCategoryDelete');

            // XHR Save Category Sort Order
            $this->post('/saveorder', function ($args) {
                return (new AdminMediaController($this))->saveCategoryMediaOrder();
            })->add('csrfGuardHandler')->setName('adminMediaCategorySaveOrder');
        });

        // Show all media
        $this->get('[/]', function ($args) {
            return (new AdminMediaController($this))->showMedia();
        })->setName('adminMedia');
    });
    // End media

    // Messages
    $this->group('/message', function () {
        // Show message page
        $this->get('[/]', function ($args) {
            return (new AdminMessageController($this))->showMessages();
        })->setName('adminMessage');

        // XHR: Get filtered messages
        $this->get('/get', function ($args) {
            return (new AdminMessageController($this))->getMessages();
        })->setName('adminMessageGet');

        // XHR: Get new message count
        $this->get('/getnewmessagecount', function ($args) {
            return (new AdminMessageController($this))->getNewMessageCount();
        })->setName('adminMessageCountGet');

        // XHR: Save message status changes, Archvie, Read, and Delete
        $this->post('/save', function ($args) {
            return (new AdminMessageController($this))->updateStatus();
        })->add('csrfGuardHandler')->setName('adminMessageSave');
    });
    // End messages

    // Settings
    $this->group('/settings', function () {
        // Show settings landing page
        $this->get('[/]', function ($args) {
            return (new AdminSettingController($this))->showSettings($args);
        })->setName('adminSetting');

        // Save settings
        $this->post('/save', function ($args) {
            return (new AdminSettingController($this))->saveSettings();
        })->add('csrfGuardHandler')->setName('adminSettingSave');

        // Show sitemap submit page
        $this->get('/sitemap', function ($args) {
            return (new AdminController($this))->sitemap();
        })->setName('adminSitemap');

        // Update sitemap
        $this->post('/sitemap/update', function ($args) {
            return (new AdminController($this))->updateSitemap();
        })->add('csrfGuardHandler')->setName('adminSitemapUpdate');

        // Edit settings by category
        $this->get('/{category:site|contact|social}/edit', function ($args) {
            return (new AdminSettingController($this))->editSettings($args);
        })->setName('adminSettingEdit');
    });
    // End settings

    // User routes
    $this->group('/user', function () {
        // Show Users
        $this->get('[/]', function ($args) {
            return (new AdminUserController($this))->showUsers();
        })->setName('adminUser');

        // Edit User
        $this->get('/edit[/[{id:[0-9]+}]]', function ($args) {
            return (new AdminUserController($this))->editUser($args);
        })->setName('adminUserEdit');

        // Save Users
        $this->post('/save', function ($args) {
            return (new AdminUserController($this))->saveUser();
        })->add('csrfGuardHandler')->setName('adminUserSave');
    });
    // End user routes

    // Help content
    $this->get('/support/{subject:client|designer}[/{file:[a-zA-Z]+}[/{link:[a-zA-Z]+}]]', function ($args) {
        return (new AdminController($this))->showHelp($args);
    })->setName('adminHelp');

    // Fallback for when calling /admin to redirect to /admin/home (adminHome)
    $this->get('[/]', function () {
        return $this->response->withRedirect($this->router->pathFor('adminHome'));
    });
})->add(function (Request $request, Response $response, callable $next) {
    // Authentication
    $security = $this->accessHandler;

    if (!$security->isAuthenticated()) {
        // Failed authentication, redirect to login
        return $response->withRedirect($this->router->pathFor('adminLoginForm'));
    }

    // Next call
    return $next($request, $response);
})->add(function (Request $request, Response $response, callable $next) {
    // Add http no-cache, no-store headers to prevent back button access to admin
    $response = $next($request, $response);
    return $response->withAddedHeader('Cache-Control', 'private, no-cache, no-store, must-revalidate');
});

//
// Public unsecured admin routes
//

// Login page with form to submit email
$app->get('/login', function ($args) {
    return (new AdminAccessController($this))->showLoginForm();
})->setName('adminLoginForm');

// Accept and validate email, and send login token
$app->post('/requestLoginToken', function ($args) {
    return (new AdminAccessController($this))->requestLoginToken();
})->add('csrfGuardHandler')->setName('adminRequestLoginToken');

// Accept and validate login token and set session
$app->get('/processLoginToken/{token:[a-zA-Z0-9]{64}}', function ($args) {
    return (new AdminAccessController($this))->processLoginToken($args);
})->setName('adminProcessLoginToken');

// Logout
$app->get('/logout', function ($args) {
    return (new AdminAccessController($this))->logout();
})->setName('adminLogout');

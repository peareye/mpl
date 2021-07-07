<?php

/**
 * PitonCMS (https://github.com/PitonCMS)
 *
 * @link      https://github.com/PitonCMS/Piton
 * @copyright Copyright 2018 Wolfgang Moritz
 * @license   https://github.com/PitonCMS/Piton/blob/master/LICENSE (MIT License)
 */

declare(strict_types=1);

/**
 * Load Base Files and Return Application
 *
 * Set:
 * - Constants
 * - Composer autoloader
 * - Configuration
 * - Load App
 * - Dependencies
 * - Middleware
 * - Routes
 */

// Wrap bootstraping code in an anonymous function to avoid unnecessary globals
return call_user_func(
    function () {

        // Load default configuration settings
        require ROOT_DIR . 'vendor/pitoncms/engine/config/config.default.php';

        // Load local configuration settings as override
        if (file_exists(ROOT_DIR . 'config/config.local.php')) {
            require ROOT_DIR . 'config/config.local.php';
        } else {
            throw new \Exception("PitonCMS: No local configuration file found");
        }

        // Set error reporting level based on environment
        if ($config['environment']['production'] === true) {
            ini_set('display_errors', 'Off');
            error_reporting(0);
            $config['displayErrorDetails'] = false;
            $config['routerCacheFile'] = ROOT_DIR . 'cache/routerCache.php';
        } else {
            // Development
            error_reporting(-1);
            $config['displayErrorDetails'] = true;
        }

        // This flag is needed in Slim 3 to get the current route name in LoadSiteSettings
        $config['determineRouteBeforeAppMiddleware'] = true;

        // Create the Slim application and make container available to dependencies
        $app = new Slim\App(['settings' => $config]);
        $container = $app->getContainer();

        // Load dependencies
        require ROOT_DIR . 'vendor/pitoncms/engine/config/dependencies.php';

        // Load overriding dependencies
        if (file_exists(ROOT_DIR . 'config/dependencies.php')) {
            require ROOT_DIR . 'config/dependencies.php';
        }

        // Load middleware
        require ROOT_DIR . 'vendor/pitoncms/engine/config/middleware.php';

        // Load admin routes
        require ROOT_DIR . 'vendor/pitoncms/engine/config/routesAdmin.php';

        // Load front end routes
        require ROOT_DIR . 'vendor/pitoncms/engine/config/routes.php';

        // Load custom front end routes
        if (file_exists(ROOT_DIR . 'config/routes.php')) {
            require ROOT_DIR . 'config/routes.php';
        }

        return $app;
    }
);

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
 * Dependency Injection Container (DIC) Configuration
 *
 * Override any container entry in config/dependencies.php
 */

/**
 * Twig HTML Templates
 *
 * Loads
 * - Template directories
 * - Debug setting
 * - Front or Admin extensions
 * - Custom date format from site settings
 * @return Slim\Views\Twig
 */
$container['view'] = function ($c) {
    $settings = $c->get('settings');

    // Array of directories for templates
    $templatePaths[] = ROOT_DIR . 'structure/templates/';
    $templatePaths['admin'] = ROOT_DIR . 'vendor/pitoncms/engine/templates/';

    $view = new Slim\Views\Twig($templatePaths, [
        'cache' => ROOT_DIR . 'cache/twig',
        'debug' => !$settings['environment']['production'],
        'autoescape' => false,
    ]);

    // Piton Twig Extension
    $view->addExtension(new Piton\Library\Twig\Base($c));

    // Load Pagination with default results per page setting
    $view->addExtension(new Piton\Pagination\TwigPagination(['resultsPerPage' => $settings['pagination']['resultsPerPage']]));

    // Load Twig debugger if in development
    if (!$settings['environment']['production']) {
        $view->addExtension(new Twig\Extension\DebugExtension());
    }

    return $view;
};

/**
 * Monolog PSR3 Logger
 *
 * If production minimum log level is ERRROR, but if not then all are logged.
 * @return Monolog\Logger
 */
$container['logger'] = function ($c) {
    $level = ($c->get('settings')['environment']['production']) ? Monolog\Logger::ERROR : Monolog\Logger::DEBUG;
    $logger = new Monolog\Logger('app');
    $logger->pushHandler(new Monolog\Handler\StreamHandler(ROOT_DIR . 'logs/' . date('Y-m-d') . '.log', $level));

    return $logger;
};

/**
 * Database Connection
 *
 * @return PDO
 */
$container['database'] = function ($c) {
    $dbConfig = $c->get('settings')['database'];

    // Extra database options
    $dbConfig['options'][PDO::ATTR_PERSISTENT] = true;
    $dbConfig['options'][PDO::ATTR_ERRMODE] = PDO::ERRMODE_EXCEPTION;
    $dbConfig['options'][PDO::ATTR_EMULATE_PREPARES] = false;

    // Define connection string
    $dsn = "mysql:host={$dbConfig['host']};dbname={$dbConfig['dbname']};charset=utf8mb4";

    // Return connection
    return new PDO($dsn, $dbConfig['username'], $dbConfig['password'], $dbConfig['options']);
};

/**
 * Error Exception Handler
 *
 * Runtime errors and exceptions are sent to this handler to log and display message.
 */
$container['errorHandler'] = function ($c) {
    return new Piton\Library\Handlers\Error($c->get('settings')['displayErrorDetails'], $c['logger']);
};

/**
 * PHP Runtime Exception
 *
 * Directs all php7 runtime errors to the error exception handler
 */
$container['phpErrorHandler'] = function ($c) {
    return $c->errorHandler;
};

/**
 * Session Handler
 *
 * Manages session state.
 * @return Piton\Library\Handlers\Session
 */
$container['sessionHandler'] = function ($c) {
    $session = $c->get('settings')['session'];
    $session['log'] = $c->logger;
    return new Piton\Library\Handlers\Session($c['database'], $session);
};

/**
 * Access Handler
 *
 * Handler for user access control
 * @return Piton\Library\Handlers\Access
 */
$container['accessHandler'] = function ($c) {
    return new Piton\Library\Handlers\Access($c->get('sessionHandler'));
};

/**
 * Route Strategy Handler
 *
 * PitonCMS route strategy handler for Slim
 * @link https://www.slimframework.com/docs/v3/objects/router.html#route-strategies
 * @return Piton\Library\Handlers\RouteArgumentStrategy
 */
$container['foundHandler'] = function ($c) {
    return new Piton\Library\Handlers\RouteArgumentStrategy();
};

/**
 * Not Found (404)
 *
 * Override the default Slim Not Found handler
 * @return Piton\Library\Handlers\NotFound
 */
$container['notFoundHandler'] = function ($c) {
    return new Piton\Library\Handlers\NotFound($c->get('view'), $c->get('logger'));
};

/**
 * Email Handler
 *
 * @return Piton\Library\Handlers\Email
 */
$container['emailHandler'] = function ($c) {
    return new Piton\Library\Handlers\Email(
        new PHPMailer\PHPMailer\PHPMailer(true),
        $c->get('logger'),
        $c->get('settings')
    );
};

/**
 * Data Mapper
 *
 * Data mapper ORM to CRUD the database tables
 * Returns closure to request DB table datamapper
 * @return closure
 */
$container['dataMapper'] = function ($c) {
    return function ($mapper) use ($c) {
        // Load session user ID to set update column, and provide PSR3 logger
        $session = $c->sessionHandler;
        $options['sessionUserId'] = (int) $session->getData('user_id') ?? 0;
        $options['logger'] = $c['logger'];
        $options['defaultDomainObjectClass'] = 'Piton\\Models\\Entities\\PitonEntity';

        // Return instantiated mapper
        $fqn = 'Piton\\Models\\' . $mapper;
        return new $fqn($c['database'], $options);
    };
};

/**
 * Markdown Parser
 *
 * Markdown parser
 * @return Piton\Library\Utilities\MDParse
 */
$container['markdownParser'] = function ($c) {
    return new Piton\Library\Utilities\MDParse();
};

/**
 * JSON Definition Handler
 *
 * @return Piton\Library\Handlers\Definition
 */
$container['jsonDefinitionHandler'] = function ($c) {
    return new Piton\Library\Handlers\Definition($c->jsonValidator);
};

/**
 * JSON Validation
 *
 * @return JsonSchema\Validator
 */
$container['jsonValidator'] = function ($c) {
    return new JsonSchema\Validator();
};

/**
 * Misc Utility Toolbox
 *
 * Piton toolbox has various utility methods
 * @return Piton\Library\Utilities\Toolbox
 */
$container['toolbox'] = function ($c) {
    return new Piton\Library\Utilities\Toolbox();
};

/**
 * CSRF Guard Handler
 *
 * Checks submitted CSRF token on POST requests
 * @return Piton\Library\Handlers\CsrfGuard
 */
$container['csrfGuardHandler'] = function ($c) {
    return new Piton\Library\Handlers\CsrfGuard($c->sessionHandler, $c->logger);
};

/**
 * Sitemap Handler
 *
 * Creates XML sitemap based on saved pages
 * @return Piton\Library\Handlers\Sitemap
 */
$container['sitemapHandler'] = function ($c) {
    return new Piton\Library\Handlers\Sitemap($c['logger']);
};

/**
 * File Upload Handler
 *
 * Manages file uploads.
 * Renames uploaded files and places in the directory defined in the mediaPathHandler
 * @return Piton\Library\Handlers\FileUpload
 */
$container['fileUploadHandler'] = function ($c) {
    return new Piton\Library\Handlers\FileUpload($c['request']->getUploadedFiles(), $c['mediaPathHandler'], $c['filenameGenerator']);
};

/**
 * Media File Path Pattern Handler
 *
 * Define upload media path under public/media/
 * @return string
 */
$container['mediaPathHandler'] = function ($c) {
    return function ($fileName) {
        $directory = pathinfo($fileName, PATHINFO_FILENAME);
        $dir = mb_substr($directory, 0, 2);

        return "/media/$dir/$directory/";
    };
};

/**
 * Media Handler
 *
 * Resizes and optimizes media using a TinyJPG key
 * @return Piton\Library\Handlers\Media
 */
$container['mediaHandler'] = function ($c) {
    return new Piton\Library\Handlers\Media($c['mediaPathHandler'], $c['mediaSizes'], $c['settings']['site']['tinifyApiKey']);
};

/**
 * Media Image Size List
 *
 * List of image size suffixes.
 * Used as validation and to construct alternate source sets.
 * @return array
 */
$container['mediaSizeList'] = function ($c) {
    return ['xlarge', 'large', 'small', 'thumb'];
};

/**
 * Media Size Constructor
 *
 * Given a filename and a desired size, checks the size against mediaSizes and then returns
 * desired filename with size.
 * @return string
 */
$container['mediaSizes'] = function ($c) {
    return function ($filename, $size = '') use ($c) {
        if (in_array($size, $c->mediaSizeList)) {
            $parts = pathinfo($filename);
            return "{$parts['filename']}-$size.{$parts['extension']}";
        }

        // If not a listed size, just return the filename as-is
        return $filename;
    };
};

/**
 * Media Filename Generator
 *
 * Creates new filename for uploaded files
 * @return string
 */
$container['filenameGenerator'] = function ($c) {
    return function () {
        return bin2hex(random_bytes(6));
    };
};

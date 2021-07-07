<?php

/**
 * PitonCMS (https://github.com/PitonCMS)
 *
 * @link      https://github.com/PitonCMS/Piton
 * @copyright Copyright 2018 Wolfgang Moritz
 * @license   https://github.com/PitonCMS/Piton/blob/master/LICENSE (MIT License)
 */

declare(strict_types=1);

use Slim\Container as SlimContainer;
use Piton\CLI\OptimizeMedia;

/**
 * This script accepts PitonCMS command line requests
 *
 * The first argument should be the request to execute, the rest are request arguments.
 * Example:
 * 	$ php cli.php request arg1 arg2 arg3
 */

 // Exit if not a Command Line Interface request
if (PHP_SAPI !== 'cli') {
    exit;
}

// Show all errors
error_reporting(-1);

 // Set encoding
mb_internal_encoding('UTF-8');
mb_http_output('UTF-8');

// Define the application root directory
define('ROOT_DIR', dirname(__DIR__, 4) . '/');

// Load the Composer Autoloader and add Piton\CLI namespace
$loader = require ROOT_DIR . 'vendor/autoload.php';
$loader->addPsr4('Piton\\CLI\\', __DIR__);

// Load default and local configuration settings
require ROOT_DIR . 'vendor/pitoncms/engine/config/config.default.php';

if (file_exists(ROOT_DIR . 'config/config.local.php')) {
    require ROOT_DIR . 'config/config.local.php';
} else {
    echo "PitonCMS: No local configuration file found";
    exit(1);
}

// Create container
$container = new SlimContainer(['settings' => $config]);

// Load dependencies into container
require ROOT_DIR . 'vendor/pitoncms/engine/config/dependencies.php';

// Override some dependencies for the CLI environment
$container['errorHandler'] = function ($c) {
    echo "Error in Piton cli/cli.php\n";
    exit(1);
};

$container['sessionHandler'] = function ($c) {
    return new class {
        // Spoof user ID
        public function getData(string $key)
        {
            if ($key === 'user_id') {
                return 1;
            }
        }
    };
};

// Load saved site settings from data_store and merge into $container['settings']['site']
$dataStoreMapper = ($container->dataMapper)('DataStoreMapper');
$siteSettings = $dataStoreMapper->findSiteSettings() ?? [];

// Create new multi-dimensional array of 'environment' (piton) and 'site' (other category) settings
$loadSettings = [];
foreach ($siteSettings as $row) {
    if ($row->category === 'piton') {
        $loadSettings['environment'][$row->setting_key] = $row->setting_value;
    } else {
        $loadSettings['site'][$row->setting_key] = $row->setting_value;
    }
}

$container['settings']['site'] = array_merge($container['settings']['site'] ?? [], $loadSettings['site']);
$container['settings']['environment'] = array_merge($container['settings']['environment'], $loadSettings['environment']);

// Parse CLI request and ignore the filename
$argv = $GLOBALS['argv'];
array_shift($argv);

if (isset($argv[0]) && $argv[0] === 'optimizeMedia') {
    $optimizer = new OptimizeMedia($container);
    $optimizer->run();
}

exit(0);

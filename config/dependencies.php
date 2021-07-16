<?php

/**
 * Moritz Media
 *
 * @link      https://moritzmedia.com/
 * @copyright Copyright 2021
 */

/**
 * Dependency Injection Container (DIC) Custom Configuration
 */

declare(strict_types=1);

use PitonCMS\Library\MPLTwig;

/**
 * MPL Twig HTML Templates
 *
 * Custom Twig functions for MyPie London
 * @return Slim\Views\Twig
 */
$container['MPLView'] = function ($c) {
    $view = $c->view;

    // MyPie London Twig Extension
    $view->addExtension(new MPLTwig($c));

    return $view;
};

// Invoke the MPLView dependency to load the extension
$var = $container['MPLView'];

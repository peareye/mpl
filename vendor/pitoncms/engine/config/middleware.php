<?php

/**
 * PitonCMS (https://github.com/PitonCMS)
 *
 * @link      https://github.com/PitonCMS/Piton
 * @copyright Copyright 2019 Wolfgang Moritz
 * @license   https://github.com/PitonCMS/Piton/blob/master/LICENSE (MIT License)
 */

declare(strict_types=1);

/**
 * Load Middleware
 *
 * Middleware is called by Slim in reverse order (bottom up)
 */
$app->add(new Piton\Middleware\ResponseHeaders($container['settings']));
$app->add(new Piton\Middleware\LoadSiteSettings($container));

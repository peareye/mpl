<?php

/**
 * PitonCMS (https://github.com/PitonCMS)
 *
 * @link      https://github.com/PitonCMS/Piton
 * @copyright Copyright 2018 Wolfgang Moritz
 * @license   https://github.com/PitonCMS/Piton/blob/master/LICENSE (MIT License)
 */

declare(strict_types=1);

namespace Piton\Middleware;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use PDOException;

/*
 * Load site settings from database into Container
 */
class LoadSiteSettings
{
    /**
     * Container
     * @var ContainerInterface
     */
    protected $container;

    /**
     * App Settings
     * @var ArrayAccess
     */
    protected $appSettings;

    /**
     * New Settings
     * @var array
     */
    protected $settings;

    /**
     * Constructor
     *
     * @param  ContainerInterface
     * @return void
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->appSettings = $container->settings;

        $this->settings['environment'] = [];
        $this->settings['site'] = [];
    }

    /**
     * Callable
     *
     * @param  Request  $request  PSR7 request
     * @param  Response $response PSR7 response
     * @param  callable $next     Next middleware
     * @return Response
     */
    public function __invoke(Request $request, Response $response, callable $next): Response
    {
        $this->loadDatabaseSettings();
        $this->loadConfigSettings($request);

        // Update app with new settings
        $this->appSettings->replace($this->settings);

        // Next Middleware call
        return $next($request, $response);
    }

    /**
     * Load Database Settings
     *
     * @param  void
     * @return void
     */
    protected function loadDatabaseSettings(): void
    {
        // This middleware data request is the first DB query in the application lifecycle.
        // If the tables do not exist (SQLSTATE[42S02]) catch and redirect to install.php script.
        // Otherwise rethrow to let the application handler deal with whatever happened.
        try {
            $dataStoreMapper = ($this->container->dataMapper)('DataStoreMapper');
        } catch (PDOException $th) {
            // SQLSTATE[42S02]
            if ($th->getCode() === '42S02') {
                // Go to installer script
                header('Location: /install.php', true, 302);
                exit;
            }

            throw $th;
        }

        $siteSettings = $dataStoreMapper->findSiteSettings() ?? [];

        // Create new multi-dimensional array of 'environment' (piton) and 'site' (other category) settings
        foreach ($siteSettings as $row) {
            if ($row->category === 'piton') {
                $this->settings['environment'][$row->setting_key] = $row->setting_value;
            } else {
                $this->settings['site'][$row->setting_key] = $row->setting_value;
            }
        }
    }

    /**
     * Load Config Settings
     *
     * Set config file and other dynamic settings
     * @param  Request  $request  PSR7 request
     * @return void
     */
    protected function loadConfigSettings(Request $request): void
    {
        // Copy production flag from config file to keep it in the new settings array
        $this->settings['environment']['production'] = $this->appSettings['environment']['production'];

        // Generate Content Security Policy nonce
        $this->settings['environment']['cspNonce'] = base64_encode(random_bytes(16));

        // Load piton engine version form composer.lock
        if (null !== $definition = json_decode(file_get_contents(ROOT_DIR . 'composer.lock'))) {
            $engineKey = array_search('pitoncms/engine', array_column($definition->packages, 'name'));
            $this->settings['environment']['engine'] = $definition->packages[$engineKey]->version;
            $this->settings['environment']['commit'] = $definition->packages[$engineKey]->source->reference;
        }

        // This is a bit of a Slim hack. The $request object passed into the __invoke() method that actually has a route object attribute
        // Because of PSR7 immutability the $request object passed into the controller constructor is a stale copy
        // and does not have the route object attribute
        $route = $request->getAttribute('route');
        $this->settings['environment']['currentRouteName'] = ($route !== null) ? $route->getName() : null;

        // This is used to break the cache by appending to asset files as a get param
        $this->settings['environment']['assetVersion'] =
            ($this->settings['environment']['production']) ? $this->settings['environment']['engine'] : date('U');

        // Add CSRF Token and Value to environment array
        $this->settings['environment']['csrfTokenName'] = $this->container->csrfGuardHandler->getTokenName();
        $this->settings['environment']['csrfTokenValue'] = $this->container->csrfGuardHandler->getTokenValue();
        // $this->settings['environment']['csrfHeaderName'] = $this->container->csrfGuardHandler->getHeaderName();

        // Set current project directory
        $this->settings['environment']['projectDir'] = basename(ROOT_DIR);

        // Set session user info
        $this->settings['environment']['sessionUserId'] = $this->container->sessionHandler->getData('user_id');
        $this->settings['environment']['sessionUserFirstName'] = $this->container->sessionHandler->getData('first_name');
        $this->settings['environment']['sessionUserLastName'] = $this->container->sessionHandler->getData('last_name');
    }
}

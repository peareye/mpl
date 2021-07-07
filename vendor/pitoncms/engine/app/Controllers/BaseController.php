<?php

/**
 * PitonCMS (https://github.com/PitonCMS)
 *
 * @link      https://github.com/PitonCMS/Piton
 * @copyright Copyright 2018 Wolfgang Moritz
 * @license   https://github.com/PitonCMS/Piton/blob/master/LICENSE (MIT License)
 */

declare(strict_types=1);

namespace Piton\Controllers;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Piton\Pagination\TwigPagination;
use Exception;

/**
 * Piton Base Controller
 *
 * All other controllers should extend this class.
 */
class BaseController
{
    /**
     * Container
     * @var Psr\Container\ContainerInterface
     */
    protected $container;

    /**
     * Request
     * @var Psr\Http\Message\ServerRequestInterface
     */
    protected $request;

    /**
     * Response
     * @var Psr\Http\Message\ResponseInterface
     */
    protected $response;

    /**
     * Page Alerts
     * @var array
     */
    protected $alert = [];

    /**
     * Settings Array
     * @var array
     */
    protected $settings = [];

    /**
     * Constructor
     *
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->request = $container->request;
        $this->response = $container->response;
        $this->settings['site'] = $container->get('settings')['site'];
        $this->settings['environment'] = $container->get('settings')['environment'];
    }

    /**
     * Render Template
     *
     * @param string $template Path to template
     * @param mixed  $data     Data to echo, Domain object or array
     * @return Response
     */
    protected function render(string $template, $data = null): Response
    {
        $twigView = $this->container->view;

        // By making page data a Twig Global, we can access page data in block elements which are loaded by a Twig function in the templates
        $twigEnvironment = $twigView->getEnvironment();
        $twigEnvironment->addGlobal('page', $data);

        // Add application alert messages as a global to display in the template within this request
        $twigEnvironment->addGlobal('alert', $this->alert);

        return $twigView->render($this->response, $template);
    }

    /**
     * Redirect
     *
     * @param string $name Route name
     * @param array  $args Associative array of route arguments
     * @return Response
     */
    protected function redirect(string $routeName, array $args = []): Response
    {
        // Save any alert messages to session flash data for next request
        if (isset($this->alert)) {
            $session = $this->container->sessionHandler;
            $session->setFlashData('alert', $this->alert);
        }

        return $this->response->withRedirect($this->container->router->pathFor($routeName, $args));
    }

    /**
     * Show Page Not Found (404)
     *
     * Returns http status 404 Not Found and custom error template
     * @param void
     * @return Response
     */
    protected function notFound(): Response
    {
        $notFound = $this->container->get('notFoundHandler');
        return $notFound($this->request, $this->response);
    }

    /**
     * XHR Response
     *
     * Returns asynchronous response as application/json
     * @param  string $status  Status code "success"|"error"
     * @param  string|null $text    Document to sent
     * @return Response
     */
    protected function xhrResponse(string $status, ?string $text): Response
    {
        // Make sure $status is set to success or error
        if (!in_array($status, ['success', 'error'])) {
            throw new Exception("Invalid XHR Status Code");
        }

        $response = $this->response->withHeader('Content-Type', 'application/json');

        return $response->write(json_encode([
            "status" => $status,
            "text" => $text,
        ]));
    }

    /**
     * Get Pagination Object
     *
     * Returns Piton\Pagination\TwigPagination object from the Twig environment array of extensions
     * to allow update of runtime settings
     * @param void
     * @return Piton\Pagination\TwigPagination
     */
    protected function getPagination(): TwigPagination
    {
        return $this->container->view->getEnvironment()->getExtensions()['Piton\Pagination\TwigPagination'];
    }
}

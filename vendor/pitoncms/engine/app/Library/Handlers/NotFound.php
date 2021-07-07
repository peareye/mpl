<?php

/**
 * PitonCMS (https://github.com/PitonCMS)
 *
 * @link      https://github.com/PitonCMS/Piton
 * @copyright Copyright 2018 Wolfgang Moritz
 * @license   https://github.com/PitonCMS/Piton/blob/master/LICENSE (MIT License)
 */

declare(strict_types=1);

namespace Piton\Library\Handlers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\log\LoggerInterface as Logger;
use Slim\Views\Twig;

/**
 * Piton Not Found Handler
 *
 * Extends the Slim NotFound handler
 */
class NotFound extends \Slim\Handlers\NotFound
{
    /**
     * Twig View Handler
     *
     * @var \Slim\Views\Twig
     */
    protected $view;

    /**
     * Monolog Logger
     *
     * @var \Monolog\Logger
     */
    protected $logger;

    /**
     * Constructor
     *
     * @param Twig   $view   Slim Twig view handler
     * @param Logger $logger Logging
     */
    public function __construct(Twig $view, Logger $logger)
    {
        $this->view = $view;
        $this->logger = $logger;
    }

    /**
     * Invoke not found handler as callable
     *
     * Adds logging for not found pages
     * Determines public or admin facing 404 page template path
     * @param  Request $request  The most recent Request object
     * @param  Response      $response The most recent Response object
     *
     * @return ResponseInterface
     */
    public function __invoke(Request $request, Response $response): Response
    {
        // Get request URL to determine if this was thrown in /admin or on the public site
        $path = $request->getUri()->getPath();

        // If request is for a file or image then just return the 404 status and stop
        if (preg_match('/^.*\.(jpe?g|png|gif|css|js|map|ico|txt)$/i', $path)) {
            return $response->withStatus(404);
        }

        // Log 404 request
        $this->logger->info("Not Found (404): {$request->getMethod()} $path");

        // Return status 404 and template
        return parent::__invoke($request, $response);
    }

    /**
     * Return a custom not found page
     *
     * @param  Request  $request  The most recent Request object
     * @param  Response $response The most recent Response object
     * @return string
     */
    protected function renderHtmlNotFoundOutput(Request $request): string
    {
        // Set theme notFound template path
        if (file_exists(ROOT_DIR . 'structure/templates/system/notFound.html')) {
            return $this->view->fetch('system/notFound.html');
        }

        // Otherwise return default template
        return $this->view->fetch('@admin/system/notFound.html');
    }
}

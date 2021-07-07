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
use Psr\Log\LoggerInterface as Logger;
use Throwable;

/**
 * Error Handler
 *
 * Extends Slim\Handlers\Error to support logging
 */
class Error extends \Slim\Handlers\PhpError
{
    /**
     * Logger
     * @var Logger
     */
    protected $logger;

    /**
     * Constructor
     *
     * @param bool   $displayErrorDetails
     * @param Logger $logger Logging instance
     */
    public function __construct(bool $displayErrorDetails, Logger $logger)
    {
        $this->logger = $logger;
        parent::__construct($displayErrorDetails);
    }

    /**
     * Invoke error handler
     *
     * Logs error exceptions and then calls parent method
     * @param ServerRequestInterface $request   The most recent Request object
     * @param ResponseInterface      $response  The most recent Response object
     * @param Throwable              $exception The caught Throwable object
     *
     * @return ResponseInterface
     * @throws UnexpectedValueException
     */
    public function __invoke(Request $request, Response $response, Throwable $exception): Response
    {
        // Log the message
        $this->logger->error($exception->getMessage() . ' ' . $exception->getTraceAsString());

        return parent::__invoke($request, $response, $exception);
    }

    /**
     * Render HTML error page
     *
     * @param Throwable $error
     *
     * @return string
     */
    protected function renderHtmlErrorMessage(Throwable $error)
    {
        $title = '<h1>Whoops! Something went wrong...<h1>';
        $subTitle = '<p class="lead">A website error has occurred.</p>';
        $html = '';

        if ($this->displayErrorDetails) {
            $html .= '<h2>Details</h2>';
            $html .= $this->renderHtmlError($error);

            while ($error = $error->getPrevious()) {
                $html .= '<h2>Previous error</h2>';
                $html .= $this->renderHtmlError($error);
            }
        } else {
            $subTitle = '<p class="lead">It wasn\'t anything you did. Try going to the <a href="/">home</a> page to start over.</p>';
        }

        // Note: Remember to escape '%' used in CSS with another '%' (as in '%%') so sprintf() doesn't get confused
        $output = sprintf(
            "<html><head><meta http-equiv='Content-Type' content='text/html; charset=utf-8'><title>" .
            "Piton Application Error</title><style>body{margin:0;padding:30px;font:14px / 1.5 Helvetica," .
            " Arial, Verdana, sans-serif; background-color:hsl(0, 0%%, 94%%);}h1{margin:0;font-size:48px;" .
            "font-weight:normal;line-height:48px;}strong{display:inline-block;width:75px;} .lead{ font-size:18px;}" .
            " .navbar{ position:static; top:0; right:0; left:0; background-color:#336699; color:#ffffff; " .
            "font-size:22.5; padding:.75rem; padding-left:30px; margin-top:-30px; margin-left:-30px; " .
            "margin-right:-30px; margin-bottom:20px;}</style></head><body><div class=\"navbar\">PitonCMS " .
            "</div>%s %s %s</body></html>",
            $title,
            $subTitle,
            $html
        );

        return $output;
    }
}

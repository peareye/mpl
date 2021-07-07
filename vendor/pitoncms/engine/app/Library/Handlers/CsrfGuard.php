<?php

/**
 * PitonCMS (https://github.com/PitonCMS)
 *
 * @link      https://github.com/PitonCMS/Piton
 * @copyright Copyright 2019 Wolfgang Moritz
 * @license   https://github.com/PitonCMS/Piton/blob/master/LICENSE (MIT License)
 */

declare(strict_types=1);

namespace Piton\Library\Handlers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Log\LoggerInterface as Logger;
use Piton\Library\Interfaces\SessionInterface;
use Exception;

/**
 * Piton CSRF Guard
 *
 * Middleware CSRF Protection for POST routes
 * Uses the Synchronizer Token Pattern
 */
class CsrfGuard
{
    /**
     * CSRF Token Name
     * @var string
     */
    protected $csrfTokenName = 'pitonCsrfToken';

    /**
     * CSRF Header Name
     * When updating the CSRF Request Header name, also update assets/js/modules/config.js object property
     * @var string
     */
    protected $csrfHeaderName = 'Piton-CSRF-Token';

    /**
     * CSRF token from session
     * @var string
     */
    protected $csrfTokenValue;

    /**
     * Session
     * @var Piton\Library\Interfaces\SessionInterface
     */
    protected $session;

    /**
     * Logging Object
     * @var Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * Constructor
     *
     * @param  SessionInterface $session
     * @param  Logger           $logger  Logging object
     * @return void
     */
    public function __construct(SessionInterface $session, Logger $logger)
    {
        $this->session = $session;
        $this->logger = $logger;
        $this->loadSessionToken();
    }

    /**
     * Invoke CSRF Guard
     *
     * Invoked on designated POST routes
     * @param  Request  $request
     * @param  Response $respose
     * @param  callable Next middleware to run
     * @return Response
     */
    public function __invoke(Request $request, Response $response, callable $next): Response
    {
        // Validate this is a POST request
        if ($request->getMethod() === 'POST') {
            $token = $this->getRequestToken($request);

            if ($token === null || !$this->validateToken($token)) {
                // Bad token. Clear and reset
                $this->unsetToken();
                $this->loadSessionToken();
                $this->logger->info('PitonCMS: 403 Forbidden request, CSRF token null or mismatch');

                return $this->forbiddenResponse($request, $response);
            }
        }

        return $next($request, $response);
    }

    /**
     * Get Token Name
     *
     * @param  void
     * @return string
     */
    public function getTokenName(): string
    {
        return $this->csrfTokenName;
    }

    /**
     * Get Token Value
     *
     * @param  void
     * @return string
     */
    public function getTokenValue(): string
    {
        return $this->csrfTokenValue;
    }

    /**
     * Get Header Name
     *
     * @param  void
     * @return string
     */
    public function getHeaderName(): string
    {
        return $this->csrfHeaderName;
    }

    /**
     * Get Request Token
     *
     * Returns CSRF token from 1) request header or 2) form input
     * @param  Request $request
     * @return string|null
     */
    public function getRequestToken(Request $request): ?string
    {
        if (null !== $token = $request->getHeader($this->csrfHeaderName)[0] ?? null) {
            // First check request header. Because there may be more than one header with the same name, pick the first one in the array
            return $token;
        } elseif (null !== $token = $request->getParsedBodyParam($this->csrfTokenName)) {
            // Then check the form input
            return $token;
        }

        return null;
    }

    /**
     * Unset Token
     *
     * Set token value to null in session and in class
     * @param  void
     * @return void
     */
    public function unsetToken(): void
    {
        $this->session->unsetData($this->csrfTokenName);
        $this->csrfTokenValue = null;
    }

    /**
     * Validate Token
     *
     * Uses hash_equals() to compare saved token ($this->csrfTokenValue) with provided token
     * @param  string $token Token hash to comapre
     * @return bool
     */
    protected function validateToken(string $token): bool
    {
        return hash_equals($this->csrfTokenValue, $token);
    }

    /**
     * Load Token
     *
     * Loads token saved in session storage, or if not found then creates a new token and sets in session
     * @param  void
     * @return void
     */
    protected function loadSessionToken(): void
    {
        if (null === $this->csrfTokenValue = $this->session->getData($this->csrfTokenName)) {
            $this->csrfTokenValue = $this->generateToken();
            $this->session->setData($this->csrfTokenName, $this->csrfTokenValue);
        }
    }

    /**
     * Generate New Token
     *
     * Creates new token value
     * @param  void
     * @return string
     */
    protected function generateToken(): string
    {
        return base64_encode(random_bytes(64));
    }

    /**
     * Forbidden HTTP 403 Response
     *
     * Code borrowed and modified from Slim Error
     * Respond with HTTP 403 Forbidden
     * @param Request  $request   The most recent Request object
     * @param Response $response  The most recent Response object
     * @return Response
     */
    public function forbiddenResponse(Request $request, Response $response): Response
    {
        $contentType = $this->determineContentType($request);
        switch ($contentType) {
            case 'application/json':
                $output = $this->renderJsonErrorMessage();
                break;

            case 'text/html':
                $output = $this->renderHtmlErrorMessage();
                break;

            default:
                throw new Exception('Cannot render unknown content type ' . $contentType);
        }

        return $response
                ->withStatus(403)
                ->withHeader('Content-type', $contentType)
                ->write($output);
    }

    /**
     * Determine Request Content Type
     *
     * Code borrowed and modified from Slim Error
     * @param Request $request
     * @return string
     */
    protected function determineContentType(Request $request): string
    {
        $knownContentTypes = [
            'application/json',
            'text/html',
        ];

        $acceptHeader = $request->getHeaderLine('Accept');
        $selectedContentTypes = array_intersect(explode(',', $acceptHeader), $knownContentTypes);

        if (count($selectedContentTypes)) {
            return current($selectedContentTypes);
        }

        // Handle +json and +xml specially
        if (preg_match('/\+(json|xml)/', $acceptHeader, $matches)) {
            $mediaType = 'application/' . $matches[1];
            if (in_array($mediaType, $knownContentTypes)) {
                return $mediaType;
            }
        }

        return 'text/html';
    }

    /**
     * Render HTML 403 Forbidden page
     *
     * Code borrowed and modified from Slim Error
     * @param void
     * @return string
     */
    protected function renderHtmlErrorMessage(): string
    {
        $title = 'PitonCMS 403 Forbidden Error';
        $html = '<p>This request is forbidden.</p>';

        $output = sprintf(
            "<html><head><meta http-equiv='Content-Type' content='text/html; charset=utf-8'>" .
            "<title>%s</title><style>body{margin:0;padding:30px;font:12px/1.5 Helvetica,Arial,Verdana," .
            "sans-serif;}h1{margin:0;font-size:48px;font-weight:normal;line-height:48px;}strong{" .
            "display:inline-block;width:65px;}</style></head><body><h1>%s</h1>%s</body></html>",
            $title,
            $title,
            $html
        );

        return $output;
    }

    /**
     * Render JSON 403 Forbidden page
     *
     * Code borrowed and modified from Slim Error
     * @param void
     * @return string
     */
    protected function renderJsonErrorMessage(): string
    {
        $error = [
            'message' => 'Piton 403 Forbidden Error',
        ];

        return json_encode($error, JSON_PRETTY_PRINT);
    }
}

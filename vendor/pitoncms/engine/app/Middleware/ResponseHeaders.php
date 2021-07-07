<?php

/**
 * PitonCMS (https://github.com/PitonCMS)
 *
 * @link      https://github.com/PitonCMS/Piton
 * @copyright Copyright 2021 Wolfgang Moritz
 * @license   https://github.com/PitonCMS/Piton/blob/master/LICENSE (MIT License)
 */

declare(strict_types=1);

namespace Piton\Middleware;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use ArrayAccess;

/*
 * Set Dynamic Response Headers
 */
class ResponseHeaders
{
    /**
     * @var ArrayAccess
     */
    protected $settings;

    /**
     * Constructor
     *
     * @param  ArrayAccess
     * @return void
     */
    public function __construct(ArrayAccess $settings)
    {
        $this->settings = $settings;
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
        // This is an Exit middleware method so wait until exiting and get next request first
        $response = $next($request, $response);

        // Get headers from settings
        $headers = $this->settings['header'] ?? [];

        foreach ($headers as $header => $value) {
            // If header value is empty or falsey do not set header and skip iteration
            if (empty($value) || !$value) {
                continue;
            }

            // If the header value contains the string 'nonce' then expand to the current nonce base64 key
            if (mb_strpos($value, 'nonce') !== false) {
                $value = str_replace('nonce', 'nonce-' . $this->settings['environment']['cspNonce'], $value);
            }

            // Set header, except for Strict-Transport-Security (STS)
            if ($header !== 'Strict-Transport-Security') {
                $response = $response->withHeader($header, "$value");
            }

            // Only set STS if NOT on localhost as this header will force future requests to HTTPS and give you a localhost headache
            if ($header === 'Strict-Transport-Security' && mb_strtolower($request->getUri()->getHost()) !== 'localhost') {
                $response = $response->withHeader($header, "$value");
            }
        }

        return $response;
    }
}

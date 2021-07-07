<?php

/**
 * PitonCMS (https://github.com/PitonCMS)
 *
 * @link      https://github.com/PitonCMS/Piton
 * @copyright Copyright (c) 2015 - 2019 Wolfgang Moritz
 * @license   https://github.com/PitonCMS/Piton/blob/master/LICENSE (MIT License)
 */

declare(strict_types=1);

namespace Piton\Library\Handlers;

use Piton\Library\Interfaces\SessionInterface;

/**
 * Access Control Handler
 *
 * Manages Authentication and Authorization
 */
class Access
{
    /**
     * Session Handler
     *
     * @var Piton\Library\Interfaces\SessionInterface
     */
    protected $session;

    /**
    * Logged in Key Name
    *
    * @var string
    */
    protected $loggedInKey = 'loggedIn';

    /**
     * Constructor
     *
     * @param SessionInterface
     * @return void
     */
    public function __construct(SessionInterface $sessionHandler)
    {
        $this->session = $sessionHandler;
    }

    /**
     * Is Authenticated
     *
     * Checks if user is currently logged in
     * @param void
     * @return bool
     */
    public function isAuthenticated(): bool
    {
        return ($this->session->getData($this->loggedInKey)) ?: false;
    }

    /**
     * Start Authenicated Session
     *
     * @param void
     * @return void
     */
    public function startAuthenticatedSession(): void
    {
        $this->session->setData([$this->loggedInKey => true]);
    }

    /**
     * End Authenticated Session
     *
     * @param void
     * @return void
     */
    public function endAuthenticatedSession(): void
    {
        $this->session->unsetData($this->loggedInKey);
    }

    /**
     * Generate Login Token Hash
     *
     * @param void
     * @return string
     */
    public function generateLoginToken(): string
    {
        return hash('sha256', microtime() . bin2hex(random_bytes(32)));
    }

    /**
     * Is Authorized
     *
     * Validates that the user has the required role in session
     * @param string Required permission: A: Admin, S: Super Admin
     * @return bool
     */
    public function isAuthorized(string $requiredRole): bool
    {
        $userRole = $this->session->getData('role');
        $permissions = ['N' => 1, 'A' => 2, 'S' => 3];

        if (!($requiredRole === 'A' || $requiredRole === 'S')) {
            return false;
        }

        return ($permissions[$requiredRole] <= $permissions[$userRole]);
    }
}

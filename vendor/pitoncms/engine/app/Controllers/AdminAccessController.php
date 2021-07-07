<?php

/**
 * PitonCMS (https://github.com/PitonCMS)
 *
 * @link      https://github.com/PitonCMS/Piton
 * @copyright Copyright (c) 2015 - 2020 Wolfgang Moritz
 * @license   https://github.com/PitonCMS/Piton/blob/master/LICENSE (MIT License)
 */

declare(strict_types=1);

namespace Piton\Controllers;

use Psr\Http\Message\ResponseInterface as Response;

/**
 * Piton Access Controller
 *
 * Piton uses a passwordless login process, in which a user requests a one-time use login token
 * to be sent by email to the user's validated email account. The login flow is:
 *
 * 1 Render login page form, which accepts an email address
 * 2 Submit (POST) the email, and validate the email string against a list of known users
 * 3 Generate a one-time use hash token, save the token to session data, and send token in query string to user's email account
 * 4 User opens email, and submits link with token
 * 5 The application validates the submitted token to the one in session data, and if not expired an authenticated
 *      session is started
 */
class AdminAccessController extends AdminBaseController
{
    /**
     * Login Token Key Name
     * @var string
     */
    private $loginTokenKey = 'loginToken';

    /**
     * Login Token Key Expires Name
     * @var string
     */
    private $loginTokenExpiresKey = 'loginTokenExpires';

    /**
     * Show Login Form
     *
     * Render page with form to submit email
     * @param void
     * @return Response
     */
    public function showLoginForm(): Response
    {
        return $this->render('login.html');
    }

    /**
     * Request Login Token
     *
     * Validates email and sends login link to user
     * @param void
     * @return Response
     */
    public function requestLoginToken(): Response
    {
        // Get dependencies
        $session = $this->container->sessionHandler;
        $emailHandler = $this->container->emailHandler;
        $security = $this->container->accessHandler;
        $userMapper = ($this->container->dataMapper)('UserMapper');
        $email = trim($this->request->getParsedBodyParam('email'));

        // Fetch users
        $user = $userMapper->findActiveUserByEmail($email);

        // Did we find a match?
        if ($user === null) {
            // No, log and silently redirect to home
            $this->container->logger->info('PitonCMS: Failed login attempt: ' . $email);

            return $this->redirect('home');
        }

        // Belt and braces/suspenders double check
        if ($user->email === $email) {
            // Get and set token, and user ID
            $token = $security->generateLoginToken();
            $session->setData([
                $this->loginTokenKey => $token,
                $this->loginTokenExpiresKey => time() + 15*60,
                'user_id' => $user->id,
                'email' => $user->email,
                'role' => $user->role,
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
            ]);

            // Get request details to create login link and email to user
            $link = $this->request->getUri()->getBaseUrl();
            $link .= $this->container->router->pathFor('adminProcessLoginToken', ['token' => $token]);

            // Send message
            $emailHandler->setTo($user->email, '')
                ->setSubject('PitonCMS Login')
                ->setMessage("Click to login\n\n $link")
                ->send();
        }

        // Direct to home page
        return $this->redirect('home');
    }

    /**
     * Process Login Token
     *
     * Validate login token and authenticate request
     * @param array
     * @return Response
     */
    public function processLoginToken(array $args): Response
    {
        // Get dependencies
        $session = $this->container->sessionHandler;
        $security = $this->container->accessHandler;
        $savedToken = $session->getData($this->loginTokenKey);
        $tokenExpires = (int) $session->getData($this->loginTokenExpiresKey);

        // Checks whether token matches, and if within expires time
        if ($args['token'] === $savedToken && time() < $tokenExpires) {
            // Successful, set session
            $security->startAuthenticatedSession();

            // Delete token
            $session->unsetData($this->loginTokenKey);
            $session->unsetData($this->loginTokenExpiresKey);

            // Go to admin dashboard
            return $this->redirect('adminHome');
        }

        // Not valid, direct home
        $message = $args['token'] . ' saved: ' . $savedToken . ' time: ' . time() . ' expires: ' . $tokenExpires;
        $this->container->logger->info('PitonCMS: Invalid login token, supplied: ' . $message);

        return $this->notFound();
    }

    /**
     * Logout
     *
     * Unsets logged in status
     * @param void
     * @return Response
     */
    public function logout(): Response
    {
        // Unset authenticated session
        $security = $this->container->accessHandler;
        $security->endAuthenticatedSession();

        // Unset CSRF Token
        $csrfGuard = $this->container->csrfGuardHandler;
        $csrfGuard->unsetToken();

        return $this->redirect('home');
    }
}

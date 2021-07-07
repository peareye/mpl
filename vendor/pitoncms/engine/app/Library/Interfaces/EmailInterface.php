<?php

/**
 * PitonCMS (https://github.com/PitonCMS)
 *
 * @link      https://github.com/PitonCMS/Piton
 * @copyright Copyright (c) 2015 - 2019 Wolfgang Moritz
 * @license   https://github.com/PitonCMS/Piton/blob/master/LICENSE (MIT License)
 */

declare(strict_types=1);

namespace Piton\Library\Interfaces;

/**
 * Piton Email Implementation
 */
interface EmailInterface
{
    /**
     * Set From Address
     *
     * @param  string  $address From email address
     * @param  string  $name    Sender name, optional
     * @return object  $this    EmailInterface
     */
    public function setFrom(string $address, string $name = null): EmailInterface;

    /**
     * Add Recipient To Address
     *
     * Can be called multiple times to add additional recipients
     * @param  string $address To email address
     * @param  string $name    Recipient name, optiona
     * @return object $this    EmailInterface
     */
    public function setTo(string $address, string $name = null): EmailInterface;

    /**
     * Set Email Subject
     *
     * @param  string $subject Email subject line
     * @return object $this    EmailInterface
     */
    public function setSubject(string $subject): EmailInterface;

    /**
     * Set Email Message Body
     *
     * @param  string $body Email body
     * @return object $this EmailInterface
     */
    public function setMessage(string $message): EmailInterface;

    /**
     * Send Email
     *
     * @param  void
     * @return void
     */
    public function send(): void;
}

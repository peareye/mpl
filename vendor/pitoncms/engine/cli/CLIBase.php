<?php

/**
 * PitonCMS (https://github.com/PitonCMS)
 *
 * @link      https://github.com/PitonCMS/Piton
 * @copyright Copyright (c) 2015 - 2020 Wolfgang Moritz
 * @license   https://github.com/PitonCMS/Piton/blob/master/LICENSE (MIT License)
 */

declare(strict_types=1);

namespace Piton\CLI;

use Psr\Container\ContainerInterface;

/**
 * Piton CLI Base Class
 *
 */
class CLIBase
{
    /**
     * Container
     * @var Psr\Container\ContainerInterface
     */
    protected $container;

    /**
     * Process ID
     * @var int
     */
    protected $pid;

    /**
     * Application Alert Messages
     * @var array
     */
    protected $alert = [];

    /**
     * Constructor
     *
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->pid = getmypid();
    }

    /**
     * Destructor
     *
     * Saves any alert notices to appAlert in data_store, which will then be displayed to the user in next admin request
     * @param void
     * @return void
     */
    public function __destruct()
    {
        // Save any alerts
        if (!empty($this->alert)) {
            $dataMapper = ($this->container->dataMapper)('DataStoreMapper');

            foreach ($this->alert as $notice) {
                $dataMapper->setAppAlert($notice['severity'], $notice['heading'], $notice['message']);
            }
        }
    }

    /**
     * Set Alert
     *
     * Set alert using appAlert data_store to display to next admin user request
     * Severity levels are one of: 'primary','secondary','success','danger','warning','info'
     * @param string        $severity Severity level color code
     * @param string        $heading  Heading text
     * @param string|array  $messge   Message or array of messages (Optional)
     * @return void
     * @throws Exception
     */
    protected function setAlert(string $severity, string $heading, $message = null): void
    {
        // Alert message is displayed in the admin base template
        $this->alert[] = [
            'severity' => $severity,
            'heading' => $heading,
            'message' => (is_array($message)) ? $message : [$message]
        ];
    }

    /**
     * Print Output
     *
     * Prints output with a trailing new line
     * @param $message
     * @return void
     */
    protected function print($message)
    {
        echo $message, PHP_EOL;
    }

    /**
     * Log Event
     *
     * Saves messages to log file
     * Also sends to print() for debugging
     * @param string $message
     * @param string $severity PSR-3 Log level, defaults to 'info'
     * @return void
     */
    protected function log(string $message, string $severity = 'info'): void
    {
        if (!is_string($message)) {
            $message = print_r($message, true);
        }

        $this->container->logger->{$severity}("PitonCLI: PID: {$this->pid} $message");
        $this->print($message);
    }
}

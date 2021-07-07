<?php

/**
 * PitonCMS (https://github.com/PitonCMS)
 *
 * @link      https://github.com/PitonCMS/Piton
 * @copyright Copyright 2019 Wolfgang Moritz
 * @license   https://github.com/PitonCMS/Piton/blob/master/LICENSE (MIT License)
 */

namespace Piton\Library\Utilities;

use Composer\Script\Event;

/**
 * Piton Build Scripts
 */
class PitonBuild
{
    /**
     * Piton Create Project
     *
     * Called after composer create-project
     * Updates docker build for current project directory
     * @param Event $event
     * @return void
     */
    public static function createProject(Event $event): void
    {
        static::printOutput("...Completing new project setup");

        static::updateDockerYaml();
        static::updateApacheHost();
        static::copyConfig();

        static::printOutput('> To start Docker, from the root of this project first run \'docker-compose build\' to create the image, a one-time step.', 'info');
        static::printOutput('> Then run \'docker-compose up -d\' and navigate to http://localhost to finish the installation.', 'info');
    }

    /**
     * Piton Update Engine
     *
     * Called after running composer update on pitoncms/engine.
     * @param Event $event
     * @return void
     */
    public static function updateEngine(Event $event): void
    {
        // TODO
        static::printOutput("Update completed. It is best to run composer update from within the Docker container.");
    }

    /**
     * Copy Config File
     *
     * @param void
     * @return void
     */
    protected static function copyConfig()
    {
        static::printOutput("...Creating config file for local development");

        $projectDir = self::getProjectDir();
        $salt = bin2hex(random_bytes(32));
        $lines = file('vendor/pitoncms/engine/config/config.default.php');

        // Update docblock
        $lines[2] = ' * Local Environment Configuration Settings' . PHP_EOL;
        $lines[4] = ' * Define environment specific settings in this file.' . PHP_EOL;
        $lines[5] = ' * DO NOT commit to VCS!' . PHP_EOL;

        // Set development configuration settings
        if ($lines) {
            foreach ($lines as &$line) {
                // Production environment to false
                if (strpos($line, 'environment') !== false && strpos($line, 'production') !== false) {
                    $line = str_replace('true', 'false', $line);
                }

                // Change localhost to Docker Compose image db
                if (strpos($line, 'database') !== false && strpos($line, 'host') !== false) {
                    $line = str_replace('localhost', 'db', $line);
                }

                // Change database name to project name
                if (strpos($line, 'database') !== false && strpos($line, 'dbname') !== false) {
                    $line = str_replace('\'\'', '\'' . $projectDir . '\'', $line);
                }

                // Change database username to project name
                if (strpos($line, 'database') !== false && strpos($line, 'username') !== false) {
                    $line = str_replace('\'\'', '\'' . $projectDir . '\'', $line);
                }

                // Change database password to project name
                if (strpos($line, 'database') !== false && strpos($line, 'password') !== false) {
                    $line = str_replace('\'\'', '\'' . $projectDir . '\'', $line);
                }

                // Change session cookie name to project name
                if (strpos($line, 'session') !== false && strpos($line, 'cookieName') !== false) {
                    $line = str_replace('\'\'', '\'' . $projectDir . '\'', $line);
                }

                // Set session salt to unique hash
                if (strpos($line, 'session') !== false && strpos($line, 'salt') !== false) {
                    $line = str_replace('\'\'', '\'' . $salt . '\'', $line);
                }

                // Set secure cookie to false
                if (strpos($line, 'session') !== false && strpos($line, 'secureCookie') !== false) {
                    $line = str_replace('true', 'false', $line);
                }
            }

            static::printOutput("> If using SMTP email set credentials in config.local.php.", 'info');

            file_put_contents('config/config.local.php', $lines);
        } else {
            static::printOutput("Unable to read vendor/pitoncms/engine/config/config.default.php.", 'error');
            static::printOutput("Copy vendor/pitoncms/engine/config/config.default.php to config/config.local.php and edit configuration settings", 'error');
        }
    }

    /**
     * Update Docker Yaml
     *
     * Update docker-compose.yml file with project name.
     * @param  void
     * @return void
     */
    protected static function updateDockerYaml()
    {
        static::printOutput("...Updating docker-compose.yml for project");

        $projectDir = self::getProjectDir();
        $lines = file('docker-compose.yml');

        if ($lines) {
            foreach ($lines as &$line) {
                if (strpos($line, 'image') !== false) {
                    $line = str_replace('piton', $projectDir, $line);
                }

                if (strpos($line, '/var/www/') !== false) {
                    $line = str_replace('piton', $projectDir, $line);
                }

                if (strpos($line, 'mysql-data') !== false) {
                    $line = str_replace('piton', $projectDir, $line);
                }

                if (strpos($line, 'MYSQL_DATABASE') !== false) {
                    $line = str_replace('piton', $projectDir, $line);
                }

                if (strpos($line, 'MYSQL_USER') !== false) {
                    $line = str_replace('piton', $projectDir, $line);
                }

                if (strpos($line, 'MYSQL_PASSWORD') !== false) {
                    $line = str_replace('piton', $projectDir, $line);
                }
            }

            file_put_contents('docker-compose.yml', $lines);
        } else {
            static::printOutput("Unable to read docker-compose.yml. Update manually to use docker-compose.", "error");
        }
    }

    /**
     * Update Apache Host
     *
     * Update docker/web/apache-host.conf directory paths using project name
     * @param  void
     * @return void
     */
    protected static function updateApacheHost()
    {
        static::printOutput("...Writing apache host file for project");

        $projectDir = self::getProjectDir();
        $lines = file('docker/web/apache-host.conf');

        if ($lines) {
            foreach ($lines as &$line) {
                if (strpos($line, 'DocumentRoot') !== false) {
                    $line = str_replace('piton', $projectDir, $line);
                }

                if (strpos($line, '<Directory') !== false) {
                    $line = str_replace('piton', $projectDir, $line);
                }
            }

            file_put_contents('docker/web/apache-host.conf', $lines);
        } else {
            static::printOutput("Unable to read docker/web/apache-host.conf. Update manually to use docker-compose.", "error");
        }
    }

    /**
     * Get Project Directory
     *
     */
    protected static function getProjectDir()
    {
        // This class is 6 levels deep from project root
        return basename(dirname(__DIR__, 6));
    }

    /**
     * Print Output
     *
     * @param string $string
     * @param string $type status|info|error
     * @return void
     */
    protected static function printOutput(string $string, $type = 'status')
    {
        if ($type === 'status') {
            echo "\033[0;32m$string\033[0m\n";
        } elseif ($type === 'info') {
            echo "\033[43m$string\033[0m\n";
        } elseif ($type === 'error') {
            echo "\033[1;37m\033[41mError: $string\033[0m\n";
            exit;
        }
    }
}

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
 * Piton Session Implementation
 */
interface SessionInterface
{
    /**
     * Set Data
     *
     * Set key => value or an array of key => values to the session data array.
     * @param mixed  $data  Session data array or string (key)
     * @param string $value Value for single key
     * @return void
     */
    public function setData($data, $value = '');

    /**
     * Unset Data
     *
     * Unset a specific key from the session data array, or clear the entire array
     * @param string $key Session data array key
     * @return void
     */
    public function unsetData($key = null);

    /**
     * Get Data
     *
     * Return a specific key => value or the array of key => values from the session data array.
     * @param string $key Session data array key
     * @return mixed      Value or array, default null
     */
    public function getData($key = null);

    /**
     * Set Flash Data
     *
     * Set flash data that will persist only until next request
     * @param mixed  $data  Flash data array or string (key)
     * @param string $value Value for single key
     */
    public function setFlashData($data, $value = '');

    /**
     * Get Flash Data
     *
     * Returns flash data
     * @param string $key Flash data array key
     * @return mixed      Value or array
     */
    public function getFlashData($key = null);
}

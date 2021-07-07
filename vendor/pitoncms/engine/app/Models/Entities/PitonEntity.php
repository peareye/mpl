<?php

/**
 * PitonCMS (https://github.com/PitonCMS)
 *
 * @link      https://github.com/PitonCMS/Piton
 * @copyright Copyright (c) 2015 - 2019 Wolfgang Moritz
 * @license   https://github.com/PitonCMS/Piton/blob/master/LICENSE (MIT License)
 */

declare(strict_types=1);

namespace Piton\Models\Entities;

use Piton\ORM\DomainObject;

/**
 * Piton Entity Value Object
 */
class PitonEntity extends DomainObject
{
    /**
     * Get Object Property
     *
     * The switch statement maps non-existent camelCase properties to real properties in database
     * @param  string $key Property name to get
     * @return mixed      Property value | null
     */
    public function __get($key)
    {
        if ($this->getCamelCaseToUnderScores($key)) {
            return $this->{$key};
        }

        return parent::__get($key);
    }

    /**
     * Isset Properties
     *
     * This is allows Twig to use non-existent camelCase equivalents in templates
     * @param string $key
     * @return mixed
     */
    public function __isset($key)
    {
        return $this->getCamelCaseToUnderScores($key);
    }

    /**
     * Get Camel Case to Under Score Property Value
     *
     * Converts camelCase property values to underscores and checks if property exists
     * If it does, then adds the camelCase property to this object with a pointer to the under score equivalent
     * @param string $key
     * @return bool
     */
    private function getCamelCaseToUnderScores($key): bool
    {
        // Split camelCase variables to underscores and see if there is a match to an existing property
        $property = strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $key));

        // Create object reference to actual property
        if (property_exists($this, $property)) {
            $this->{$key} = $this->{$property};

            return true;
        }

        return false;
    }
}

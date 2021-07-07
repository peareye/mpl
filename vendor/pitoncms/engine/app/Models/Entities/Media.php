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

/**
 * Piton Media Value Object
 */
class Media extends PitonEntity
{
    /**
     * Derived properties calculated at runtime
     */
    public $aspectRatio;
    public $orientation;
    public $featured;

    /**
     * Constructor
     */
    public function __construct()
    {
        if (isset($this->height) && $this->height > 0) {
            $this->aspectRatio = round($this->width / $this->height, 2);
            $this->orientation = ($this->aspectRatio > 1) ? 'landscape' : 'portrait';
        }

        $this->featured = ($this->feature == 'Y') ? 'featured-img' : null;
    }
}

<?php

/**
 * PitonCMS (https://github.com/PitonCMS)
 *
 * @link      https://github.com/PitonCMS/Piton
 * @copyright Copyright 2015 Wolfgang Moritz
 * @license   https://github.com/PitonCMS/Piton/blob/master/LICENSE (MIT License)
 */

declare(strict_types=1);

namespace Piton\Pagination;

use Piton\Pagination\PaginationTrait;

/**
 * Renders Page Number Links
 */
class Pagination
{
    // Import main pagination code
    use PaginationTrait;

    /**
     * Print Pagination
     *
     * Render pagination links HTML
     * @param  void
     * @return void
     */
    public function __invoke()
    {
        $this->buildPageLinks();
        $this->render();
    }
}

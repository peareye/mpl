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
use Twig\Extension\AbstractExtension;
use Twig\Extension\GlobalsInterface;
use Twig\TwigFunction;
use Twig\Environment;

/**
 * Renders Page Number Links
 * Use this class if using Twig
 */
class TwigPagination extends AbstractExtension implements GlobalsInterface
{
    use PaginationTrait;

    /**
     * Register Global variables
     *
     * @param void
     * @return array
     */
    public function getGlobals(): array
    {
        return [
            'currentPageNumber' => $this->getCurrentPageNumber(),
        ];
    }

    /**
     * Register Custom Functions
     *
     * @param void
     * @return array
     */
    public function getFunctions(): array
    {
        return [
            new TwigFunction('pagination', [$this, 'pagination'], ['needs_environment' => true, 'is_safe' => ['html']]),
        ];
    }

    /**
     * Pagination
     *
     * Render pagination links HTML
     * @param  Environment $env Twig Environment
     * @return void
     */
    public function pagination(Environment $env): void
    {
        $this->buildPageLinks();
        $this->render($env);
    }
}

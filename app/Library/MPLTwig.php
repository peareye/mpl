<?php

/**
 * Moritz Media
 *
 * @link      https://moritzmedia.com/
 * @copyright Copyright 2021
 */

declare(strict_types=1);

namespace PitonCMS\Library;

use Psr\Container\ContainerInterface;
use Twig\Extension\AbstractExtension;
use Twig\Extension\GlobalsInterface;
use Twig\TwigFunction;

/**
 * MPL Twig Custom Extension
 */
class MPLTwig extends AbstractExtension implements GlobalsInterface
{

    /**
     * Dependency Container
     * @var Psr\Container\ContainerInterface
     */
    protected $container;

    /**
     * Cache
     * @var array
     */
    protected $cache = [];

    /**
     * Constructor
     *
     * @param object Psr\Container\ContainerInterface
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * Register Global variables
     *
     * @param void
     * @return array
     */
    public function getGlobals(): array
    {
        return [];
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
            new TwigFunction('getActiveMenus', [$this, 'getActiveMenus']),
        ];
    }

    /**
     * Get Active Menus
     *
     * @param int $collectionId
     * @return Piton\Pagination\TwigPagination
     */
    public function getActiveMenus(int $collectionId): ?array
    {
        // Check cache first
        if (isset($this->cache['activeMenus'])) {
            return $this->cache['activeMenus'];
        }

        // Get dependencies
        $pageMapper = ($this->container->dataMapper)('MPLPageMapper', 'PitonCMS\\Models\\');
        $dataStoreMapper = ($this->container->dataMapper)('MPLDataStoreMapper', 'PitonCMS\\Models\\');
        $pageElementMapper = ($this->container->dataMapper)('MPLPageElementMapper', 'PitonCMS\\Models\\');

        // Get all menu collection pages with future publish dates
        $menus = $pageMapper->findActiveMenuPagesByCollectionId($collectionId);

        // If no menus were found then return null
        if (!$menus) {
            return $this->cache['activeMenus'] = null;
        }

        // Get all elements and data fields
        $pageIds = array_column($menus, 'id');
        $elements = $pageElementMapper->findElementsInPageIds($pageIds) ?? [];
        $settings = $dataStoreMapper->findPageAndElementSettingsInPageIds($pageIds) ?? [];

        // Put it all together
        foreach ($menus as &$menu) {
            // Load custom page settings
            $menu->setPageSettings($settings);

            // Load custom element settings into elements
            array_walk($elements, function ($el) use ($settings) {
                $el->setElementSettings($settings);
            });

            // Load elements into blocks
            $menu->setBlockElements($elements);
        }

        return $this->cache['activeMenus'] = $menus;
    }
}

<?php

/**
 * PitonCMS (https://github.com/PitonCMS)
 *
 * @link      https://github.com/PitonCMS/Piton
 * @copyright Copyright (c) 2015 - 2020 Wolfgang Moritz
 * @license   https://github.com/PitonCMS/Piton/blob/master/LICENSE (MIT License)
 */

declare(strict_types=1);

namespace Piton\Models;

use Piton\ORM\DataMapperAbstract;

/**
 * Piton Navigation Mapper
 */
class NavigationMapper extends DataMapperAbstract
{
    protected $table = 'navigation';
    protected $modifiableColumns = ['navigator','parent_id','sort','page_id', 'collection_id', 'title', 'url'];

    /**
     * Find Navigation Structure
     *
     * Selects all nav rows for this navigator, and returns in a flat array
     * Note: Similar to findNavigation but does not include all matching collection detail pages. Use to manage navigator.
     * @param string $navigator Navigator name
     * @return array|null
     */
    public function findNavigationStructure(string $navigator): ?array
    {
        $this->sql =<<<SQL
select
    n.navigator,
    n.id,
    n.parent_id,
    n.sort,
    n.title nav_title,
    n.url,
    n.collection_id,
    c.collection_title,
    c.collection_slug,
    p.id page_id,
    p.title page_title,
    p.published_date,
    p.page_slug page_slug
from navigation n
left join page p on n.page_id = p.id
left join collection c on n.collection_id = c.id
where n.navigator = ?
order by n.sort;
SQL;

        $this->bindValues[] = $navigator;

        return $this->find();
    }

    /**
     * Find Navigation
     *
     * Selects all nav rows for this navigator including all matching collection detail pages.
     * Note: Similar to findNavigationStructure but includes all matching collection pages. Use to display navigator.
     * @param string $navigator Navigator name
     * @return array|null
     */
    public function findNavigation(string $navigator): ?array
    {
        $this->sql =<<<SQL
    select
        n.navigator,
        n.id,
        n.parent_id,
        n.sort,
        n.title nav_title,
        n.url,
        n.collection_id,
        c.collection_title,
        c.collection_slug,
        coalesce(p.id, cp.id) page_id,
        coalesce(p.title, cp.title) page_title,
        coalesce(p.published_date, cp.published_date) published_date,
        coalesce(p.page_slug, cp.page_slug) page_slug
    from navigation n
    left join page p on n.page_id = p.id
    left join collection c on n.collection_id = c.id
    left join page cp on c.id = cp.collection_id
    where n.navigator = ?
    order by n.sort, cp.published_date desc

SQL;
        $this->bindValues[] = $navigator;

        return $this->find();
    }

    /**
     * Build Navigation
     *
     * Takes flat array of navigation links and builds multi-dimensional array of navigation items nested by parent ID
     * @param array|null $navList      Array of navigation links
     * @param string     $currentRoute Page slug
     * @param bool       $isPublished  Flag to only return published pages
     * @param int|null   $parentId
     * @return array|null
     */
    public function buildNavigation(?array $navList, string $currentRoute = null, bool $isPublished = true, int $parentId = null): ?array
    {
        if (empty($navList)) {
            return null;
        }

        $nav = [];
        foreach ($navList as $row) {
            // Published page check, skip ahead if page is not published
            if ($isPublished && !is_null($row->page_id) && (is_null($row->published_date) || $row->published_date > $this->today)) {
                continue;
            }

            // Check if navigation list row matches requested parent. First pass is where parent_id is null
            if ($row->parent_id === $parentId) {
                // Is this the current route? If so set currentPage flag
                if ($currentRoute === $row->page_slug) {
                    $row->currentPage = true;
                }

                // Set title
                $row->title = $row->nav_title ?? $row->page_title ?? $row->collection_title;

                // Add any children
                $children = $this->buildNavigation($navList, $currentRoute, $isPublished, $row->id);

                if ($children) {
                    $row->childNav = $children;
                }

                // Asign to return navigator array
                $nav[] = $row;
            }
        }

        return $nav;
    }

    /**
     * Delete by Page ID
     *
     * Delete navigation record by page_id
     * @param  int $pageId Page ID
     * @return bool
     */
    public function deleteByPageId(int $pageId): bool
    {
        $this->sql = 'delete from `navigation` where `page_id` = ?;';
        $this->bindValues[] =  $pageId;

        return $this->execute();
    }
}

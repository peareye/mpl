<?php

/**
 * Moritz Media
 *
 * @link      https://moritzmedia.com/
 * @copyright Copyright 2021
 */

declare(strict_types=1);

namespace PitonCMS\Models;

use Piton\Models\PageMapper;

/**
 * MPL Page Mapper
 */
class MPLPageMapper extends PageMapper
{
    /**
     * Find Active Menu Pages by Menu Collection ID
     *
     * Active menus use the publish_date as the pitch date. Show all unpublished menus until the publish date.
     * @param  int   $collectionId
     * @return array|null
     */
    public function findActiveMenuPagesByCollectionId(int $collectionId): ?array
    {
        $this->makeSelect();
        $this->sql .= " and c.id = ? and p.published_date >= '{$this->today}'";
        $this->bindValues[] = $collectionId;

        $this->sql .= ' order by p.published_date desc';

        return $this->find();
    }
}

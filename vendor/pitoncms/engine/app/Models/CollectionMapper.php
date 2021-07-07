<?php

/**
 * PitonCMS (https://github.com/PitonCMS)
 *
 * @link      https://github.com/PitonCMS/Piton
 * @copyright Copyright (c) 2015 - 2019 Wolfgang Moritz
 * @license   https://github.com/PitonCMS/Piton/blob/master/LICENSE (MIT License)
 */

declare(strict_types=1);

namespace Piton\Models;

use Piton\ORM\DataMapperAbstract;
use Piton\Models\Entities\PitonEntity;

/**
 * Piton Collection Mapper
 */
class CollectionMapper extends DataMapperAbstract
{
    protected $table = 'collection';
    protected $modifiableColumns = ['collection_slug', 'collection_title', 'collection_definition' ];

    /**
     * Find Collection by ID
     *
     * Override from parent class to allow use of table alias.
     * Find one table row using the primary key ID
     * @param  int   $id Primary key ID
     * @return PitonEntity|null
     */
    public function findById(int $id): ?PitonEntity
    {
        $this->makeSelect(false, ' and c.id = ?');
        $this->bindValues[] = $id;

        return $this->findRow();
    }

    /**
     * Find Collection by Slug
     *
     * @param string $collectionSlug
     * @return PitonEntity|null
     */
    public function findCollectionBySlug(string $collectionSlug = null): ?PitonEntity
    {
        $this->makeSelect(false, ' and c.collection_slug = ?');
        $this->bindValues[] = $collectionSlug;

        return $this->findRow();
    }

    /**
     * Make Default Collection Select
     *
     * Make select statement with collection page detail count
     * Overrides and sets $this->sql.
     * The $andClauses parameter is added before the group by statements
     * @param  bool $foundRows Set to true to get foundRows() after query
     * @param  string $andClauses Optional conditions to include in query
     * @return void
     */
    protected function makeSelect(bool $foundRows = false, string $andClauses = '')
    {
        $this->sql = <<<SQL
select
    c.id,
    c.collection_title,
    c.collection_slug,
    c.collection_definition,
    count(p.id) page_count
from collection c
left outer join page p on c.id = p.collection_id
where 1=1
$andClauses
group by
    c.id,
    c.collection_title,
    c.collection_slug,
    c.collection_definition
SQL;
    }
}

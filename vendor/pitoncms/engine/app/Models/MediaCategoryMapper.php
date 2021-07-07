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
 * Piton Media Category Mapper
 */
class MediaCategoryMapper extends DataMapperAbstract
{
    protected $table = 'media_category';
    protected $modifiableColumns = ['category'];

    /**
     * Find Categories
     *
     * Find all categories sorted by category name
     * @param  void
     * @return array|null
     */
    public function findCategories(): ?array
    {
        $this->makeSelect();
        $this->sql .= ' order by category';

        return $this->find();
    }
}

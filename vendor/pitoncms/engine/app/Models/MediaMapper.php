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
 * Piton Media Mapper
 */
class MediaMapper extends DataMapperAbstract
{
    protected $table = 'media';
    protected $modifiableColumns = [
        'filename',
        'width',
        'height',
        'feature',
        'caption',
        'mime_type',
        'optimized',
    ];
    protected $domainObjectClass = __NAMESPACE__ . '\Entities\Media';

    // Status codes for processing new images
    protected $optimizedStatus = [
        'new' => 'new',
        'complete' => 'complete',
        'retry' => 'retry',
        'exclude' => 'exclude'
    ];

    /**
     * Find All Media
     *
     * Return all media records
     * @param  int  $limit
     * @param  int  $offset
     * @return array|null
     */
    public function findAllMedia(int $limit = null, int $offset = null): ?array
    {
        $this->mediaSelectJoinCategory();
        $this->sql .= ' order by m.`created_date` desc';

        if ($limit) {
            $this->sql .= " limit ?";
            $this->bindValues[] = $limit;
        }

        if ($offset) {
            $this->sql .= " offset ?";
            $this->bindValues[] = $offset;
        }

        return $this->find();
    }

    /**
     * Find Media By Category ID
     *
     * Find media by category ID
     * @param  int|null  $categoryId
     * @param  int       $limit
     * @param  int       $offset
     * @return array|null
     */
    public function findMediaByCategoryId(?int $categoryId, int $limit = null, int $offset = null): ?array
    {
        // If no category ID was provided just return
        if (null === $categoryId) {
            return null;
        }

        $this->sql = <<<SQL
select SQL_CALC_FOUND_ROWS
    m.id,
    m.filename,
    m.width,
    m.height,
    m.feature,
    m.caption,
    m.optimized,
    m.mime_type,
    m.created_date,
    mcm.media_sort
from media m
join media_category_map mcm on m.id = mcm.media_id
where mcm.category_id = ?
order by mcm.media_sort
SQL;

        $this->bindValues[] = $categoryId;

        if ($limit) {
            $this->sql .= " limit ?";
            $this->bindValues[] = $limit;
        }

        if ($offset) {
            $this->sql .= " offset ?";
            $this->bindValues[] = $offset;
        }

        return $this->find();
    }

    /**
     * Find Media By Category ID and Featured Flag
     *
     * Returns matching media records with all assigned categories
     * Category and Featured are the unique set (inclusive)
     * @param  int|null     $categoryId
     * @param  string|null  $featured Y|N
     * @param  int          $limit
     * @param  int          $offset
     * @return array|null
     */
    public function findMediaByCategoryIdAndFeatured(?int $categoryId, ?string $featured, int $limit = null, int $offset = null): ?array
    {
        // Build optional where clause
        $where = '';

        // Category filter
        if ($categoryId !== null && $categoryId !== 0) {
            $where .= " and mcm.category_id = ?";
            $this->bindValues[] = $categoryId;
        }

        // Featured flag filter
        if ($featured !== null && in_array($featured, ['Y', 'N'])) {
            $where .= " and m.feature = ?";
            $this->bindValues[] = $featured;
        }

        $this->sql = <<<SQL
select SQL_CALC_FOUND_ROWS
    m.id,
    m.filename,
    m.width,
    m.height,
    m.feature,
    m.caption,
    m.optimized,
    m.mime_type,
    m.created_date,
    (select group_concat(mcm2.category_id)
       from media_category_map mcm2
      where mcm2.media_id = m.id) category_id_list
from media m
left join media_category_map mcm on m.id = mcm.media_id
where 1=1
$where
order by mcm.media_sort
SQL;



        if ($limit) {
            $this->sql .= " limit ?";
            $this->bindValues[] = $limit;
        }

        if ($offset) {
            $this->sql .= " offset ?";
            $this->bindValues[] = $offset;
        }

        return $this->find();
    }

    /**
     * Text Search Media
     *
     * This query searches each of these fields for having all supplied terms:
     *  - media.caption
     * @param  string $terms  Search terms
     * @param  int    $limit  Limit
     * @param  int    $offset Offset
     * @return array|null
     */
    public function searchMedia(string $terms, int $limit = null, int $offset = null): ?array
    {
        $where = ' and match(m.caption) against (? IN BOOLEAN MODE)';
        $this->bindValues[] = $terms;

        $this->mediaSelectJoinCategory($where);
        $this->sql .= ' order by `created_date` desc';

        if ($limit) {
            $this->sql .= " limit ?";
            $this->bindValues[] = $limit;
        }

        if ($offset) {
            $this->sql .= " offset ?";
            $this->bindValues[] = $offset;
        }

        return $this->find();
    }

    /**
     * Make Default Media Select
     *
     * Make select statement
     * Overrides and sets $this->sql.
     * @param  string|null $where Optional where clause staring with "and..."
     * @return void
     */
    protected function mediaSelectJoinCategory(string $where = null): void
    {
        $this->sql = <<<SQL
select SQL_CALC_FOUND_ROWS
    m.id,
    m.filename,
    m.width,
    m.height,
    m.feature,
    m.caption,
    m.optimized,
    m.mime_type,
    m.created_date,
    group_concat(mcm.category_id) category_id_list
from media m
left join media_category_map mcm on m.id = mcm.media_id
where 1=1 $where
group by
    m.id,
    m.filename,
    m.width,
    m.height,
    m.feature,
    m.caption,
    m.optimized,
    m.mime_type,
    m.created_date
SQL;
    }

    /**
     * Get New Media to Optimize
     *
     * Get unoptimized 'new' media files to process
     * @param  string $key
     * @return array|null
     */
    public function findNewMediaToOptimize(string $key): ?array
    {
        // Set the key on 'new' rows
        $this->sql = "update `media` set `optimized` = '$key' where `optimized` = ? and `mime_type` in ('image/png', 'image/jpeg');";
        $this->bindValues[] = $this->optimizedStatus['new'];
        $this->execute();

        // Now select those rows marked for optimization
        $this->sql = "select `id`, `filename`, `optimized` from `media` where `optimized` = ?;";
        $this->bindValues[] = $key;

        return $this->find();
    }

    /**
     * Optimized Key Exists
     *
     * Checks if the provided key is already in use - highly unlikely
     * @param string $key Key to search for
     * @return bool
     */
    public function optimizeKeyExists(string $key): bool
    {
        $this->sql = "select `id` from `media` where `optimized` = ? limit 1;";
        $this->bindValues[] = $key;

        return ($this->findRow()) ?? false;
    }

    /**
     * Set Optimized Complete Status
     *
     * After optimizaion, set media row to completed
     * @param int $id Media ID
     * @param string $status Status code from $this->optimizedStatus
     * @return void
     */
    public function setOptimizedStatus(int $id, string $status): void
    {
        $this->sql = "update `media` set `optimized` = ? where `id` = ?";
        $this->bindValues[] = $this->optimizedStatus[$status];
        $this->bindValues[] = $id;

        $this->execute();
    }

    /**
     * Get Optimized Status Code
     *
     * Returns status code for use in record
     * @param string $key
     * @return string
     */
    public function getOptimizedCode(string $key): string
    {
        return $this->optimizedStatus[$key];
    }
}

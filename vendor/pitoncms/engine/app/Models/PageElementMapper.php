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

/**
 * Piton Page Element Mapper
 */
class PageElementMapper extends DataMapperAbstract
{
    protected $table = 'page_element';
    protected $modifiableColumns = [
        'page_id',
        'block_key',
        'template',
        'element_sort',
        'title',
        'content',
        'excerpt',
        'collection_id',
        'gallery_id',
        'media_id',
        'embedded'
    ];
    protected $domainObjectClass = __NAMESPACE__ . '\Entities\PageElement';

    /**
     * Find Elements by Page ID
     *
     * @param int    $pageId Page ID
     * @return array|null
     */
    public function findElementsByPageId(int $pageId): ?array
    {
        $this->sql = <<<SQL
select  page_element.*,
        media.id media_id,
        media.filename media_filename,
        media.width media_width,
        media.height media_height,
        media.feature media_feature,
        media.caption media_caption
from page_element
left join media on media.id = page_element.media_id
where page_element.page_id = ?
order by block_key, element_sort
SQL;

        $this->bindValues[] = $pageId;

        return $this->find();
    }
}

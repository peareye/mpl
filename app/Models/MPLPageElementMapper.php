<?php

/**
 * Moritz Media
 *
 * @link      https://moritzmedia.com/
 * @copyright Copyright 2021
 */

declare(strict_types=1);

namespace PitonCMS\Models;

use Piton\Models\PageElementMapper;

/**
 * MPL Page Element Mapper
 */
class MPLPageElementMapper extends PageElementMapper
{
    /**
     * Find Elements for Page ID's
     *
     * @param array       $pageIds Array of page IDs
     * @return array|null
     */
    public function findElementsInPageIds(array $pageIds): ?array
    {
        $Ids = implode(', ', $pageIds);

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
where page_element.page_id in ($Ids)
order by block_key, element_sort
SQL;

        return $this->find();
    }
}

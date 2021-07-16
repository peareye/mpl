<?php

/**
 * Moritz Media
 *
 * @link      https://moritzmedia.com/
 * @copyright Copyright 2021
 */

declare(strict_types=1);

namespace PitonCMS\Models;

use Piton\Models\DataStoreMapper;

/**
 * MPL Data Store Mapper
 */
class MPLDataStoreMapper extends DataStoreMapper
{
    /**
     * Find All Page and Element Settings for Given Page ID's
     *
     * Get all page and page_element settings for all elements in this page in one query
     * @param array      $pageIds Array of page ID's
     * @return array|null
     */
    public function findPageAndElementSettingsInPageIds(array $pageIds): ?array
    {
        $Ids = implode(', ', $pageIds);

        $this->sql = <<<SQL
select ds.id, ds.category, ds.page_id, ds.element_id, ds.setting_key, ds.setting_value
from data_store ds
join page p on ds.page_id = p.id
where p.id in ($Ids)
union all
select ds.id, ds.category, ds.page_id, ds.element_id, ds.setting_key, ds.setting_value
from data_store ds
join page_element pe on ds.element_id = pe.id and ds.category = 'element'
join page p on pe.page_id = p.id
where p.id in ($Ids)
SQL;

        return $this->find();
    }
}

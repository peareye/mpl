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
 * Piton Message Data Mapper
 */
class MessageDataMapper extends DataMapperAbstract
{
    protected $table = 'message_data';
    protected $modifiableColumns = [
        'message_id',
        'data_key',
        'data_value',
    ];

    /**
     * Find Message Data
     *
     * @param int $messageId Message ID
     * @return array|null
     */
    public function findMessageDataByMessageId(int $messageId): ?array
    {
        $this->makeSelect();
        $this->sql .= " and `message_id` = ?";
        $this->bindValues[] = $messageId;

        return $this->find();
    }
}

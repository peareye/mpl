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
 * Piton Message Mapper
 */
class MessageMapper extends DataMapperAbstract
{
    protected $table = 'message';
    protected $modifiableColumns = [
        'name',
        'email',
        'message',
        'is_read',
        'context'
    ];

    /**
     * Find Messages in Date Order
     *
     * @param  string     $filter
     * @param  int        $limit
     * @param  int        $offset
     * @return array|null
     */
    public function findMessages(string $filter = 'read', int $limit = null, int $offset = null): ?array
    {
        $this->makeSelect(true);

        if ($filter === 'readUnRead') {
            $this->sql .= " and `is_read` in ('Y','N')";
        } elseif ($filter === 'read') {
            $this->sql .= " and `is_read` = 'Y'";
        } elseif ($filter === 'unread') {
            $this->sql .= " and `is_read` = 'N'";
        } elseif ($filter === 'archive') {
            $this->sql .= " and `is_read` = 'A'";
        }

        $this->sql .= ' order by `created_date` desc';

        if ($limit) {
            $this->sql .= ' limit ?';
            $this->bindValues[] = $limit;
        }

        if ($offset) {
            $this->sql .= ' offset ?';
            $this->bindValues[] = $offset;
        }

        return $this->find();
    }

    /**
     * Text Search
     *
     * This query searches each of these fields for having all supplied terms:
     *  - name
     *  - email
     *  - message
     *  - context
     *  - Custom message fields
     * @param  string $terms                Search terms
     * @param  int    $limit                Limit
     * @param  int    $offset               Offset
     * @return array|null
     */
    public function textSearch(string $terms, int $limit = null, int $offset = null): ?array
    {
        $this->makeSelect(true);
        $this->sql .=<<<SQL
and (
    match(`name`, `email`, `message`, `context`) against (? IN BOOLEAN MODE)
    or `id` in (select `message_id` from `message_data` where match(`data_value`) against (? IN BOOLEAN MODE))
    )
order by `created_date` desc
SQL;

        $this->bindValues[] = $terms;
        $this->bindValues[] = $terms;

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
     * Find Unread Count
     *
     * Gets the count of unread messages
     * @param  void
     * @return int
     */
    public function findUnreadCount(): int
    {
        $this->sql = "select count(*) unread from {$this->table} where `is_read` = 'N';";

        return (int) $this->findRow()->unread;
    }
}

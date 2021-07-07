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
 * Piton Media Category Map Mapper
 */
class MediaCategoryMapMapper extends DataMapperAbstract
{
    protected $table = 'media_category_map';
    protected $modifiableColumns = ['media_id', 'category_id', 'media_sort'];

    /**
     * Save Media Category Assignments
     *
     * For a media ID, save category array
     * @param int        $mediaId
     * @param array|null $categoryIds
     * @return void
     */
    public function saveMediaCategoryAssignments(int $mediaId, ?array $categoryIds): void
    {
        // If a null was passed for $categoryIds then assign an empty array so array_diff works
        // In PHP7.4 we can shorten this to $categoryIds ??= [];
        $categoryIds = $categoryIds ?? [];

        // Get saved assigned categories
        $this->makeSelect();
        $this->sql .= 'and `media_id` = ?;';
        $this->bindValues[] = $mediaId;
        $mediaCategories = $this->find();

        if ($mediaCategories) {
            $mediaCategories = array_column($mediaCategories, 'category_id');
        } else {
            $mediaCategories = [];
        }

        // Delete any previously saved category assignments not in new array
        $toDelete = array_diff($mediaCategories, $categoryIds);

        if ($toDelete) {
            $inClause = '';
            $count = 0;

            // Build statement
            $this->sql = "delete from `{$this->table}` where `media_id` = ?";
            $this->bindValues[] = $mediaId;

            foreach ($toDelete as $deleteCategoryId) {
                if ($count++ !== 0) {
                    $inClause .= ', ';
                }

                $inClause .= '?';
                $this->bindValues[] = $deleteCategoryId;
            }

            $this->sql .= " and `category_id` in ($inClause);";
            $this->execute();
        }

        // Insert new categories not previously saved
        $toInsert = array_diff($categoryIds, $mediaCategories);

        if ($toInsert) {
            $valuesClause = '';
            $count = 0;

            // Build statement
            $this->sql = "insert into `{$this->table}` (`media_id`, `category_id`) values ";

            foreach ($toInsert as $insertCategoryId) {
                if ($count++ !== 0) {
                    $valuesClause .= ', ';
                }

                $valuesClause .= '(?, ?)';
                $this->bindValues[] = $mediaId;
                $this->bindValues[] = $insertCategoryId;
            }

            $this->sql .= $valuesClause . ';';
            $this->execute();
        }
    }

    /**
     * Save Category Media Assignment Order
     *
     * For a category ID, save the order of media
     * @param int        $categoryId
     * @param array|null $mediaIds
     * @return void
     */
    public function saveCategoryMediaAssignmentOrder(int $categoryId, ?array $mediaIds): void
    {
        // If a null was passed for $mediaIds then assign an empty array so array_diff works
        // In PHP7.4 we can shorten this to $mediaIds ??= [];
        $mediaIds = $mediaIds ?? [];

        if ($mediaIds) {
            $order = 0;
            $sql = "update {$this->table} set `media_sort` = ? where `media_id` = ? and `category_id` = ?;";

            foreach ($mediaIds as $mediaId) {
                $order++;
                $this->sql = $sql;
                $this->bindValues[] = $order;
                $this->bindValues[] = $mediaId;
                $this->bindValues[] = $categoryId;

                $this->execute();
            }
        }
    }
}

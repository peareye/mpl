<?php

/**
 * PitonCMS (https://github.com/PitonCMS)
 *
 * @link      https://github.com/PitonCMS/Piton
 * @copyright Copyright (c) 2015 - 2019 Wolfgang Moritz
 * @license   https://github.com/PitonCMS/Piton/blob/master/LICENSE (MIT License)
 */

declare(strict_types=1);

namespace Piton\Models\Entities;

/**
 * Piton Page Value Object
 */
class Page extends PitonEntity
{
    /**
     * Block Elements Array
     * @var array
     */
    public $blocks = [];

    /**
     * Page Settings Array
     * @var array
     */
    public $settings = [];

    /**
     * Media Sub-Object
     * @var PitonEntity
     */
    public $media;

    /**
     * Constructor
     */
    public function __construct()
    {
        // The class properties are set by PDO::FETCH_CLASS *before* the constructor is called.
        // This checks if a media file was joined in the query, and then builds a media sub-object.
        // Media constructor sets additional calculated properties based on the image.
        if (isset($this->media_filename)) {
            // Create new Media object and assign as sub-object
            $media = new Media();
            $media->id = $this->media_id;
            $media->filename = $this->media_filename;
            $media->width = $this->media_width;
            $media->height = $this->media_height;
            $media->feature = $this->media_feature;
            $media->caption = $this->media_caption;
            $media->__construct();
            $this->media = $media;
        }

        // Remove media properties from page element object
        // unset($this->media_id);
        unset($this->media_filename);
        unset($this->media_width);
        unset($this->media_height);
        unset($this->media_feature);
        unset($this->media_caption);
    }

    /**
     * Set Block Elements
     *
     * Accepts array of element objects and converts to multidimensional array of
     * [block_key][] element objects and assigns to $this->blocks
     * @param array|null $elements
     * @return void
     */
    public function setBlockElements(?array $elements): void
    {
        // Nothing to do with an empty array but stop
        if (empty($elements)) {
            return;
        }

        // Go through array and assign by block key as index
        array_walk($elements, function ($el) {
            $this->blocks[$el->block_key][] = $el;
        });
    }

    /**
     * Set Page Settings
     *
     * Filters array of data_store settings on page category and creates key:value array on $this->settings
     * @param array|null
     * @return void
     */
    public function setPageSettings(?array $settings): void
    {
        if (empty($settings)) {
            return;
        }

        array_walk($settings, function ($setting) {
            if ($setting->category === 'page' && $this->id === $setting->page_id) {
                $this->settings[$setting->setting_key] = $setting->setting_value;
            }
        });
    }

    /**
     * Get Published Status
     *
     * Returns draft|pending|published depending on published date compared to today
     * @param void
     * @return string
     */
    public function getPublishedStatus(): ?string
    {
        $today = date('Y-m-d');

        if (empty($this->published_date)) {
            return 'draft';
        } elseif ($this->published_date > $today) {
            return 'pending';
        } elseif ($this->published_date <= $today) {
            return 'published';
        }

        return null;
    }
}

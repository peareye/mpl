<?php

/**
 * PitonCMS (https://github.com/PitonCMS)
 *
 * @link      https://github.com/PitonCMS/Piton
 * @copyright Copyright (c) 2015 - 2020 Wolfgang Moritz
 * @license   https://github.com/PitonCMS/Piton/blob/master/LICENSE (MIT License)
 */

declare(strict_types=1);

namespace Piton\Library\Handlers;

use function Tinify\setKey as setTinifyKey;
use function Tinify\validate as validateTinifyKey;
use function Tinify\fromFile as setTinifySource;
use Tinify\Source as TinifySource;
use Tinify\AccountException as TinifyAccountException;
use Tinify\ClientException as TinifyClientException;
use Tinify\ServerException as TinifyServerException;
use Tinify\Exception as TinifyException;
use Exception;
use Closure;

/**
 * Piton Media Handler
 *
 * Manages media manipulations and optimizations
 * Requires TinyJPG API
 */
class Media
{
    /**
     * Provided File Name
     * @var string
     */
    protected $filename;

    /**
     * Absolute File Path
     * @var string
     */
    protected $mediaPath;

    /**
     * Media File Width
     * @var int
     */
    protected $width;

    /**
     * Media File Height
     * @var int
     */
    protected $height;

    /**
     * Orientation
     * @var string
     */
    protected $orientation;

    /**
     * Tinify Source
     * @var \Tinify\Source
     */
    protected $tinifySource;

    /**
     * Media File Path Closure
     * @var closure
     */
    protected $mediaPathClosure;

    /**
     * Media Sizes Closure
     * @var closure
     */
    protected $mediaSizesClosure;

    /**
     * Error Messages
     * @var array
     */
    protected $error = [];

    /**
     * Constructor
     *
     * @param  closure $mediaPath    Function to derive media file Path
     * @param  closure $mediaSizes   Function to derive media size suffix
     * @param  string  $tinifyApiKey TinyJPG API Key
     * @return void
     * @throws Exception
     */
    public function __construct(closure $mediaPath, closure $mediaSizes, string $tinifyApiKey)
    {
        // Make sure there is a valid key
        try {
            setTinifyKey($tinifyApiKey);
            validateTinifyKey();
        } catch (TinifyException $e) {
            $this->error[] = 'Invalid TinyJPG key submitted: ' . print_r($e->getMessage(), true);
        }

        $this->mediaPathClosure = $mediaPath;
        $this->mediaSizesClosure = $mediaSizes;
    }

    /**
     * Set Media Source
     *
     * @param string $soureMedia Source media filename
     * @return bool
     */
    public function setSource(string $sourceMedia): bool
    {
        // Get path to file and assign as property
        $this->filename = $sourceMedia;
        $this->mediaPath = ROOT_DIR . 'public' . ($this->mediaPathClosure)($sourceMedia);
        $absoluteSourceMedia = $this->mediaPath . $sourceMedia;

        // Ensure source file exists
        if (!file_exists($absoluteSourceMedia)) {
            $this->error[] = "Source file not found $absoluteSourceMedia";
            return false;
        }

        // Get dimensions and set orientation
        list($this->width, $this->height) = getimagesize($absoluteSourceMedia);

        if (isset($this->height) && $this->height > 0) {
            $aspectRatio = round($this->width / $this->height, 2);
            $this->orientation = ($aspectRatio > 1) ? 'landscape' : 'portrait';
        }

        // Set and validate source
        $this->tinifySource = setTinifySource($absoluteSourceMedia);
        if (!$this->tinifySource instanceof TinifySource) {
            $this->error[] = "Unable to set Tinify source file $sourceMedia.";
            return false;
        }

        return true;
    }

    /**
     * Make Optimized X-Large Media File
     *
     * Uses Tinify optimization
     * @param void
     * @return bool
     */
    public function makeXLarge(): bool
    {
        return $this->resizeMedia($this->getAbsoluteFilenameBySize('xlarge'));
    }

    /**
     * Make Optimized Large Media File
     *
     * Uses Tinify optimization
     * @param void
     * @return bool
     */
    public function makeLarge(): bool
    {
        // If square, keep aspect and constrain to 2000 x 2000
        if ($this->width == $this->height) {
            $resize = [
                'method' => 'fit',
                'width' => 2000,
                'height' => 2000
            ];
        } elseif ($this->orientation === 'landscape') {
            $resize = [
                'method' => 'fit',
                'width' => 2000,
                'height' => 1500
            ];
        } else {
            $resize = [
                'method' => 'fit',
                'width' => 1500,
                'height' => 2000
            ];
        }

        return $this->resizeMedia($this->getAbsoluteFilenameBySize('large'), $resize);
    }

    /**
     * Make Optimized Small Media File
     *
     * Uses Tinify optimization
     * @param void
     * @return bool
     */
    public function makeSmall(): bool
    {
        // If square, keep aspect and constrain to 1024 x 1024
        if ($this->width == $this->height) {
            $resize = [
                'method' => 'fit',
                'width' => 1024,
                'height' => 1024
            ];
        } elseif ($this->orientation === 'landscape') {
            $resize = [
                'method' => 'fit',
                'width' => 1024,
                'height' => 768
            ];
        } else {
            $resize = [
                'method' => 'fit',
                'width' => 768,
                'height' => 1024
            ];
        }

        return $this->resizeMedia($this->getAbsoluteFilenameBySize('small'), $resize);
    }

    /**
     * Make Optimized Thumbnail Media File
     *
     * Uses Tinify optimization
     * @param void
     * @return bool
     */
    public function makeThumb(): bool
    {
        $resize = [
            'method' => 'thumb',
            'width' => 350,
            'height' => 263
        ];

        return $this->resizeMedia($this->getAbsoluteFilenameBySize('thumb'), $resize);
    }

    /**
     * Tinify Resize
     *
     * Wraps Tinify methods in try catch block with messages
     * @param string $newFilename
     * @param array $resize
     * @return bool
     */
    protected function resizeMedia(string $newFilename, array $resize = null): bool
    {
        try {
            if ($resize) {
                // New dimensions
                $this->tinifySource->resize($resize)->toFile($newFilename);
            } else {
                // Keep dimenions but optimize
                $this->tinifySource->toFile($newFilename);
            }

            // Success
            return true;
        } catch (TinifyAccountException $e) {
            // Verify your API key and account limit.
            $this->error[] = "There is an error with the Tinify account:  {$e->getMessage()}";
            return false;
        } catch (TinifyClientException $e) {
            // Check your source image and request options.
            $this->error[] = "There is an with the original media file:  {$e->getMessage()}";
            return false;
        } catch (TinifyServerException $e) {
            // Temporary issue with the Tinify API.
            $this->error[] = "There is an error with the Tinify server:  {$e->getMessage()}";
            return false;
        } catch (TinifyConnectionException $e) {
            // A network connection error occurred.
            $this->error[] = "There is an error with the Tinify connection:  {$e->getMessage()}";
            return false;
        } catch (Exception $e) {
            // Something else went wrong, unrelated to the Tinify API.
            $this->error[] = "Whoops! Something went wrong trying to optimize media:  {$e->getMessage()}";
            return false;
        }
    }

    /**
     * Get Absolute Filename by Size
     *
     * Returns absolute filename with size and extension
     * @param string $size
     * @return string
     */
    protected function getAbsoluteFilenameBySize(string $size = ''): string
    {
        return $this->mediaPath . ($this->mediaSizesClosure)($this->filename, $size);
    }

    /**
     * Get Error Messages
     *
     * @param void
     * @return array|null
     */
    public function getErrorMessages(): ?array
    {
        return empty($this->error) ? null : $this->error;
    }
}

<?php

/**
 * PitonCMS (https://github.com/PitonCMS)
 *
 * @link      https://github.com/PitonCMS/Piton
 * @copyright Copyright (c) 2015 - 2019 Wolfgang Moritz
 * @license   https://github.com/PitonCMS/Piton/blob/master/LICENSE (MIT License)
 */

declare(strict_types=1);

namespace Piton\Library\Handlers;

use Exception;
use Closure;

/**
 * Piton File Upload Handler
 *
 * Manages file uploads
 */
class FileUpload
{
    /**
     * Array of Slim\Http\UploadedFile
     * @var array
     */
    protected $uploadedFiles;

    /**
     * Public Root Directory
     * @var string
     */
    protected $publicRoot = ROOT_DIR . 'public';

    /**
     * New File Name
     * @var string
     */
    protected $filename;

    /**
     * Extension
     * @var string
     */
    protected $extension;

    /**
     * Media File Width
     * @var int
     */
    public $width;

    /**
     * Media File Height
     * @var int
     */
    public $height;

    /**
     * Media File URI Closure
     * @var closure
     */
    protected $mediaPathClosure;

    /**
     * Media New Filename Generator
     * @var closure
     */
    protected $filenameGenerator;

    /**
     * PHP Upload Error Code
     * @var int
     */
    protected $error = UPLOAD_ERR_OK;

    /**
     * Mime Type
     * @var string
     */
    public $mimeType;

    /**
     * Image Mime Types
     * @var array
     */
    public $imageMimeTypes = [
        'image/png',
        'image/jpeg',
        'image/gif',
        'image/bmp',
        'image/vnd.microsoft.icon',
        'image/tiff',
        'image/svg+xml',
    ];

    /**
     * Constructor
     *
     * @param  array   $uploadedfiles        Array of Slim\Http\UploadedFile objects
     * @param  closure $mediaPathClosure     Function to derive file URI
     * @param  closure $filenameGenerator Function to generate new filenames
     * @return void
     */
    public function __construct(array $uploadedFiles, closure $mediaPathClosure, closure $filenameGenerator)
    {
        $this->uploadedFiles = $uploadedFiles;
        $this->mediaPathClosure = $mediaPathClosure;
        $this->filenameGenerator = $filenameGenerator;
    }

    /**
     * Upload File
     *
     * Upload file given input name
     * @param  string  $fileKey Array key for file upload
     * @return bool
     */
    public function upload(string $fileKey): bool
    {
        if (!isset($this->uploadedFiles[$fileKey])) {
            throw new Exception('PitonCMS: File upload key does not exist.');
        }

        if ($this->uploadedFiles[$fileKey]->getError() === UPLOAD_ERR_OK) {
            // Get original file name and save extension
            $this->extension = mb_strtolower(pathinfo(
                $this->uploadedFiles[$fileKey]->getClientFilename(),
                PATHINFO_EXTENSION
            ));
            $this->mimeType = $this->uploadedFiles[$fileKey]->getClientMediaType();

            $this->makeDirectoryPath();
            $this->uploadedFiles[$fileKey]->moveTo($this->getFilename(true));

            // Set file width and height attributes if an image type
            if (in_array($this->mimeType, $this->imageMimeTypes)) {
                list($this->width, $this->height) = getimagesize($this->getFilename(true));
            }

            return true;
        }

        // Otherwise save error code
        $this->error = $this->uploadedFiles[$fileKey]->getError();

        return false;
    }

    /**
     * Get New Basename Name
     *
     * Returns filename plus extension
     * @param  bool  $absolute Get absolute path
     * @return string
     */
    public function getFilename(bool $absolute = false): string
    {
        if ($absolute) {
            return $this->publicRoot . ($this->mediaPathClosure)($this->filename) . "{$this->filename}.{$this->extension}";
        } else {
            return "{$this->filename}.{$this->extension}";
        }
    }

    /**
     * Make Directory Path
     *
     * Creates the directory path
     * @param void
     * @return void
     */
    protected function makeDirectoryPath(): void
    {
        // Create new file name and directory and ensure it is unique
        $dirDoesNotExist = true;
        do {
            $this->generateFilename();
            $filePath = $this->publicRoot . ($this->mediaPathClosure)($this->filename);

            // Create the path if the directory does not exist
            if (!is_dir($filePath)) {
                try {
                    mkdir($filePath, 0775, true);
                    $dirDoesNotExist = false;
                } catch (Exception $e) {
                    throw new Exception('PitonCMS: Failed to create file upload directory. ' . $e->getMessage());
                }
            }
        } while ($dirDoesNotExist);

        return;
    }

    /**
     * Generate Filename
     *
     * Generates new random filename and assigns to filename property
     * @param  void
     * @return void
     */
    protected function generateFilename(): void
    {
        $this->filename = ($this->filenameGenerator)();
    }

    /**
     * Clear Upload
     *
     * Resets FileUpload for the next file
     * @param string $fileKey Array key for file upload to remove
     * @return void
     */
    public function clear(string $fileKey): void
    {
        unset($this->uploadedFiles[$fileKey]);
        $this->filename = null;
        $this->extension = null;
        $this->width = null;
        $this->height = null;
        $this->error = null;
        $this->mimeType = null;
    }

    /**
     * Get Upload Error Message
     *
     * Converts PHP UPLOAD_ERR_* codes to plain text
     * @param  void
     * @return string
     */
    public function getErrorMessage(): string
    {
        switch ($this->error) {
            case UPLOAD_ERR_INI_SIZE:
                $message = "The uploaded file exceeds the upload_max_filesize directive in php.ini";
                break;
            case UPLOAD_ERR_FORM_SIZE:
                $message = "The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form";
                break;
            case UPLOAD_ERR_PARTIAL:
                $message = "The uploaded file was only partially uploaded";
                break;
            case UPLOAD_ERR_NO_FILE:
                $message = "No file was uploaded";
                break;
            case UPLOAD_ERR_NO_TMP_DIR:
                $message = "Missing a temporary folder";
                break;
            case UPLOAD_ERR_CANT_WRITE:
                $message = "Failed to write file to disk";
                break;
            case UPLOAD_ERR_EXTENSION:
                $message = "File upload stopped by extension";
                break;

            default:
                $message = "Unknown upload error";
        }

        return $message;
    }
}

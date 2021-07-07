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

use RecursiveCallbackFilterIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use JsonSchema\Constraints\Constraint;
use Throwable;

/**
 * Piton JSON Definition File Loader and Validator
 */
class Definition
{
    /**
     * JSON Validator
     * @var object
     */
    protected $validator;

    /**
     * Definition File Paths
     * @var array
     */
    protected $definition = [
        'elements' => ROOT_DIR . 'structure/templates/elements/',
        'pages' => ROOT_DIR . 'structure/templates/pages/',
        'navigation' => ROOT_DIR . 'structure/definitions/navigation.json',
        'siteSettings' => ROOT_DIR . 'structure/definitions/siteSettings.json',
        'seededSettings' => ROOT_DIR . 'vendor/pitoncms/engine/config/settings.json',
        'contact' => ROOT_DIR . 'structure/definitions/contactInputs.json',
    ];

    /**
     * Validation File Paths
     * @var array
     */
    protected $validation = [
        'element' => ROOT_DIR . 'vendor/pitoncms/engine/jsonSchemas/definitions/elementSchema.json',
        'page' => ROOT_DIR . 'vendor/pitoncms/engine/jsonSchemas/definitions/pageSchema.json',
        'navigation' => ROOT_DIR . 'vendor/pitoncms/engine/jsonSchemas/definitions/navigationSchema.json',
        'settings' => ROOT_DIR . 'vendor/pitoncms/engine/jsonSchemas/definitions/settingsSchema.json',
        'contact' => ROOT_DIR . 'vendor/pitoncms/engine/jsonSchemas/definitions/contactSchema.json',
    ];

    /**
     * Validation Errors
     * @var array
     */
    protected $errors = [];

    /**
     * Constructor
     *
     * @param  object JSON Validator
     * @return void
     */
    public function __construct(object $validator)
    {
        $this->validator = $validator;
    }

    /**
     * Get Custom Site Settings
     *
     * Get site settings
     * @param  void
     * @return mixed
     */
    public function getSiteSettings()
    {
        return $this->decodeValidJson($this->definition['siteSettings'], $this->validation['settings']);
    }

    /**
     * Get Seeded Site Settings
     *
     * Get site settings
     * @param  void
     * @return mixed
     */
    public function getSeededSiteSettings()
    {
        return $this->decodeValidJson($this->definition['seededSettings'], $this->validation['settings']);
    }

    /**
     * Get Navigation
     *
     * @param  void
     * @return mixed
     */
    public function getNavigation()
    {
        return $this->decodeValidJson($this->definition['navigation'], $this->validation['navigation']);
    }

    /**
     * Get Page
     *
     * Get page definition
     * @param  string $pageDefinition
     * @return mixed
     */
    public function getPage(string $pageDefinition)
    {
        return $this->decodeValidJson($this->definition['pages'] . $pageDefinition, $this->validation['page']);
    }

    /**
     * Get All Pages
     *
     * Get page definitions
     * @param  void
     * @return mixed
     */
    public function getPages()
    {
        return $this->getPageDefinitions('page');
    }

    /**
     * Get All Collections
     *
     * Get collection definitions
     * @param  void
     * @return mixed
     */
    public function getCollections()
    {
        return $this->getPageDefinitions('collection');
    }

    /**
     * Get Single Element
     *
     * Get element definition
     * @param  string $elementDefinition
     * @return mixed
     */
    public function getElement(string $elementDefinition)
    {
        return $this->decodeValidJson($this->definition['elements'] . $elementDefinition, $this->validation['element']);
    }

    /**
     * Get Contact Inputs
     *
     * Custom contact field validation
     * @param void
     */
    public function getContactInputs()
    {
        return $this->decodeValidJson($this->definition['contact'], $this->validation['contact']);
    }

    /**
     * Get All Elements
     *
     * Get all element JSON definitions
     * @param  void
     * @return array
     */
    public function getElements(): array
    {
        // Get all Element JSON files in directory
        $elements = [];
        foreach ($this->getDirectoryDefinitionFiles($this->definition['elements']) as $file) {
            // Get definition file, but do not validate as we are only returning a list of available templates
            if (null === $definition = $this->decodeValidJson($this->definition['elements'] . $file)) {
                $this->errors[] = "PitonCMS: Unable to read element definition file: $file";
                break;
            }

            // Remove .json extension from filename but keep relative path
            $elements[] = [
                'filename' => mb_substr($file, 0, mb_stripos($file, '.json')),
                'name' => $definition->elementName,
                'description' => $definition->elementDescription ?? null,
            ];
        }

        return $elements;
    }

    /**
     * Get Page or Collection Page Definitions
     *
     * Get available templates from JSON files. If no param is provided, then all templates are returned
     * @param  string $templateType page|collection
     * @return array                Array of page templates
     */
    protected function getPageDefinitions(string $templateType): array
    {
        $templates = [];
        foreach ($this->getDirectoryDefinitionFiles($this->definition['pages']) as $file) {
            // Get definition file, but do not validate as we are only returning a list of available templates
            if (null === $definition = $this->decodeValidJson($this->definition['pages'] . $file)) {
                $this->errors[] = "PitonCMS: Unable to read page definition file: $file";
                break;
            }

            // Filter out unneeded templates
            if (!empty($definition->templateType) && $definition->templateType !== $templateType) {
                continue;
            }

            // Remove .json extension from filename but keep relative path
            $templates[] = [
                'filename' => mb_substr($file, 0, mb_stripos($file, '.json')),
                'name' => $definition->templateName,
                'description' => $definition->templateDescription
            ];
        }

        return $templates;
    }

    /**
     * Get Directory Definition Files
     *
     * Recursively scans a given template directory.
     * Returns an array of JSON definition files with path relative to $dirPath
     * @param  string $dirPath Path to directory to scan
     * @return array
     */
    protected function getDirectoryDefinitionFiles($dirPath): array
    {
        $files = [];
        $dirPathLength = mb_strlen($dirPath);
        $directories = new RecursiveDirectoryIterator($dirPath, RecursiveDirectoryIterator::SKIP_DOTS);
        $filter = new RecursiveCallbackFilterIterator($directories, function ($current, $key, $iterator) {
            // Ensures we scan recursively
            if ($iterator->hasChildren()) {
                return true;
            }

            if ($current->getExtension() === 'json') {
                return true;
            }

            return false;
        });

        foreach (new RecursiveIteratorIterator($filter) as $file) {
            // Return path relative to $dirPath
            $files[] = mb_substr($file->getPathname(), $dirPathLength);
        }

        return $files;
    }

    /**
     * Decode and Validate JSON Definition
     *
     * Validation is optional, if validation $schema is provided
     * Validation errors available from getErrorMessages()
     * @param string $json   Path to page JSON file to decode
     * @param string $schema Path to validation JSON Schema file
     * @return mixed|null
     */
    protected function decodeValidJson(string $json, string $schema = null)
    {
        // Get and decode JSON to be validated
        if (!file_exists($json) || false === $contents = file_get_contents($json)) {
            $this->errors[] = "Unable to read file: $json";
            return null;
        }

        try {
            $jsonDecodedInput = json_decode($contents, false, JSON_THROW_ON_ERROR);

            if ($schema) {
                // Validate JSON
                $this->validator->validate($jsonDecodedInput, (object)['$ref' => 'file://' . $schema], Constraint::CHECK_MODE_APPLY_DEFAULTS);

                if (!$this->validator->isValid()) {
                    // If not valid, record error messages and return null
                    foreach ($this->validator->getErrors() as $error) {
                        $this->errors[] =  sprintf("[%s] %s", $error['property'], $error['message']);
                    }

                    $jsonDecodedInput = null;
                }
            }
        } catch (Throwable $th) {
            $this->errors[] = "Error: {$th->getMessage()}";
            return null;
        }

        $this->validator->reset();
        return $jsonDecodedInput;
    }

    /**
     * Get Errors
     *
     * Returns array of error messages and resets error array
     * @param void
     * @return array
     */
    public function getErrorMessages(): array
    {
        $errors = $this->errors;
        $this->errors = [];

        return $errors;
    }
}

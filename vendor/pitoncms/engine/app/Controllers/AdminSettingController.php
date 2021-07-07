<?php

/**
 * PitonCMS (https://github.com/PitonCMS)
 *
 * @link      https://github.com/PitonCMS/Piton
 * @copyright Copyright (c) 2015 - 2020 Wolfgang Moritz
 * @license   https://github.com/PitonCMS/Piton/blob/master/LICENSE (MIT License)
 */

declare(strict_types=1);

namespace Piton\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Exception;

/**
 * Admin Setting Controller
 *
 * Manage site level application settings
 */
class AdminSettingController extends AdminBaseController
{
    /**
     * Show Settings Landing Page
     * @param  void
     * @return Response
     */
    public function showSettings(): Response
    {
        return $this->render('settings/settings.html');
    }

    /**
     * Edit Site Settings
     *
     * List site configuration settings to bulk edit
     * @param array $args
     * @return Response
     */
    public function editSettings($args): Response
    {
        // Get dependencies
        $dataStoreMapper = ($this->container->dataMapper)('DataStoreMapper');
        $definition = $this->container->jsonDefinitionHandler;

        // Validate we have one of the defined categories
        if (!in_array($args['category'], ['site', 'social', 'contact'])) {
            throw new Exception("PitonCMS: Unexpected value for category.");
        }

        // Get saved settings from database
        $savedSettings = $dataStoreMapper->findSiteSettings($args['category']) ?? [];

        // Get seeded PitonCMS settings definition
        if (null === $seededSettings = $definition->getSeededSiteSettings()) {
            throw new Exception('PitonCMS: Invalid seeded config/settings.json: ' . implode(', ', $definition->getErrorMessages()));
        }

        // Get custom settings definition
        if (null === $customSettings = $definition->getSiteSettings()) {
            $this->setAlert('danger', 'Custom Settings Definition Error', $definition->getErrorMessages());
        } else {
            // Merge saved settings with custom settings
            $data['settings'] = $this->mergeSettings(
                $savedSettings,
                array_merge($seededSettings->settings, $customSettings->settings),
                $args['category']
            );
        }

        // Send category name to page to help with redirects
        $data['category'] = $args['category'];

        return $this->render('settings/editSettings.html', $data);
    }

    /**
     * Save Settings
     *
     * Save site configuration settings
     * @param void
     * @return Response
     */
    public function saveSettings(): Response
    {
        // Get dependencies
        $dataStoreMapper = ($this->container->dataMapper)('DataStoreMapper');

        // Get setting data POST array
        $settings = $this->request->getParsedBodyParam('setting');
        $category = $this->request->getParsedBodyParam('category');

        // Save each setting
        foreach ($settings as $row) {
            $setting = $dataStoreMapper->make();
            $setting->id = (int) $row['id'];

            // Check for a setting delete flag
            if (isset($row['delete'])) {
                $dataStoreMapper->delete($setting);
                continue;
            }

            $setting->category = $row['category'];
            $setting->setting_key = $row['setting_key'];
            $setting->setting_value = $row['setting_value'];
            $dataStoreMapper->save($setting);
        }

        // Redirect back to list of settings
        return $this->redirect('adminSettingEdit', ['category' => $category]);
    }
}

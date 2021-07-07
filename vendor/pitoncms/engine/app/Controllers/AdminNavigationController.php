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

use Slim\Http\Response;
use Exception;
use Throwable;

/**
 * Piton Navigation Controller
 *
 * Manage page navigators
 */
class AdminNavigationController extends AdminBaseController
{
    /**
     * Show Navigators
     *
     * @param  void
     * @return Response
     */
    public function showNavigators(): Response
    {
        // Get dependencies
        $navigators = ($this->container->jsonDefinitionHandler)->getNavigation();
        $navigators = $navigators->navigators ?? null;

        return $this->render('navigation/navigation.html', ['navigators' => $navigators]);
    }

    /**
     * Edit Navigator
     *
     * @param  array $args
     * @return Response
     */
    public function editNavigator($args): Response
    {
        // Get dependencies
        $navMapper = ($this->container->dataMapper)('NavigationMapper');
        $pageMapper = ($this->container->dataMapper)('PageMapper');
        $collectioMapper = ($this->container->dataMapper)('CollectionMapper');

        if (null === $navivation = ($this->container->jsonDefinitionHandler)->getNavigation()) {
            throw new Exception("Invalid navigator definition");
        }

        $navs = $navivation->navigators;
        $navs = array_combine(array_column($navs, 'key'), $navs);

        $data['pages'] = $pageMapper->findContent('all', 'pages');
        $data['collections'] = $collectioMapper->find();
        $data['navDefinition'] = $navs[$args['navigator']];

        $navList = $navMapper->findNavigationStructure($args['navigator']);
        $data['navigation'] = $navMapper->buildNavigation($navList, null, false);

        return $this->render('navigation/navigationEdit.html', $data);
    }

    /**
     * Save Navigation
     *
     * @param void
     * @return Resonse
     */
    public function saveNavigation(): Response
    {
        // Get dependencies
        $navigationMapper = ($this->container->dataMapper)('NavigationMapper');

        // Get POST data
        $navigation = $this->request->getParsedBodyParam('nav');
        $navigator = $this->request->getParsedBodyParam('navigator');

        // Save each nav item. Array elements are updated by reference so new nav items get an ID assigned after insert to use parent ID's
        $index = 0;
        foreach ($navigation as &$navItem) {
            $index++;
            $nav = $navigationMapper->make();
            $nav->id = (is_numeric($navItem['navId'])) ? (int) $navItem['navId'] : null;
            $nav->navigator = $navigator;

            // Page ID 0 is for placeholder nav links, which are not joined to page table
            $nav->page_id = (is_numeric($navItem['pageId'])) ? (int) $navItem['pageId'] : null;
            $nav->parent_id = (is_numeric($navItem['parentId'])) ? (int) $navItem['parentId'] : null;

            // Set parent nav ID
            if (is_numeric($navItem['parentId'])) {
                // If a numeric parentId was set coerce and assign
                $nav->parent_id = (int) $navItem['parentId'];
            } elseif (!empty($navItem['parentId'])) {
                // If parent ID is not numeric (new pages use a '0x' array key), then get parent's ID from post array
                $nav->parent_id = (int) $navigation[$navItem['parentId']]['navId'];
            } else {
                // Otherwise set to null
                $nav->parent_id = null;
            }

            // Yes, this happened. A nav element cannot be a child of itself.
            if ($nav->parent_id !== null && $nav->page_id === $nav->parent_id) {
                throw new Exception("PitonCMS: A navigation element cannot be a child of itself");
            }

            $nav->sort = $index;
            $nav->title = trim($navItem['navTitle']) ?? null;
            $nav->url = $navItem['url'] ?? null;
            $nav->collection_id = $navItem['collectionId'] ?? null;

            // Save and assign inserted nav ID for child rows
            $savedNav = $navigationMapper->save($nav);
            $navItem['navId'] = $savedNav->id;
        }

        return $this->redirect('adminNavigationEdit', ['navigator' => $navigator]);
    }

    /**
     * Delete Navigation
     *
     * XHR Request
     * @param void
     * @return Response
     * @uses POST
     */
    public function deleteNavigator()
    {
        // Wrap in try catch to stop processing at any point and let the xhrResponse takeover
        try {
            $navigationMapper = ($this->container->dataMapper)('NavigationMapper');
            $navIds = $this->request->getParsedBodyParam("navIds");
            $navigationIds = json_decode($navIds);
            $status = "success";
            $text = "";

            // Go through array and delete
            if (is_array($navigationIds)) {
                foreach ($navigationIds as $nav) {
                    // Check that we have an int or skip
                    if (!is_int($nav)) {
                        $text .= "Unable to delete: $nav\n";
                        continue;
                    }
                    $navItem = $navigationMapper->make();
                    $navItem->id = (int) $nav;
                    $navigationMapper->delete($navItem);
                }
            }
        } catch (Throwable $th) {
            $status = "error";
            $text = "Exception deleting navigator: ". $th->getMessage();
        }

        return $this->xhrResponse($status, $text);
    }
}

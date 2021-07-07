<?php

/**
 * PitonCMS (https://github.com/PitonCMS)
 *
 * @link      https://github.com/PitonCMS/Piton
 * @copyright Copyright 2018 Wolfgang Moritz
 * @license   https://github.com/PitonCMS/Piton/blob/master/LICENSE (MIT License)
 */

declare(strict_types=1);

namespace Piton\Controllers;

use Piton\Models\Entities\PitonEntity;
use Slim\Http\Response;
use Exception;
use DateTime;
use Throwable;

/**
 * Piton Admin Page and Collection Controller
 */
class AdminPageController extends AdminBaseController
{
    /**
     * Show Pages and Collection Pages
     *
     * Show all pages and collection pages
     * @param void
     * @return Response
     */
    public function showPages(): Response
    {
        $data['pages'] = $this->loadPages();
        return $this->render('pages/pages.html', $data);
    }

    /**
     * Get Pages
     *
     * XHR Request
     * Returns filtered page list
     * @param void
     * @return Response
     */
    public function getPages(): Response
    {
        try {
            $pages = $this->loadPages();

            // Make string template
            $template =<<<HTML
            {% import '@admin/pages/_pageMacros.html' as pageMacro %}
            {% for p in pages %}
                {{ pageMacro.pageListItem(p) }}
            {% endfor %}

            {{ pagination() }}
HTML;

            $status = "success";
            $text = $this->container->view->fetchFromString($template, ['pages' => $pages]);
        } catch (Throwable $th) {
            $status = "error";
            $text = "Exception getting pages: ". $th->getMessage();
        }

        return $this->xhrResponse($status, $text);
    }

    /**
     * Load Pages
     *
     * Get all pages using query string parameters
     * @param void
     * @param array
     * @uses GET params
     */
    protected function loadPages(): array
    {
        // Get dependencies
        $pageMapper = ($this->container->dataMapper)('PageMapper');
        $pagination = $this->getPagination();
        $pagination->setPagePath($this->container->router->pathFor('adminPage'));
        $definition = $this->container->jsonDefinitionHandler;
        $pageTemplates = array_merge($definition->getPages(), $definition->getCollections());

        // Get filters or search if requested
        $status = htmlspecialchars($this->request->getQueryParam('status', 'all'));
        $type = htmlspecialchars($this->request->getQueryParam('type', 'all'));
        $terms = htmlspecialchars($this->request->getQueryParam('terms', ''));

        // Get data
        if (!empty($terms)) {
            // This was a search request
            $pages = $pageMapper->searchContent($terms, $pagination->getLimit(), $pagination->getOffset()) ?? [];
        } else {
            // Otherwise return filtered list
            $pages = $pageMapper->findContent($status, $type, $pagination->getLimit(), $pagination->getOffset()) ?? [];
        }

        // Set pagination found rows
        $pagination->setTotalResultsFound($pageMapper->foundRows() ?? 0);

        // Use filename as key for quick look up when adding template name into result set
        $pageTemplates = array_combine(array_column($pageTemplates, 'filename'), $pageTemplates);

        // Set template name in result set
        foreach ($pages as &$page) {
            $page->template_name = $pageTemplates[$page->template]['name'] ?? null;
        }

        return $pages;
    }

    /**
     * Edit Page or Collection Page
     *
     * Create new page, or edit existing page
     * @param array $args
     * @return Response
     */
    public function editPage($args): Response
    {
        // Determine whether to edit an existing page or create a new page
        if (isset($args['id']) && is_numeric($args['id'])) {
            // Edit saved page
            return $this->editLoadSavedPage((int) $args['id']);
        } else {
            // Create new page
            return $this->editLoadNewPage();
        }
    }

    /**
     * Edit Load Saved Page
     *
     * @param int $pageId Page ID
     * @return Response
     */
    protected function editLoadSavedPage(int $pageId): Response
    {
        // Get dependencies
        $pageMapper = ($this->container->dataMapper)('PageMapper');
        $pageElementMapper = ($this->container->dataMapper)('PageElementMapper');
        $dataStoreMapper = ($this->container->dataMapper)('DataStoreMapper');
        $definition = $this->container->jsonDefinitionHandler;

        // Load existing page from database
        $page = $pageMapper->findById($pageId);

        // Return 404 if not found
        if (empty($page)) {
            return $this->notFound();
        }

        // Get page definition
        if (null === $page->definition = $definition->getPage($page->template . '.json')) {
            $this->setAlert('danger', 'Page Definition Error ' . $page->template . '.json', $definition->getErrorMessages());
        }

        // Get and load page settings
        $pageSettings = $dataStoreMapper->findPageSettings($page->id) ?? [];

        if (isset($page->definition->settings)) {
            $page->settings = $this->mergeSettings($pageSettings, $page->definition->settings);
        } else {
            // This case is for when page settings were saved to the DB but then deleted from the JSON definition
            $page->settings = $pageSettings;
        }

        // Get saved elements and element settings
        $elements = $pageElementMapper->findElementsByPageId($page->id) ?? [];

        foreach ($elements as &$el) {
            // Get element definition
            if (null === $el->definition = $definition->getElement($el->template . '.json')) {
                $this->setAlert('danger', 'Element Definition Error ' . $el->template . '.json', $definition->getErrorMessages());
            }

            // Get and load page element settings
            $elementSettings = $dataStoreMapper->findPageElementSettings($el->id) ?? [];

            if (isset($el->definition->settings)) {
                $el->settings = $this->mergeSettings($elementSettings, $el->definition->settings);
            } else {
                // This case is for when page element settings were saved to the DB but then deleted from the JSON definition
                $el->settings = $elementSettings;
            }
        }

        $page->setBlockElements($elements);

        return $this->render('pages/pageEdit.html', $page);
    }

    /**
     * Edit Load New Page
     */
    protected function editLoadNewPage()
    {
        // Get dependencies
        $pageMapper = ($this->container->dataMapper)('PageMapper');
        $collectionMapper = ($this->container->dataMapper)('CollectionMapper');
        $definition = $this->container->jsonDefinitionHandler;

        // Create new page object
        $page = $pageMapper->make();

        // Get options
        $templateParam = $this->request->getQueryParam('definition');
        $collectionId = $this->request->getQueryParam('collectionId');

        // Get requested page template from query string
        if ($templateParam && empty($collectionId)) {
            $templateParam = htmlspecialchars($templateParam);

            // Validate that we have a proper definition file name
            if (null === $templateParam || 1 !== preg_match('/^[a-zA-Z0-9\/]+$/', $templateParam)) {
                // $this->setAlert('danger', 'Invalid Template Name', 'The template name must only include a-z, A-Z, 0-9, and /');
                throw new Exception("PitonCMS: Invalid query parameter for 'definition': $templateParam");
            }

            $page->template = $templateParam;
        }

        // OR - "collectionId" and "definition" should be exclusive options to create a page

        // Get collection details for collection pages. (Collection details for existing pages are returned with the findById() query above.)

        if (is_numeric($collectionId) && empty($templateParam)) {
            $collection = $collectionMapper->findById((int) $collectionId);
            $page->collection_id = $collectionId;
            $page->collection_title = $collection->collection_title;
            $page->collection_slug = $collection->collection_slug;
            $page->template = $collection->collection_definition;
        }

        // Get page definition
        if (null === $page->definition = $definition->getPage($page->template . '.json')) {
            $this->setAlert('danger', 'Page JSON Definition Error ' . $page->template . '.json', $definition->getErrorMessages());
        }

        // Populate page settings
        if (isset($page->definition->settings)) {
            $page->settings = $this->mergeSettings([], $page->definition->settings);
        }

        return $this->render('pages/pageEdit.html', $page);
    }

    /**
     * Save Page
     *
     * Create or update page wrapper
     */
    public function savePage()
    {
        // Save settings and elements
        $pageEntity = $this->savePageHeader();
        $this->savePageSettings($pageEntity->id);
        $this->savePageElements($pageEntity->id);

        return $this->redirect('adminPageEdit', ['id' => $pageEntity->id]);
    }

    /**
     * Save Page
     *
     * Create new page, or update existing page
     * @param void
     * @return PitonEntity
     * @uses POST
     */
    public function savePageHeader(): ?PitonEntity
    {
        // Get dependencies
        $pageMapper = ($this->container->dataMapper)('PageMapper');
        $toolbox = $this->container->toolbox;

        // Get page object
        $pageId = $this->request->getParsedBodyParam('page_id');
        $newSlug = $toolbox->cleanUrl($this->request->getParsedBodyParam('page_slug'));

        // Try to get the original page from database for update
        if ($pageId) {
            $page = $pageMapper->findById((int) $pageId);

            // Ensure we are not messing with the home page slug
            if ($page->page_slug === 'home' && $newSlug !== 'home') {
                throw new Exception('PitonCMS: Cannot change home page slug.');
            }
        } else {
            $page = $pageMapper->make();
        }

        $page->collection_id = $this->request->getParsedBodyParam('collection_id');
        $page->template = $this->request->getParsedBodyParam('template');
        $page->title = trim($this->request->getParsedBodyParam('title'));
        $page->sub_title = $this->request->getParsedBodyParam('sub_title');
        $page->page_slug = $newSlug;
        $page->meta_description = trim($this->request->getParsedBodyParam('meta_description'));
        $page->media_id = $this->request->getParsedBodyParam('page_media_id');

        // Process published date
        $publishedDate = $this->request->getParsedBodyParam('published_date');
        $publishNow = $this->request->getParsedBodyParam('publish_now');

        // Set publish date
        if (empty($publishedDate) && isset($publishNow)) {
            // Set publish date to today if the date was not set and the publish now button was used
            $date = new DateTime();
            $page->published_date = $date->format('Y-m-d');
        } elseif (!empty($publishedDate)) {
            // Otherwise if a date was provided, honor the selected date
            $date = DateTime::createFromFormat('Y-m-d', $publishedDate);
            $page->published_date = $date->format('Y-m-d');
        } else {
            // Clear date
            $page->published_date = null;
        }

        // Save Page and return ID
        return $pageMapper->save($page);
    }

    /**
     * Save Page Settings
     *
     * @param int $pageId
     * @return void
     * @uses POST
     */
    protected function savePageSettings(int $pageId)
    {
        // Save any custom page settings
        if ($post = $this->request->getParsedBodyParam('setting')) {
            // Get dependencies
            $dataStoreMapper = ($this->container->dataMapper)('DataStoreMapper');

            foreach ($post as $row) {
                $setting = $dataStoreMapper->make();
                $setting->id = $row['id'];

                // Check for a page setting delete flag
                if (isset($row['delete'])) {
                    $dataStoreMapper->delete($setting);
                    continue;
                }

                $setting->page_id = $pageId;
                $setting->category = 'page';
                $setting->setting_key = $row['setting_key'];
                $setting->setting_value = $row['setting_value'];
                $dataStoreMapper->save($setting);
            }
        }
    }

    /**
     * Save Page Elements
     *
     * @param int $pageId
     * @return void
     * @users POST
     */
    protected function savePageElements(int $pageId)
    {
        $pageElementMapper = ($this->container->dataMapper)('PageElementMapper');
        $dataStoreMapper = ($this->container->dataMapper)('DataStoreMapper');
        $toolbox = $this->container->toolbox;

        // Save page elements by block
        $index = 1;
        foreach ($this->request->getParsedBodyParam('element') as $element) {
            // Save element
            $pageElement = $pageElementMapper->make();
            $pageElement->id = $element['element_id'];
            $pageElement->page_id = $pageId;
            $pageElement->block_key = $element['block_key'];
            $pageElement->template = $element['element_template'];
            $pageElement->element_sort = $index++;
            $pageElement->title = $element['element_title'];
            $pageElement->content = $element['content'];
            $pageElement->excerpt = $toolbox->truncateHtmlText($pageElement->content, 60);
            $pageElement->collection_id = $element['element_collection_id'];
            $pageElement->gallery_id = $element['element_gallery_id'];
            $pageElement->embedded = $element['embedded'];
            $pageElement->media_id = $element['element_media_id'];

            $pageElement = $pageElementMapper->save($pageElement);

            // Save any element settings
            if ($element['setting']) {
                foreach ($element['setting'] as $row) {
                    $setting = $dataStoreMapper->make();
                    $setting->id = $row['id'];

                    // Check for a page setting delete flag
                    if (isset($row['delete'])) {
                        $dataStoreMapper->delete($setting);
                        continue;
                    }

                    $setting->element_id = $pageElement->id;
                    $setting->category = 'element';
                    $setting->setting_key = $row['setting_key'];
                    $setting->setting_value = $row['setting_value'];
                    $dataStoreMapper->save($setting);
                }
            }
        }
    }

    /**
     * Delete Page
     *
     * Home page is restricted from being deleted
     */
    public function deletePage()
    {
        // Get dependencies
        $pageMapper = ($this->container->dataMapper)('PageMapper');

        $pageId = empty($this->request->getParsedBodyParam('page_id')) ? null : $this->request->getParsedBodyParam('page_id');

        if (null !== $pageId) {
            // Ensure this is not the home page
            $page = $pageMapper->findById((int) $pageId);

            if ($page->page_slug === 'home') {
                throw new Exception('PitonCMS: Cannot delete home page');
            }

            // Delete page. Elements, Settings, and Navigation records are deleted by foreign key database constraints
            $pageMapper->delete($page);
        }

        // Redirect back to show pages
        return $this->redirect('adminPage');
    }

    /**
     * Get New Element Form
     *
     * XHR Request
     * Renders new element form with initial values and returns XHR request
     * @param void
     * @uses queryParam pageTemplate
     * @uses queryParam blockKey
     * @return Response
     */
    public function getNewElement(): Response
    {
        // Wrap in try catch to stop processing at any point and let the xhrResponse takeover
        try {
            // Get dependencies
            $definition = $this->container->jsonDefinitionHandler;
            $pageElementMapper = ($this->container->dataMapper)('PageElementMapper');
            $pageElement = $pageElementMapper->make();

            $pageElement->template = htmlspecialchars($this->request->getQueryParam('template'));
            $pageElement->block_key = htmlspecialchars($this->request->getQueryParam('blockKey'));

            // Get element definition
            if (null === $pageElement->definition = $definition->getElement($pageElement->template . '.json')) {
                throw new Exception('Element Definition Error ' . $pageElement->template . '.json' . print_r($definition->getErrorMessages(), true));
            }

            // Get and load page element settings
            if (isset($pageElement->definition->settings)) {
                $pageElement->settings = $this->mergeSettings([], $pageElement->definition->settings);
            }

            // Make string template
            $template = '{% import "@admin/pages/_pageMacros.html" as pageMacro %}';
            $template .= ' {{ pageMacro.elementForm(element, element.blockKey) }}';

            $status = "success";
            $text = $this->container->view->fetchFromString($template, ['element' => $pageElement]);
        } catch (Throwable $th) {
            $status = "error";
            $text = "Exception getting new element: ". $th->getMessage();
        }

        return $this->xhrResponse($status, $text);
    }

    /**
     * Delete Element
     *
     * XHR Request
     * Deletes element returns XHR request
     * @param void
     * @uses POST
     * @uses elementId Element ID
     * @return Response
     */
    public function deleteElement()
    {
        // Get dependencies
        $pageElement = ($this->container->dataMapper)('PageElementMapper');

        // Wrap in try catch to stop processing at any point and let the xhrResponse takeover
        try {
            // Check that we received an ID
            $id = htmlspecialchars($this->request->getParsedBodyParam('elementId', 'x'));
            if (!is_numeric($id)) {
                throw new Exception("Invalid element ID");
            }

            // Delete block element
            $blockElement = $pageElement->make();
            $blockElement->id = (int) $id;
            $pageElement->delete($blockElement);

            $status = 'success';
            $text = null;
        } catch (Throwable $th) {
            $status = "error";
            $text = "Exception getting new element: ". $th->getMessage();
        }

        return $this->xhrResponse($status, $text);
    }

    /**
     * Show Collection Groups
     *
     * @param void
     * @return Response
     */
    public function showCollectionGroups()
    {
        $collectionMapper = ($this->container->dataMapper)('CollectionMapper');
        $definition = $this->container->jsonDefinitionHandler;
        $templates = $definition->getCollections();

        $collections = $collectionMapper->find();

        // Use filename as key for quick look up when adding template name into result set
        $templates = array_combine(array_column($templates, 'filename'), $templates);

        // Set template name in result set
        foreach ($collections as &$page) {
            $page->template_name = $templates[$page->collection_definition]['name'] ?? null;
        }

        return $this->render('pages/collections.html', ['collections' => $collections]);
    }

    /**
     * Edit Collection Group
     *
     * Create, edit, or delete collection group
     * @param array $args
     * @return Response
     */
    public function editCollection($args): Response
    {
        // Get dependencies
        $collectionMapper = ($this->container->dataMapper)('CollectionMapper');
        $definition = $this->container->jsonDefinitionHandler;

        if (isset($args['id']) && is_numeric($args['id'])) {
            // If a collection ID was provided, load that collection
            $data['collection'] = $collectionMapper->findById((int) $args['id']);
        } else {
            // Create new collection object
            $data['collection'] = $collectionMapper->make();
        }

        // Get available collection templates
        $data['templates'] = $definition->getCollections();

        return $this->render('pages/collectionEdit.html', $data);
    }

    /**
     * Save Collection Group
     *
     * Create new collection, or update existing collection
     * @param void
     * @return Response
     * @uses POST
     */
    public function saveCollection(): Response
    {
        // Get dependencies
        $collectionMapper = ($this->container->dataMapper)('CollectionMapper');
        $toolbox = $this->container->toolbox;

        $collectionId = $this->request->getParsedBodyParam('collection_id');

        // Get saved collection or make one
        if ($collectionId) {
            $collection = $collectionMapper->findById((int) $collectionId);
        } else {
            $collection = $collectionMapper->make();
        }

        $collection->id = $collectionId;
        $collection->collection_title = trim($this->request->getParsedBodyParam('collection_title'));
        $collection->collection_slug = $toolbox->cleanUrl($this->request->getParsedBodyParam('collection_slug'));
        $collection->collection_definition = $this->request->getParsedBodyParam('collection_definition');
        $collectionMapper->save($collection);

        // Save collection and redirect back
        return $this->redirect('adminCollectionEdit', ['id' => $collection->id]);
    }

    /**
     * Delete Collection Group
     *
     * Collections with assigned pages are restricted from being deleted
     * @param void
     * @return Response
     * @uses POST
     */
    public function deleteCollection(): Response
    {
        // Get dependencies
        $collectionMapper = ($this->container->dataMapper)('CollectionMapper');

        // Get collection to delete
        $collectionId = $this->request->getParsedBodyParam('collection_id');
        $collection = $collectionMapper->findById((int) $collectionId);

        // Integrity checks before deleting
        if (empty($collection)) {
            $collectionId = $collectionId ?? 'null';
            throw new Exception("PitonCMS: Collection ID $collectionId not found for deletion");
        }

        if ($collection->page_count > 0) {
            throw new Exception("PitonCMS: Cannot delete a Collection with pages assigned. First remove all pages from this collection");
        }

        $collectionMapper->delete($collection);

        // Redirect back to collection groups
        return $this->redirect('adminCollection');
    }
}

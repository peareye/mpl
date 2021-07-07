<?php

/**
 * PitonCMS (https://github.com/PitonCMS)
 *
 * @link      https://github.com/PitonCMS/Piton
 * @copyright Copyright 2015 Wolfgang Moritz
 * @license   https://github.com/PitonCMS/Piton/blob/master/LICENSE (MIT License)
 */

declare(strict_types=1);

namespace Piton\Pagination;

use Exception;
use Twig\Environment;

/**
 * Pagination Trait
 * @version 0.4.0
 */
trait PaginationTrait
{
    protected $domain = '';
    protected $pageUrl;
    protected $queryStringPageNumberParam = 'page';
    protected $currentPageLinkNumber;
    protected $numberOfPageLinks;
    protected $resultsPerPage = 10;
    protected $numberOfAdjacentLinks = 2;
    protected $totalResultsFound = 0;
    protected $cache = [];
    protected $values = [];
    protected $paginationWrapperClass = 'pagination';
    protected $linkBlockClass = 'page-link';
    protected $anchorClass = '';
    protected $previousText = '&laquo;';
    protected $nextText = '&raquo;';
    protected $ellipsisText = '&hellip;';
    protected $templateDirectory;
    protected $templateFilename;

    /**
     * Constructor
     *
     * @param  array $config Configuration options array
     * @return void
     */
    public function __construct(array $config = null)
    {
        $this->setCurrentPageNumber();
        $this->setConfig($config ?? []);
        $this->setPagePath();
    }

    /**
     * Set Page Path
     *
     * Set link URL with domain and path, or defaults to the current path
     * This automatically gets other query params and values, and passes them to the next request.
     * Optionally update other params by passing in the second argument.
     * @param  string|null $pagePath    Path to resource, can optionally include http(s) full qualified domain, or just path
     * @param  array|null  $queryParams Optional array of query string params and values
     * @return void
     */
    public function setPagePath(string $pagePath = null, array $queryParams = null): void
    {
        // If $pagePath is not set, just use current request URI
        if ($pagePath === null) {
            $pagePath = htmlspecialchars($_SERVER['REQUEST_URI']);
        }

        // Capture any existing query params from $_GET array
        $params = $_GET ?? [];

        // Strip off any query params as those were captured above and will be reattached later
        if (stristr($pagePath, '?')) {
            $pagePath = mb_substr($pagePath, 0, mb_strpos($pagePath, '?'));
        }

        // If the provided path starts with http(s), just set the link and ignore the $domain property
        if ('http' === mb_strtolower(mb_substr($pagePath, 0, 4))) {
            $this->pageUrl = $pagePath . '?';
        } else {
            $this->pageUrl = rtrim($this->domain, '/') . '/' . ltrim($pagePath, '/') . '?';
        }

        // Update any query params if provided
        if ($queryParams) {
            array_walk($queryParams, function ($value, $key) use (&$params) {
                $params[$key] = $value;
            });
        }

        // Remove and re-add page number query param so it is at the end of the array, and therefore the end of the query string
        unset($params[$this->queryStringPageNumberParam]);
        $params[$this->queryStringPageNumberParam] = '';

        // Build query string
        $this->pageUrl .= http_build_query($params);
    }

    /**
     * Set Current Page Number
     *
     * Derives the current page number request
     * @param  void
     * @return void
     */
    public function setCurrentPageNumber(): void
    {
        if (isset($_GET[$this->queryStringPageNumberParam])) {
            $this->currentPageLinkNumber = (int) htmlspecialchars($_GET[$this->queryStringPageNumberParam]);
        } else {
            $this->currentPageLinkNumber = 1;
        }
    }

    /**
     * Get Current Page Number
     *
     * Gets the current page number for display in templates
     * @param void
     * @return int Page number
     */
    public function getCurrentPageNumber(): int
    {
        return $this->currentPageLinkNumber;
    }

    /**
     * Get Offset
     *
     * Returns the query offset for the current page number
     * @param  void
     * @return int
     */
    public function getOffset(): int
    {
        return ($this->currentPageLinkNumber - 1) * $this->resultsPerPage;
    }

    /**
     * Get Limit
     *
     * Gets the query limit rows per page configuration setting
     * @param  void
     * @return int
     */
    public function getLimit(): int
    {
        return $this->resultsPerPage;
    }

    /**
     * Set Total Results Found
     *
     * Set the total results from the query
     * @param  int $totalResultsFound number of rows found
     * @return void
     */
    public function setTotalResultsFound(int $totalResultsFound): void
    {
        $this->totalResultsFound = $totalResultsFound;
    }

    /**
     * Build Pagination Links
     *
     * Build pagination links array and assigns to $this->values
     * @param  void
     * @return void
     */
    private function buildPageLinks(): void
    {
        // If buildPageLinks has already been called, just return
        if (isset($this->values['links'])) {
            return;
        }

        // Make sure we have required variables
        if (!isset($this->totalResultsFound)) {
            throw new Exception('PitonPagination: Total rows in results not set in setTotalResultsFound()');
        }

        // Calculate the total number of pages in the result set
        $this->numberOfPageLinks = (int) ceil($this->totalResultsFound / $this->resultsPerPage);

        // Calcuate starting and ending page in the central set of links
        $startPage = ($this->currentPageLinkNumber - $this->numberOfAdjacentLinks > 0) ? $this->currentPageLinkNumber - $this->numberOfAdjacentLinks : 1;
        $endPage = ($this->currentPageLinkNumber + $this->numberOfAdjacentLinks <= $this->numberOfPageLinks) ? $this->currentPageLinkNumber + $this->numberOfAdjacentLinks : $this->numberOfPageLinks;

        //  Start with Previous link
        if ($this->currentPageLinkNumber === 1) {
            $this->values['links'][] = ['href' => $this->pageUrl . 1, 'pageNumber' => ''];
        } else {
            $this->values['links'][] = ['href' => $this->pageUrl . ($this->currentPageLinkNumber - 1), 'pageNumber' => ''];
        }

        // Always include the page one link
        if ($startPage > 1) {
            $this->values['links'][] = ['href' => $this->pageUrl . 1, 'pageNumber' => 1];
        }

        // Do we need to add ellipsis after '1' and before the link series?
        if ($startPage >= 3) {
            $this->values['links'][] = ['href' => '', 'pageNumber' => 'ellipsis'];
        }

        // Build link series
        for ($i = $startPage; $i <= $endPage; ++$i) {
            $this->values['links'][] = ['href' => $this->pageUrl . $i, 'pageNumber' => $i];
        }

        // Do we need to add ellipsis after the link series?
        if ($endPage <= $this->numberOfPageLinks - 2) {
            $this->values['links'][] = ['href' => '', 'pageNumber' => 'ellipsis'];
        }

        // Always include last page link
        if ($endPage < $this->numberOfPageLinks) {
            $this->values['links'][] = ['href' => $this->pageUrl . $this->numberOfPageLinks, 'pageNumber' => $this->numberOfPageLinks];
        }

        // And finally, the Next link
        if ($endPage === $this->numberOfPageLinks) {
            $this->values['links'][] = ['href' => $this->pageUrl . $this->numberOfPageLinks, 'pageNumber' => ''];
        } else {
            $this->values['links'][] = ['href' => $this->pageUrl . ($this->currentPageLinkNumber + 1), 'pageNumber' => ''];
        }
    }

    /**
     * Render Pagination HTML
     *
     * @param Twig\Environment|null $env Only provided by TwigPagination
     * @return string
     */
    protected function render(Environment $env = null)
    {
        // If there are no rows, or if there is only one page of results then do not display the pagination
        if ($this->totalResultsFound === 0 || $this->resultsPerPage >= $this->totalResultsFound) {
            return;
        }

        if ($env) {
            // Called from TwigPagination class
            $values['pagination']['links'] = $this->values['links'];
            $values['pagination']['currentPageLinkNumber'] = $this->currentPageLinkNumber;
            $values['pagination']['numberOfPageLinks'] = $this->numberOfPageLinks;
            $values['pagination']['pageUrl'] = $this->pageUrl;
            $values['pagination']['paginationWrapperClass'] = $this->paginationWrapperClass;
            $values['pagination']['anchorClass'] = $this->anchorClass;
            $values['pagination']['previousText'] = $this->previousText;
            $values['pagination']['nextText'] = $this->nextText;
            $values['pagination']['ellipsisText'] = $this->ellipsisText;
            $values['pagination']['linkBlockClass'] = $this->linkBlockClass;

            // Add custom Twig pagination template and display
            $loader = $env->getLoader();
            $loader->setPaths($this->templateDirectory, 'pitonPagination');
            $env->display('@pitonPagination/' . $this->templateFilename, $values);
        } else {
            // Called from Pagination class
            $counter = 0;
            $numberOfLinks = count($this->values['links']) - 1;
            require dirname(__FILE__) . '/templates/pageLinks.php';
        }
    }

    /**
     * Set Pagination Configuration
     *
     * @param  array|null $config Configuration array of options
     * @return void
     */
    public function setConfig(?array $config): void
    {
        // Optional fully qualified domaine
        if (isset($config['domain'])) {
            $this->domain = $config['domain'];
        }

        // Query string param name
        if (isset($config['queryStringPageNumberParam'])) {
            $this->queryStringPageNumberParam = $config['queryStringPageNumberParam'];
        }

        // The number of results to display per page
        if (isset($config['resultsPerPage']) && is_numeric($config['resultsPerPage'])) {
            $this->resultsPerPage = (int) $config['resultsPerPage'];
        }

        // Number of adjacent links to display next to the current active page. Only works when there are many links
        if (isset($config['numberOfAdjacentLinks']) && is_numeric($config['numberOfAdjacentLinks'])) {
            $this->numberOfAdjacentLinks = (int) $config['numberOfAdjacentLinks'];
        }

        // Total number of results found by query
        if (isset($config['totalResultsFound'])) {
            $this->setTotalResultsFound($config['totalResultsFound']);
        }

        // Class to apply in container div
        if (isset($config['paginationWrapperClass'])) {
            $this->paginationWrapperClass = $config['paginationWrapperClass'];
        }

        // Class to apply to each link block div
        if (isset($config['linkBlockClass'])) {
            $this->linkBlockClass = $config['linkBlockClass'];
        }

        // Class to apply to each anchor
        if (isset($config['anchorClass'])) {
            $this->anchorClass = $config['anchorClass'];
        }

        // Text or symbol to display in previous button
        if (isset($config['previousText'])) {
            $this->previousText = $config['previousText'];
        }

        // Text or symbol to display in next button
        if (isset($config['nextText'])) {
            $this->nextText = $config['nextText'];
        }

        // Text or symbol to display in ellipsis blocks ... that fill gaps when there are too many page blocks
        if (isset($config['ellipsisText'])) {
            $this->ellipsisText = $config['ellipsisText'];
        }

        // Path to directory containing HTML templates. Including trailing slash /
        if (isset($config['templateDirectory'])) {
            $this->templateDirectory = $config['templateDirectory'];
        } else {
            $this->templateDirectory = dirname(__FILE__) . '/templates/';
        }

        // Template file name including extension to load
        if (isset($config['templateFilename'])) {
            $this->templateFilename = $config['templateFilename'];
        } else {
            $this->templateFilename = 'twigPageLinks.html';
        }
    }
}

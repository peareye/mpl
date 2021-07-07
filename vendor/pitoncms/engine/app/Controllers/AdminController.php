<?php

/**
 * PitonCMS (https://github.com/PitonCMS)
 *
 * @link      https://github.com/PitonCMS/Piton
 * @copyright Copyright (c) 2015 - 2019 Wolfgang Moritz
 * @license   https://github.com/PitonCMS/Piton/blob/master/LICENSE (MIT License)
 */

declare(strict_types=1);

namespace Piton\Controllers;

use Slim\Http\Response;
use DOMDocument;

/**
 * Piton Admin Controller
 */
class AdminController extends AdminBaseController
{
    /**
     * Admin Home Page
     *
     * @param void
     * @param Response
     */
    public function home(): Response
    {
        return $this->render('home.html');
    }

    /**
     * Sitemap
     *
     * Shows current sitemap, and sitemap update submit button
     * @param void
     * @return Response
     */
    public function sitemap(): Response
    {
        // Get current sitemap
        $pathToSitemap = ROOT_DIR . 'public/sitemap.xml';
        $data = [];
        if (file_exists($pathToSitemap)) {
            $data['sitemapXML'] = file_get_contents($pathToSitemap);
            $data['lasteUpdateDate'] = filemtime($pathToSitemap);
        }

        return $this->render('tools/sitemap.html', $data);
    }

    /**
     * Update Sitemap
     *
     * Generates sitemap to public/sitemap.xml
     * @param void
     * @return Response
     */
    public function updateSitemap(): Response
    {
        // Get dependencies
        $pageMapper = ($this->container->dataMapper)('PageMapper');
        $sitemapHandler = $this->container->get('sitemapHandler');

        // Get all published content
        $allContent = array_merge($pageMapper->findPublishedContent());
        $links = [];

        foreach ($allContent as $page) {
            $link = ($page->collection_slug) ? $page->collection_slug . '/' : null;
            $link .= ($page->page_slug === 'home') ? '' : $page->page_slug;

            $links[] = [
                'link' => $link,
                'date' => date('c', strtotime($page->updated_date))
            ];
        }

        // Make sitemap
        if ($sitemapHandler->make($links, $this->request->getUri()->getBaseUrl(), $this->settings['environment']['production'])) {
            $this->setAlert('info', 'Sitemap updated and search engines alerted', $sitemapHandler->getMessages());
        } else {
            $this->setAlert('danger', 'Unable to update sitemap', $sitemapHandler->getMessages());
        }

        return $this->redirect('adminSitemap');
    }

    /**
     * Show Help Page
     *
     * @param array $args
     * @return Response
     */
    public function showHelp($args): Response
    {
        // Load dependencies
        $markdown = $this->container->markdownParser;

        // Pass through reference to subject
        $data['subject'] = $args['subject'];

        // If no support file was requested, default to a support index
        if (!isset($args['file'])) {
            $index = ($args['subject'] === 'designer') ? 'designerIndex' : 'clientIndex';
            return $this->render("help/$index.html", $data);
        }

        // If requesting the about PitonCMS page
        if ($args['file'] === 'aboutPitonCMS') {
            return $this->aboutPiton();
        }

        // Build path to file and add deep link to anchor
        $data['link'] = $args['link'] ?? null;
        $helpFile = ROOT_DIR . "vendor/pitoncms/engine/support/{$args['subject']}/{$args['file']}.md";

        if (file_exists($helpFile)) {
            $helpContent = $markdown->text(file_get_contents($helpFile));

            // Parse help file to modify headings
            $document = new DOMDocument();
            $document->loadHTML($helpContent);

            // Get heading tags h1..h6 and set ID so we can deep link to content
            foreach (['h1', 'h2', 'h3', 'h4', 'h5', 'h6'] as $h) {
                $nodes = $document->getElementsByTagName($h);
                foreach ($nodes as $node) {
                    $value = str_replace(' ', '-', strtolower($node->nodeValue));
                    $node->setAttribute('id', $value);
                }
            }

            // Get breadcrumb title from first H1 in file and render HTML
            $data['breadcrumbTitle'] = $document->getElementsByTagName('h1')[0]->textContent ?? 'Error';
            $data['helpContent'] = $document->saveHTML();
        } else {
            $this->container->logger->warning("PitonCMS: Help file does not exist: Subject {$args['subject']}, File {$args['file']}.");
            $data['helpContent'] = "<h1>Help File Does Not Exist</h1>";
        }

        return $this->render('help/helpFile.html', $data);
    }

    /**
     * Show Piton Engine aboutPiton Notes
     *
     * Used in Help > Developer > Version
     * @param void
     * @return Response
     */
    public function aboutPiton(): Response
    {
        $markdown = $this->container->markdownParser;
        $log = $this->container->logger;

        // Get list of releases from GitHub. First check that cURL is installed on the server
        if (!function_exists('curl_init')) {
            // If curl is not installed display notice
            $log->info("Piton: cURL is not installed, unable to get releases from GitHub.");
        } else {
            // Get GitHub release history for engine
            // https://developer.github.com/v3/repos/releases
            $githubApi = 'https://api.github.com/repos/PitonCMS/Engine/releases';
            $curl = curl_init();
            curl_setopt_array($curl, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_URL => $githubApi,
                CURLOPT_USERAGENT => $this->request->getHeaderLine('HTTP_USER_AGENT')
            ]);
            $responseBody = curl_exec($curl);
            $responseStatus = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            curl_close($curl);

            // Verify that we have a response
            if ($responseStatus == '200') {
                $jsonReleases = json_decode($responseBody);
                $data['releases'] = array_slice($jsonReleases, 0, 3, true);

                // Format Markdown
                foreach ($data['releases'] as $key => $release) {
                    $data['releases'][$key]->body = $markdown->text($release->body);
                }

                // TODO
                // Check if there is a more current release available
                // if (array_search($installedRelease, array_column($releases, 'tag_name')) > 0) {
                //     $message = "The current PitonCMS version is {$releases[0]->tag_name}, you have version {$installedRelease}.";
                //     // $this->setAlert('info', 'There is a newer version of the PitonCMS Engine', $message);
                // }
            }
        }

        $data['breadcrumbTitle'] = 'About PitonCMS';
        // Not passing any helpContent through, but sending a flag to enable the breadcrumb
        $data['helpContent'] =  true;

        return $this->render('help/about.html', $data);
    }
}

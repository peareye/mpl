<?php

/**
 * PitonCMS (https://github.com/PitonCMS)
 *
 * @link      https://github.com/PitonCMS/Piton
 * @copyright Copyright 2015 Wolfgang Moritz
 * @license   https://github.com/PitonCMS/Piton/blob/master/LICENSE (MIT License)
 */

declare(strict_types=1);

/**
 * Test Piton Pagination Library
 *
 * This is to test the non-Twig version using plain PHP templates
 * Copy to public root to run.
*/

// Set encoding
mb_internal_encoding('UTF-8');
mb_http_output('UTF-8');

// Load the Composer Autoloader
require dirname(__DIR__) . '/vendor/autoload.php';

// Create array of pseudo pages
$pages = array_fill(0, 100, 'Pagination Test');

// Load and configure Piton Pagination, and test config options
$config = [];
// $config['queryStringPageNumberParam'] = 'newPage';
// $config['resultsPerPage'] = 5;
// $config['numberOfAdjacentLinks'] = 1;
// $config['paginationWrapperClass'] = 'pagination';
$pagination = new Piton\Pagination\Pagination($config);

// Set runtime values
$pagination->setTotalResultsFound(count($pages));
$pagination->setPagePath($_SERVER['PHP_SELF']);
// $pagination->setPagePath('paginationTestController.php');

// Load template
$currentPage = $pagination->getCurrentPageNumber();
$currentPageSet = array_slice($pages, $pagination->getOffset(), $pagination->getLimit(), true);
require dirname(__DIR__) . '/vendor/pitoncms/pagination/test/template.php';
